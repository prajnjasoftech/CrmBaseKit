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
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('company')->nullable();
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('country')->default('US');
            $table->enum('status', ['active', 'inactive', 'churned'])->default('active');
            $table->text('notes')->nullable();
            $table->foreignId('converted_from_lead_id')->nullable()->constrained('leads')->nullOnDelete();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('business_id')->nullable()->constrained('businesses')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'assigned_to']);
            $table->index('business_id');
            $table->index('converted_from_lead_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
