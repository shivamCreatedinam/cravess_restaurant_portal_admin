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
        Schema::create('store_verifications', function (Blueprint $table) {
            $table->id();
            $table->string("user_id")->index();
            $table->text("fssai_image")->nullable();
            $table->text("gst_image")->nullable();
            $table->string("gst_no", 25)->nullable();
            $table->enum("fssai_verification", ['pending', 'verified', 'cancelled'])->nullable();
            $table->text("fssai_cancellation_reason")->nullable();
            $table->enum("gst_verification", ['pending', 'verified', 'cancelled'])->nullable();
            $table->text("gst_cancellation_reason")->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('store_verifications');
    }
};
