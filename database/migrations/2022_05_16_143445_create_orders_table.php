<?php

use App\Models\Order;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('merchant_id')->constrained();
            $table->foreignId('affiliate_id')->nullable()->constrained();
            // Replace floats with decimal for precise financial values
            $table->decimal('subtotal', 10, 2); // Adjust precision (10) and scale (2) based on your requirements
            $table->decimal('commission_owed', 10, 2)->default(0.00); // Adjust precision (10) and scale (2) based on your requirements
            $table->string('payout_status')->default(Order::STATUS_UNPAID);
            $table->string('discount_code')->nullable();
            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('orders');
    }
};
