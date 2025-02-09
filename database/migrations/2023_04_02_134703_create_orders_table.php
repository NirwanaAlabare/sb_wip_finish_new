<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('mysql_sb_wip')->create('orders', function (Blueprint $table) {
            $table->id();
            // $table->foreignId('buyer_id')->constrained('buyers')->onDelete('cascade');
            // temporary field :
            $table->string('ws_number');
            $table->string('buyer_name');
            $table->string('style_name');
            $table->string('product_type');
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
