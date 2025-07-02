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
        Schema::create('unit_contracts', function (Blueprint $table) {  
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
            $table->foreignId('unit_id')->constrained('units')->onDelete('cascade');
            $table->date('agreement_start_date')->nullable();
            $table->date('agreement_end_date')->nullable();

            $table->integer('rent_payment_type')->nullable()->comment('1:full, 2:installment');
            $table->decimal('full_amount', 10, 2)->nullable();
            $table->decimal('deposit_amount', 10, 2)->nullable();
            $table->integer('duration_in_months')->nullable();
            
            $table->decimal('total_paid_amount', 10, 2)->nullable();
            $table->date('next_rent_due_date')->nullable();

            $table->decimal('rent_amount', 10, 2)->nullable();
            $table->integer('total_installments')->nullable();
            $table->integer('completed_installments')->nullable();

            
            $table->text('terms')->nullable();

            $table->integer('status')->default(0);
            $table->integer("approval_status")->nullable();
            $table->string("approval_remarks")->nullable();

            // 🔒 Audit
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
        Schema::dropIfExists('unit_contracts');
    }
};
