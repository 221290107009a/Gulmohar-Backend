<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Create a simple footwear size inventory table linked to products.
     * Each row = one size + available quantity for that product.
     */
    public function up(): void
    {
        Schema::create('product_size_inventory', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('product_id');   // matches products.id int(10) unsigned
            $table->string('size', 30);
            $table->unsignedInteger('qty')->default(0);
            $table->boolean('in_stock')->default(true);
            $table->unsignedSmallInteger('position')->default(0);
            $table->timestamps();

            $table->foreign('product_id')
                  ->references('id')
                  ->on('products')
                  ->onDelete('cascade');

            $table->index('product_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_size_inventory');
    }
};
