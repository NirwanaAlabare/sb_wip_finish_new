<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('mysql_sb_wip')->create('order_details', function (Blueprint $table) {
            $table->id();
            // $table->foreignId('buyer_id')->constrained('buyers')->onDelete('cascade');
            // temporary field :
            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');
            $table->string('product_color');
            $table->integer('qty');
            $table->integer('qty_output');
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
}
