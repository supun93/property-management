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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('tenant_id')->nullable();
            $table->unsignedBigInteger('contract_id')->nullable();
            $table->date('payment_date')->nullable(); // Invoice date
            $table->decimal('total_amount', 10, 2)->default(0.00);
            $table->string('pdf_path')->nullable(); // If PDF is stored
            $table->string('name'); // Invoice title or reference
            $table->integer('status')->default(0); // 0=Pending, 1=Paid, etc.
            $table->string("approval_remarks")->nullable();
            $table->unsignedBigInteger("created_by")->nullable();
            $table->unsignedBigInteger("updated_by")->nullable();
            $table->unsignedBigInteger("deleted_by")->nullable();

            $table->softDeletes();
            $table->timestamps();

            // Foreign Keys
            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('set null');
            $table->foreign('contract_id')->references('id')->on('unit_contracts')->onDelete('set null');
            $table->foreign("created_by")->references("id")->on("users")->onDelete('set null');
            $table->foreign("updated_by")->references("id")->on("users")->onDelete('set null');
            $table->foreign("deleted_by")->references("id")->on("users")->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
