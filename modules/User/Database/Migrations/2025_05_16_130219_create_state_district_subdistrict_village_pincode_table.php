<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStateDistrictSubdistrictVillagePincodeTable extends Migration
{
    public function up()
    {
        Schema::create('location_master', function (Blueprint $table) {
            $table->id();
            $table->string('state_code', 20)->index();
            $table->string('state_name');
            $table->string('district_code', 20)->index();
            $table->string('district_name');
            $table->string('subdistrict_code', 20)->index();
            $table->string('subdistrict_name');
            $table->string('village_code', 20)->index();
            $table->string('village_name');
            $table->string('pincode', 10)->index();
            $table->timestamps();

            // Composite indexes for common search patterns
            $table->index(['state_code', 'district_code'], 'idx_state_district');
            $table->index(['district_code', 'subdistrict_code'], 'idx_district_subdistrict');
            $table->index(['subdistrict_code', 'village_code'], 'idx_subdistrict_village');
        });
    }

    public function down()
    {
        Schema::dropIfExists('location_master');
    }
}
