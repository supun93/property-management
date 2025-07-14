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
        Schema::create('schedulers', function (Blueprint $table) {
            $table->id();
            $table->string('last_run_date')->nullable();
            $table->string('next_run_date')->nullable();
            $table->integer('status')->comment("0:inactive, 1:active")->default(1);
            $table->integer('type')->comment("1:daily, 2:weekly, 3:monthly, 4:yearly")->nullable();
            $table->string('method')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schedulers');
    }
};
