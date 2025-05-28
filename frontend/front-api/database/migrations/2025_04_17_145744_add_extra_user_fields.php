<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddExtraUserFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //

        Schema::table('users', function (Blueprint $table) {
            $table->string("faculty",80);
            $table->string("function",30);
            $table->unsignedInteger("language_id")->default(0);
            $table->string("promotor",80);          
            $table->boolean('termsofuse')->default(True);
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

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('faculty');
            $table->dropColumn('function');
            $table->dropColumn('language_id');
            $table->dropColumn('promotor');
            $table->dropColumn('termsofuse');
        });
    }
};
