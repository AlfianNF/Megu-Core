<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->bigIncrements('id')->unsigned();
            $table->string('role_code', 255)->unique();
            $table->string('role_name', 255);
            $table->text('description');
            $table->timestampsTz($precision = 0);
        });

        DB::table("roles")->insert([
            "role_code" => "super-admin",
            "role_name" => "Super Admin",
            "description" => "Super Admin"
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};
