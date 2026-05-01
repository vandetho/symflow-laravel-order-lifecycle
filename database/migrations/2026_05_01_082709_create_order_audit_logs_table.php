<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            // Single audit table for all three workflows — disambiguate via this column.
            $table->string('workflow_name');
            $table->string('event');
            $table->string('transition');
            $table->json('marking_before')->nullable();
            $table->json('marking_after')->nullable();
            $table->text('reason')->nullable();
            $table->timestamp('occurred_at');
            $table->timestamps();

            $table->index(['order_id', 'occurred_at']);
            $table->index(['order_id', 'workflow_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_audit_logs');
    }
};
