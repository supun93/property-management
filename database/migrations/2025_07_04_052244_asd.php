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
        Schema::table('unit_payment_schedules', function (Blueprint $table) {
            $table->timestamp('paid_at')->nullable();
            $table->decimal('paid_amount', 10, 2)->nullable();
            $table->string('receipt_number')->nullable();
            $table->text('payment_note')->nullable();
            $table->enum('status_enum', ['PENDING', 'PAID'])->default('PENDING');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
