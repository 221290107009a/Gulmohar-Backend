<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserDatasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_datas', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id')->unsigned()->nullable();
            $table->date('dob');
            $table->string('whatsapp_mobile');
            $table->string('address');
            $table->string('city');
            $table->integer('category');
            $table->integer('education');
            $table->integer('religion');
            $table->string('cast');
            $table->string('gender');
            $table->string('country');
            $table->string('state');
            $table->string('photo');
            $table->string('adharcard');
            $table->string('electioncard');
            $table->date('join_date');
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_datas');
    }
}
