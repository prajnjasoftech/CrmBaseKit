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
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('company')->nullable();
            $table->enum('source', ['website', 'referral', 'advertisement', 'cold_call', 'social_media', 'other'])->default('other');
            $table->enum('status', ['new', 'contacted', 'qualified', 'proposal', 'negotiation', 'won', 'lost'])->default('new');
            $table->text('notes')->nullable();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('business_id')->nullable()->constrained('businesses')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'assigned_to']);
            $table->index('business_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
