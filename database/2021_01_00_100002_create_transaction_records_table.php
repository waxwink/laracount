<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransactionRecordsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transaction_records', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("account_id");
            $table->bigInteger("voucher_id");
            $table->float("debt");
            $table->float("credit");
            $table->string("description")->nullable();
            $table->bigInteger("ref_id")->nullable();
            $table->timestamps();

            $table->foreign("account_id")->references("id")->on("accounts")->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('transaction_records');
    }
}
