<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAllowedaccessTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('allowedaccess', function (Blueprint $table) {
            $table->id();
            $table->string('identifier');
            $table->string('note',150);
            $table->timestamps();
        });


        $allowedaccess = array(
            array(
                "identifier" => "50019227",
                "note" => "Instituut voor Mediastudies (OE)",
                "created_at" => "2020-06-18 12:02:45",
                "updated_at" => NULL,
            ),
            array(
                "identifier" => "52972732",
                "note" => "Onderzoekseenheid KU Leuven Centrum voor IT & IE Recht",
                "created_at" => "2020-06-18 12:02:45",
                "updated_at" => NULL,
            ),
            array(
                "identifier" => "50518200",
                "note" => "Onderzoeksgroep Kwantitatieve Lexicologie en Variatielinguïstiek (QLVL), Leuven",
                "created_at" => "2020-06-18 12:02:45",
                "updated_at" => NULL,
            ),
            array(
                "identifier" => "50019224",
                "note" => "School voor Massacommunicatieresearch (OE)",
                "created_at" => "2020-06-18 12:02:45",
                "updated_at" => NULL,
            ),
            array(
                "identifier" => "50000253",
                "note" => "Centrum voor Politicologie (OE)",
                "created_at" => "2020-06-18 12:02:45",
                "updated_at" => NULL,
            ),
            array(
                "identifier" => "50000991",
                "note" => "LIBIS",
                "created_at" => "2020-06-18 12:02:45",
                "updated_at" => NULL,
            ),
            array(
                "identifier" => "50000168",
                "note" => "Onderzoekseenheid Publiekrecht",
                "created_at" => "2020-06-18 12:02:45",
                "updated_at" => NULL,
            ),
            array(
                "identifier" => "55645620",
                "note" => "Afdeling DTAI, Declaratieve Talen en Artificiële Intelligentie",
                "created_at" => "2020-06-18 12:02:45",
                "updated_at" => NULL,
            )


        );

        foreach($allowedaccess as $k => $v) {
            DB::table('allowedaccess')->insert($v);
        }

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('allowedaccess');
    }
}
