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
        Schema::create('role_task', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('role_id');
            $table->foreignId('task_id');
            $table->integer('active')->nullable(true)->default('1');
            $table->timestampsTz($precision = 0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('role_tasks');
    }
};
