<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHomeScreenSessionsTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Sessions table
        Schema::create('home_screen_sessions', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('user_id')->nullable();
            $table->string('session_token', 64)->unique();
            $table->string('service_type', 50)->default('general');
            $table->json('section_order');
            $table->unsignedInteger('current_position')->default(0);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('expires_at')->nullable();
            
            $table->index(['user_id', 'service_type']);
            $table->index('session_token');
            $table->index('expires_at');
        });
        
        // Content tracking table
        Schema::create('home_screen_shown_content', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('session_id');
            $table->string('section_type', 50);
            $table->string('service_type', 50)->nullable();
            $table->string('content_type', 50);
            $table->unsignedInteger('content_id');
            $table->timestamp('shown_at')->useCurrent();
            
            $table->foreign('session_id')
                ->references('id')
                ->on('home_screen_sessions')
                ->onDelete('cascade');
            
            // Custom index names to avoid MySQL 64-char limit
            $table->index(['session_id', 'section_type', 'service_type'], 'hss_content_session_section_idx');
            $table->index(['session_id', 'content_type', 'content_id'], 'hss_content_session_content_idx');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('home_screen_shown_content');
        Schema::dropIfExists('home_screen_sessions');
    }
}
