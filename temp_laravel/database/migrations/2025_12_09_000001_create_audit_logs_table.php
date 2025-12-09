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
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->string('action'); // ADD_STUDENT, REMOVE_STUDENT, etc.
            $table->string('entity_type'); // Student, Employee, Vehicle, Vessel
            $table->unsignedBigInteger('entity_id')->nullable();
            $table->foreignId('policy_id')->constrained('policies')->onDelete('cascade');
            $table->unsignedBigInteger('endorsement_id')->nullable();
            $table->decimal('amount', 15, 2)->default(0); // Amount debited/credited
            $table->enum('transaction_type', ['DEBIT', 'CREDIT'])->nullable();
            $table->decimal('balance_before', 15, 2)->default(0);
            $table->decimal('balance_after', 15, 2)->default(0);
            $table->json('metadata')->nullable(); // Additional data like student details
            $table->foreignId('performed_by')->constrained('users');
            $table->timestamps();

            $table->foreign('endorsement_id')->references('id')->on('endorsements')->onDelete('set null');
            $table->index(['entity_type', 'entity_id']);
            $table->index('policy_id');
            $table->index('action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
