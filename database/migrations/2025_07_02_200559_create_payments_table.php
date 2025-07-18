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
        Schema::create('unit_payment_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unit_contract_id')->constrained('unit_contracts')->onDelete('cascade')->nullable();
            $table->unsignedBigInteger('unit_billing_type_id')->nullable();
            $table->integer('is_rent')->default(0); // 1 = rent, 0 = other 
            $table->date('payment_date')->nullable();
            $table->decimal('amount', 10, 2)->nullable();
            $table->text('note')->nullable();
            $table->integer('status')->default(0); // 1 = paid, 0 = pending
            $table->integer("approval_status")->nullable();
            $table->string("approval_remarks")->nullable();
            $table->integer("installment_number")->nullable();
            // 🔒 Audit
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->unsignedBigInteger('deleted_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign("created_by")->references("id")->on("users");
            $table->foreign("updated_by")->references("id")->on("users");
            $table->foreign("deleted_by")->references("id")->on("users");
            $table->foreign("unit_billing_type_id")->references("id")->on("unit_billing_types");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('unit_payment_schedules');
    }
};
