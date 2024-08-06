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
        Schema::create('users', function (Blueprint $table) {
            $table->uuid();
            $table->string('name');
            $table->text('profile_image')->nullable();
            $table->string('email', 100)->unique();
            $table->string('mobile_no', 20)->unique();
            $table->enum('role', ["user", "superadmin","store","rider"])->default("user")->comment("user, superadmin, store, rider");
            $table->timestamp('email_verified_at')->nullable();
            $table->timestamp('mobile_verified_at')->nullable();
            $table->string('temp_token')->nullable();
            $table->string('password');
            $table->boolean('aadhar_verified')->default(0);
            $table->boolean('pan_verified')->default(0);
            $table->boolean('bank_verified')->default(0);
            $table->boolean('fssai_verified')->default(0)->comment("Only Store");
            $table->boolean('gst_verified')->default(0)->comment("Only Store");
            $table->string('facebook_id')->nullable();
            $table->string('google_id')->nullable();
            $table->string('google2fa_secret')->nullable();
            $table->enum('google2fa_enable', ["yes", "no"])->default("no");
            $table->timestamp('google2fa_enable_at')->nullable()->default(null);
            $table->enum('user_status',["active","block","ban"])->nullable()->default('active');
            $table->enum('resto_rider_status',["pending","approved","cancelled"])->nullable();
            $table->text('resto_rider_reason')->nullable()->comment("Cancellation Reason");

            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
