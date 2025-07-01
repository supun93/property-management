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
        Schema::create('properties', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('address');
            $table->foreignId('category_id')->constrained('property_categories')->onDelete('cascade');
            $table->foreignId('owner_id')->constrained('users')->onDelete('cascade');
            
            $table->unsignedBigInteger("created_by")->nullable();
            $table->unsignedBigInteger("updated_by")->nullable();
            $table->unsignedBigInteger("deleted_by")->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->foreign("created_by")->references("id")->on("users");
            $table->foreign("updated_by")->references("id")->on("users");
            $table->foreign("deleted_by")->references("id")->on("users");
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('property_categories');
    }
};
