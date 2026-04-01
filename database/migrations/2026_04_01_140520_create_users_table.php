<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('fullname');
            $table->string('username')->unique();
            $table->text('password');
            $table->string('email')->nullable();
            $table->string('telephone')->nullable();
            $table->foreignId('role_id')->constrained('roles');
            $table->timestampTz('email_verified_at')->nullable();
            $table->string('status_code');
            $table->string('api_token')->nullable(true);
            $table->timestampsTz($precision = 0);
        });

        $role = DB::selectOne("SELECT id FROM roles WHERE role_code = 'super-admin'");
        DB::table("users")->insert([
            "fullname" => "Super Admin",
            "username" => "admin",
            "password" => bcrypt("password"),
            "role_id" => $role->id,
            "status_code" => 'user_active'
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
