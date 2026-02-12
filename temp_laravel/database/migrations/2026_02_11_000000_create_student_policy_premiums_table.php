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
        Schema::create('student_policy_premiums', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->foreignId('policy_id')->constrained()->onDelete('cascade');
            $table->foreignId('endorsement_id')->nullable()->constrained()->onDelete('set null');
            $table->decimal('sum_insured', 12, 2);
            $table->decimal('rate', 10, 4);
            $table->decimal('annual_premium', 10, 2);
            $table->date('date_of_joining');
            $table->date('date_of_exit');
            $table->integer('pro_rata_days');
            $table->decimal('prorata_premium', 10, 2);
            $table->decimal('gst_rate', 5, 2)->default(18);
            $table->decimal('gst_amount', 10, 2);
            $table->decimal('final_premium', 10, 2);
            $table->enum('premium_type', ['ADDITION', 'REMOVAL', 'RENEWAL'])->default('ADDITION');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_policy_premiums');
    }
};
