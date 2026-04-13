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
        Schema::create('cases', function (Blueprint $table) {
            $table->id();
            $table->string('case_number')->unique();
            $table->string('title');
            $table->text('description');
            $table->string('case_type');
            $table->string('complexity_level'); // Simple, Standard, Complex
            $table->string('priority'); // Urgent, High, Medium, Low
            $table->string('status')->default('Pending'); // Pending, Ongoing, Adjourned, Judgment, Closed
            $table->foreignId('assigned_judge_id')->nullable()->constrained('users');
            $table->foreignId('assigned_lawyer_id')->nullable()->constrained('users');
            $table->foreignId('created_by')->constrained('users');
            $table->date('filing_date');
            $table->dateTime('next_hearing_date')->nullable();
            $table->date('closed_date')->nullable();
            $table->integer('hearing_interval_days')->default(30);
            $table->integer('estimated_hearings')->default(5);
            $table->integer('hearings_held')->default(0);
            $table->string('petitioner');
            $table->string('respondent');
            $table->string('court_name');
            $table->text('notes')->nullable();
            $table->boolean('priority_overridden')->default(false);
            $table->text('priority_override_reason')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cases');
    }
};
