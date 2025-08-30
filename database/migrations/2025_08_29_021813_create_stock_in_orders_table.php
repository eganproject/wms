<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('stock_in_orders', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->date('date');
            $table->foreignId('warehouse_id')->constrained('warehouses')->onDelete('cascade');
            $table->enum('status', ['requested', 'shipped', 'completed', 'rejected'])->default('requested');
            $table->mediumText('description')->nullable();
            $table->bigInteger('requested_by')->nullable()->constrained('users')->onDelete('set null');
            $table->bigInteger('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->date('shipped_at')->nullable();
            $table->date('completed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_in_orders');
    }
};
