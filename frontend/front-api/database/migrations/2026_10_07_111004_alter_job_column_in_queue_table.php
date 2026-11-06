<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('queue', function (Blueprint $table) {
            $table->mediumText('job')->change();
        });
    }

    public function down()
    {
        Schema::table('queue', function (Blueprint $table) {
            $table->text('job')->change();
        });
    }
};