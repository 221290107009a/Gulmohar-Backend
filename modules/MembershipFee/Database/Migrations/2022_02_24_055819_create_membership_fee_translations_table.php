<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMembershipFeeTranslationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('membership_fee_translations', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('membership_fee_id')->unsigned()->nullable();
            $table->string('locale');
            $table->string('name');
            $table->timestamps();

            $table->foreign('membership_fee_id')->references('id')->on('membership_fees')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('membership_fee_translations');
    }
}
