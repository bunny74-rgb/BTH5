<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('product_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_report_id')
                  ->nullable()
                  ->constrained('orders_reports')
                  ->onDelete('cascade');
            $table->unsignedBigInteger('product_id');
            $table->integer('total_sold')->default(0);
            $table->decimal('revenue', 10, 2)->default(0);
            $table->decimal('cost', 10, 2)->default(0);
            $table->decimal('profit', 10, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_reports');
    }
};