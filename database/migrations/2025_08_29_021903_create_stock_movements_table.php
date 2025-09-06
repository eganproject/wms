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
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->integer('item_id');
            $table->integer('warehouse_id');
            $table->date('date')->default(now());
            $table->decimal('quantity', 20, 2)->default(0);
            $table->decimal('koli', 20, 2)->default(0);
            $table->enum('type', ['stock_in', 'stock_out', 'transfer_in', 'transfer_out', 'adjustment']);
            $table->mediumText('description')->nullable();
            $table->bigInteger('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->bigInteger('reference_id')->comment('ID dari dokumen sumber (misal: id dari stock_in_orders atau transfer_requests)')->nullable();
            $table->enum('reference_type', ['stock_in_order_items', 'transfer_requests', 'stock_out', 'adjustment_items'])->comment('Tipe dokumen sumber (misal: stock_in_orders atau transfer_requests)');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
