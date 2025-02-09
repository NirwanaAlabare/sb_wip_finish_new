<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDefectsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('mysql_sb_wip')->create('defects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('line_production_id')->constrained('line_productions')->onDelete('cascade');
            $table->foreignId('defect_type_id')->constrained('defect_types')->onDelete('cascade');
            $table->foreignId('defect_area_id')->constrained('defect_areas')->onDelete('cascade');
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
        Schema::dropIfExists('defects');
    }
}
