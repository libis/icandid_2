<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateContentTableAddTitle extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('content', function (Blueprint $table) {
            $table->string('title_nl', 128);
            $table->string('title_en', 128);
        });                
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
        Schema::table('content', function (Blueprint $table) {
            $table->dropColumn('title_nl');
            $table->dropColumn('title_en');
        });      
    }
}
