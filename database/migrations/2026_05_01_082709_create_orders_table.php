<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('reference', 16)->unique();
            $table->foreignId('customer_id')->constrained('users')->cascadeOnDelete();
            $table->string('item_summary');                       // "3 items: ..."
            $table->decimal('total', 12, 2);
            $table->string('currency', 3)->default('USD');

            // One model, three independent workflows — three marking columns.
            $table->string('lifecycle')->default('cart');         // order_lifecycle (state machine)
            $table->string('payment')->default('unpaid');         // order_payment   (state machine)
            $table->json('fulfillment_marking')->nullable();      // order_fulfillment (Petri net)

            $table->timestamp('placed_at')->nullable();
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
