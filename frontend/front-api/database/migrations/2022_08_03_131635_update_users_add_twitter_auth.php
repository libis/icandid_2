<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateUsersAddTwitterAuth extends Migration
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
            $table->string('twitter_api_key', 25)->default("");
            $table->string('twitter_api_key_secret', 50)->default("");
            $table->string('twitter_bearer_token', 127)->default("");
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
            $table->dropColumn('twitter_api_key');
            $table->dropColumn('twitter_api_key_secret');
            $table->dropColumn('twitter_bearer_token');
        }); 
    }
}
