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
        Schema::create('hearings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('case_id')->constrained('cases')->onDelete('cascade');
            $table->foreignId('judge_id')->nullable()->constrained('users')->onDelete('set null');
            $table->date('hearing_date');
            $table->time('hearing_time');
            $table->integer('duration_minutes')->default(30);
            $table->string('status')->default('Scheduled'); // Scheduled, Completed, Adjourned, Cancelled
            $table->string('hearing_type');
            $table->text('notes')->nullable();
            $table->text('outcome')->nullable();
            $table->string('courtroom')->nullable();
            $table->boolean('is_auto_scheduled')->default(false);
            $table->foreignId('scheduled_by')->nullable()->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hearings');
    }
};
