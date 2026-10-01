<?php

/**
 * Part of the Sentinel package.
 *
 * NOTICE OF LICENSE
 *
 * Licensed under the 3-clause BSD License.
 *
 * This source file is subject to the 3-clause BSD License that is
 * bundled with this package in the LICENSE file.
 *
 * @package        Sentinel
 * @version        2.0.12
 * @author         Cartalyst LLC
 * @license        BSD License (3-clause)
 * @copyright  (c) 2011-2015, Cartalyst LLC
 * @link           http://cartalyst.com
 */

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class MigrationCartalystSentinel extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('fullname')->nullable();
            $table->string('email')->unique()->nullable();
            $table->string('address', 500)->nullable();
            $table->string('personal_details', 500)->nullable();
            $table->date('dob')->nullable();
            $table->string('gender', 7)->nullable();
            $table->string('profession', 50)->nullable();
            $table->string('education', 50)->nullable();
            $table->string('facebook')->nullable();
            $table->string('instagram')->nullable();
            $table->string('twitter')->nullable();
            $table->string('linkedin')->nullable();
            $table->string('language', 5)->nullable();
            $table->integer('is_subscribe')->default(0);
            $table->integer('is_sankhar_karta')->default(0);
            $table->string('state')->nullable();
            $table->string('city')->nullable();
            $table->string('sankhar_karta_profile')->nullable();
            $table->integer('sankhar_karta_status')->default(0);
            $table->integer('subscription_history_id')->nullable();
            $table->string('expo_notification_token')->nullable();
            $table->string('fcm_token')->nullable();
            $table->string('referral_code', 250)->nullable();
            $table->datetime('notification_date')->nullable();
            $table->string('password')->nullable();
            $table->text('permissions')->nullable();
            $table->datetime('last_login')->nullable();
            $table->timestamps();
        });

        Schema::create('roles', function (Blueprint $table) {
            $table->increments('id');
            $table->text('permissions')->nullable();
            $table->timestamps();
        });

        Schema::create('role_translations', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('role_id')->unsigned();
            $table->string('locale');
            $table->string('name');

            $table->unique(['role_id', 'locale']);
            $table->foreign('role_id')->references('id')->on('roles')->onDelete('cascade');
        });

        Schema::create('user_roles', function (Blueprint $table) {
            $table->integer('user_id')->unsigned();
            $table->integer('role_id')->unsigned();
            $table->timestamps();

            $table->primary(['user_id', 'role_id']);
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('role_id')->references('id')->on('roles')->onDelete('cascade');
        });

        Schema::create('activations', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned()->index();
            $table->string('code');
            $table->boolean('completed')->default(false);
            $table->datetime('completed_at')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        Schema::create('persistences', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned();
            $table->string('code')->unique();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        Schema::create('reminders', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned();
            $table->string('code');
            $table->boolean('completed')->default(false);
            $table->datetime('completed_at')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        Schema::create('throttle', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned()->nullable();
            $table->string('type');
            $table->string('ip')->nullable();
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
        Schema::dropIfExists('throttle');
        Schema::dropIfExists('reminders');
        Schema::dropIfExists('persistences');
        Schema::dropIfExists('activations');
        Schema::dropIfExists('user_roles');
        Schema::dropIfExists('role_translations');
        Schema::dropIfExists('roles');
        Schema::dropIfExists('users');
    }
}
