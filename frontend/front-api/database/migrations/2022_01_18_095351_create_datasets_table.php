<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDatasetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('datasets', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('internalident', 45);
            $table->string('description', 255)->default(null);
            $table->string('requestor', 100)->default(null);
            $table->string('requestoremail', 200)->default(null);
            $table->date('from')->default('2010-01-01');
            $table->date('until')->default('2060-12-31');
            $table->string('query',1000)->default(null);
            $table->string('provider', 45)->default(null);

            $table->index('provider');
            $table->index(['from','until']);
            
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
        Schema::dropIfExists('datasets');
    }
}
