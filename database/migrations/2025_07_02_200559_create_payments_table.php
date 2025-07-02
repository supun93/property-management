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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contract_id')->constrained('contracts')->onDelete('cascade')->nullable();
            $table->foreignId('payment_type_id')->constrained('payment_types')->onDelete('cascade')->nullable();
            $table->date('payment_date')->nullable();
            $table->decimal('amount', 10, 2)->nullable();
            $table->string('method')->nullable(); // e.g., Cash, Bank Transfer, Cheque
            $table->string('reference')->nullable(); // e.g., cheque number or transaction ID
            $table->text('note')->nullable();
            $table->integer('status')->default(0); // 1 = paid, 0 = pending
            $table->integer("approval_status")->nullable();
            $table->string("approval_remarks")->nullable();
            $table->integer("installment_number")->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->foreignId('created_by')->constrained('contracts')->onDelete('cascade')->nullable();
            $table->foreignId('updated_by')->constrained('contracts')->onDelete('cascade')->nullable();
            $table->foreignId('deleted_by')->constrained('contracts')->onDelete('cascade')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
