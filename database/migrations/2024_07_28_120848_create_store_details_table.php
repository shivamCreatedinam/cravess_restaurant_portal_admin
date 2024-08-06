<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('store_details', function (Blueprint $table) {
            $table->id();
            $table->string("user_id")->index();
            $table->string('unique_id')->nullable()->comment("Only for store.");
            $table->string("store_name", 100)->nullable();
            $table->enum("store_type", ['veg', 'non_veg', 'both'])->nullable();
            $table->text("store_serving")->nullable()->comment("Store Serving Dishes type id in json format");
            $table->string("store_mobile_no", 15)->nullable();
            $table->string("store_phone_no", 15)->nullable();
            $table->string("store_email", 100)->nullable();
            $table->string("website", 100)->nullable();
            $table->string("store_address")->nullable();
            $table->string("store_city", 50)->nullable();
            $table->string("store_state", 50)->nullable();
            $table->string("store_pincode", 10)->nullable();
            $table->text("store_desc")->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('store_details');
    }
};
