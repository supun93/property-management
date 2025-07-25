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
        Schema::create('unit_billing_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unit_id')->constrained('units')->onDelete('cascade');
            $table->foreignId('billing_type_id')->constrained('billing_types')->onDelete('cascade');
            $table->integer('status')->default(1); // 1 = active, 0 = inactive
            $table->unsignedBigInteger('created_by')->nullable();  
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

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
        Schema::dropIfExists('unit_billing_types');
    }
};
