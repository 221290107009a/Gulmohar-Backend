<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateReferralUsagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('referral_usages', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('referral_code', 10)->nullable(false);
            $table->unsignedBigInteger('referrer_id')->nullable(false);
            $table->unsignedBigInteger('referred_user_id')->nullable(false);
            $table->decimal('reward_point', 10, 2)->nullable();
            $table->string('referral_share_type', 10)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('referral_usages');
    }
}
