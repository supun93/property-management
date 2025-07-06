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
        Schema::create('invoice_lines', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('invoice_id');
            $table->unsignedBigInteger('unit_payment_schedule_id')->nullable(); // Optional link
            $table->unsignedBigInteger('unit_billing_type_id')->nullable();     // Optional link
            $table->string('description');
            $table->decimal('amount', 10, 2)->default(0.00);

            $table->timestamps();

            $table->foreign('invoice_id')->references('id')->on('invoices')->onDelete('cascade');
            $table->foreign('unit_payment_schedule_id')->references('id')->on('unit_payment_schedules')->onDelete('set null');
            $table->foreign('unit_billing_type_id')->references('id')->on('unit_billing_types')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_lines');
    }
};
