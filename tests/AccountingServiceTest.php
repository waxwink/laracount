<?php

namespace Waxwink\Laracount\Test;

use Waxwink\Accounting\Contracts\LockerInterface;
use Waxwink\Accounting\Exceptions\LockedAccountException;
use Waxwink\Laracount\AccountingService;

class AccountingServiceTest extends TestCase
{
    private AccountingService $service;

    private User $testUser;


    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(AccountingService::class);

        $this->testUser = $this->prepareTestUser();
    }

    public function testUserCanDepositToHisAccount()
    {
        $this->service->deposit($this->testUser, 1000);

        $this->assertEquals(-1000, $this->service->balance($this->testUser));
    }

    public function testUserCanDepositToHisAccountMultipleTimes()
    {
        $this->service->deposit($this->testUser, 1000);
        $this->service->deposit($this->testUser, 500);

        $this->assertEquals(-1500, $this->service->balance($this->testUser));
    }

    public function testMultipleUsersCanDepositToTheirAccount()
    {
        $user1 = $this->prepareTestUser();
        $user2 = $this->prepareTestUser();

        $this->service->deposit($user1, 1000);
        $this->service->deposit($user2, 500);

        $this->assertEquals(-1000, $this->service->balance($user1));
        $this->assertEquals(-500, $this->service->balance($user2));
    }

    public function testUserCanWithdrawFromHisAccount()
    {
        $this->service->deposit($this->testUser, 1000);

        $this->service->withdraw($this->testUser, 400);

        $this->assertEquals(-600, $this->service->balance($this->testUser));
    }

    public function testUserCanGetHisTransactionsList()
    {
        $this->service->deposit($this->testUser, 1000);

        $this->service->withdraw($this->testUser, 400);

        $list = $this->service->transactionsList($this->testUser);

        $this->assertCount(2, $list);
    }

    public function testUserCanPaginateThroughHisTransactionsList()
    {
        $this->service->deposit($this->testUser, 1000);
        $this->service->deposit($this->testUser, 500);
        $this->service->withdraw($this->testUser, 400);

        $list = $this->service->transactionsList($this->testUser, paginate: true, page: 1, perPage: 2);

        $this->assertCount(2, $list);

        $list = $this->service->transactionsList($this->testUser, paginate: true, page: 2, perPage: 2);

        $this->assertCount(1, $list);
    }

    public function testTransactionsCanBeFetchedByTimeFiltering()
    {
        $this->service->deposit($this->testUser, 1000);
        $this->travel(-5)->days();
        $this->service->deposit($this->testUser, 500);
        $this->travel(-5)->days();
        $this->service->withdraw($this->testUser, 400);
        $this->travelBack();

        $list = $this->service->transactionsList($this->testUser, from: now()->subDays(6));

        $this->assertCount(2, $list);

        $list = $this->service->transactionsList($this->testUser,
            from: now()->subDays(6),
            to: now()->subDays(4));

        $this->assertCount(1, $list);
    }

    public function testUserCanCustomizeHisTransactionsList()
    {
        $this->service->deposit($this->testUser, 1000);
        $this->service->deposit($this->testUser, 500);

        $list = $this->service->transactionsList($this->testUser, columns: ['created_at', 'debt', 'credit']);
        $item = $list->first();
        $this->assertCount(3, $item->getAttributes());

        $list = $this->service->transactionsList($this->testUser, columns:
            ['created_at', 'debt', 'credit', 'voucher_id']);
        $item = $list->first();
        $this->assertCount(4, $item->getAttributes());
    }

    public function testBankBalanceCanBeChecked()
    {
        $user1 = $this->prepareTestUser();
        $user2 = $this->prepareTestUser();

        $this->service->deposit($user1, 1000);
        $this->service->deposit($user1, 500);
        $this->service->withdraw($user1, 700);
        $this->service->deposit($user2, 500);

        $this->assertEquals(1300, $this->service->bankBalance());
    }

    public function testBankTransactionsCanBeRetrieved()
    {
        $user1 = $this->prepareTestUser();
        $user2 = $this->prepareTestUser();

        $this->service->deposit($user1, 1000);
        $this->service->deposit($user1, 500);
        $this->service->withdraw($user1, 700);
        $this->service->deposit($user2, 500);

        $this->assertCount(4, $this->service->bankTransactionsList());
    }

    public function testCustomerCanPayForAServiceOrInvoiceOrWhatever()
    {
        $this->service->deposit($this->testUser, 1000);
        $this->service->pay($this->testUser, 400);

        $this->assertEquals(-600, $this->service->balance($this->testUser));
        $this->assertEquals(1000, $this->service->bankBalance());
        $this->assertEquals(-400, $this->service->revenueBalance());
    }


    public function testCustomerCanRefundForAPaidService()
    {
        $this->service->deposit($this->testUser, 1000);
        $this->service->pay($this->testUser, 400);
        $this->service->refund($this->testUser, 200);

        $this->assertEquals(-800, $this->service->balance($this->testUser));
        $this->assertEquals(1000, $this->service->bankBalance());
        $this->assertEquals(-200, $this->service->revenueBalance());
    }

    public function testTransactionsCanBeRetrievedByRefId()
    {
        $this->service->deposit($this->testUser, 1000);

        $ref = 45102;
        $this->service->pay($this->testUser, 400, $ref);
        $this->service->refund($this->testUser, 100, $ref);

        $this->assertEquals(-300, $this->service->revenueBalanceByRef($ref));
    }

    public function testUserCanPayOutToAUserForAServiceOrWhatever()
    {
        $this->service->payTo($this->testUser, 1000);

        $this->assertEquals(-1000, $this->service->balance($this->testUser));
        $this->assertEquals(1000, $this->service->expenseBalance());
    }

    public function testCustomerCanPayTaxForHisServicePurchase()
    {
        $ref = 1205;
        $this->service->pay($this->testUser, 900, $ref);
        $this->service->payTax($this->testUser, 100, $ref);

        $this->assertEquals(-900, $this->service->revenueBalanceByRef($ref));
        $this->assertEquals(-100, $this->service->taxBalanceByRef($ref));
    }

    public function testUserCanNotGetBalanceWhenHeIsLocked()
    {
        $locker = app(LockerInterface::class);

        $locker->lock($this->testUser->getAccountId());

        $this->expectException(LockedAccountException::class);

        $this->service->balance($this->testUser);
    }

}