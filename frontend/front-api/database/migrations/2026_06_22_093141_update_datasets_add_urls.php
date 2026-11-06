<?php

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
        Schema::table('datasets', function (Blueprint $table) { 
            $table->string('dataseturl', 255)->default("");
            $table->string('providerurl', 255)->default("");
        });   
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('datasets', function (Blueprint $table) { 
            $table->dropColumn('dataseturl');
            $table->dropColumn('providerurl');
        });   
    }
};
