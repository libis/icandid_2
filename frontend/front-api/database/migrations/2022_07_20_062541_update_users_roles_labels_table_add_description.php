<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateUsersRolesLabelsTableAddDescription extends Migration
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
            $table->string('description', 450)->default("");
        });
        Schema::table('roles', function (Blueprint $table) {
            $table->string('description', 255)->default("");
        });
        Schema::table('labels', function (Blueprint $table) {
            $table->string('description', 255)->default("");
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
            $table->dropColumn('description');
        }); 
        Schema::table('roles', function (Blueprint $table) {
            $table->dropColumn('description');
        }); 
        Schema::table('labels', function (Blueprint $table) {
            $table->dropColumn('description');
        }); 
    }
}
