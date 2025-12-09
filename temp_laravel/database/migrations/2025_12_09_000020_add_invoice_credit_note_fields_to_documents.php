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
        Schema::table('documents', function (Blueprint $table) {
            // Update document types to include invoices and credit notes
            $table->dropColumn('document_type');
        });
        
        Schema::table('documents', function (Blueprint $table) {
            $table->enum('document_type', [
                'POLICY_DOCUMENT',
                'ENDORSEMENT_DOCUMENT', 
                'FINANCIAL_DOCUMENT',
                'INVOICE',
                'CREDIT_NOTE',
                'RECEIPT',
                'OTHER'
            ])->default('OTHER');
        });
        
        // Add invoice/credit note specific fields
        Schema::table('documents', function (Blueprint $table) {
            $table->string('invoice_number')->nullable()->after('file_name');
            $table->decimal('amount', 15, 2)->nullable()->after('invoice_number');
            $table->decimal('tax_amount', 15, 2)->default(0)->after('amount');
            $table->decimal('total_amount', 15, 2)->nullable()->after('tax_amount');
            $table->enum('status', ['DRAFT', 'SENT', 'PARTIALLY_PAID', 'PAID', 'OVERDUE', 'CANCELLED', 'ISSUED', 'APPLIED'])->nullable()->after('total_amount');
            $table->date('issue_date')->nullable()->after('status');
            $table->date('due_date')->nullable()->after('issue_date');
            $table->date('paid_date')->nullable()->after('due_date');
            $table->text('notes')->nullable()->after('paid_date');
        });
        
        // Add indexes for better performance
        Schema::table('documents', function (Blueprint $table) {
            $table->index(['document_type', 'status']);
            $table->index('invoice_number');
            $table->index(['issue_date', 'due_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropIndex(['document_type', 'status']);
            $table->dropIndex('invoice_number');
            $table->dropIndex(['issue_date', 'due_date']);
        });
        
        Schema::table('documents', function (Blueprint $table) {
            $table->dropColumn([
                'invoice_number', 'amount', 'tax_amount', 'total_amount', 
                'status', 'issue_date', 'due_date', 'paid_date', 'notes'
            ]);
        });
        
        Schema::table('documents', function (Blueprint $table) {
            $table->enum('document_type', [
                'POLICY_DOCUMENT',
                'ENDORSEMENT_DOCUMENT',
                'FINANCIAL_DOCUMENT',
                'OTHER'
            ])->default('OTHER');
        });
    }
};