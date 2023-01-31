<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateDatasetTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('datasets', function (Blueprint $table) {
            $table->boolean('available')->default(True);
            $table->boolean('ismedia')->default(False);
            $table->unsignedBigInteger('recordcount')->default(0);
            $table->date('recordcountdate')->default('1976-05-18');

            $table->index('available');
            $table->index('ismedia');
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
            $table->dropColumn('available');
            $table->dropColumn('ismedia');
            $table->dropColumn('recordcount');
            $table->dropColumn('recordcountdate');
        });
    }
}
