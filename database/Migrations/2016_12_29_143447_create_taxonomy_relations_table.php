<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTaxonomyRelationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('taxonomy_relations', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('type_taxonomy_id');
            $table->unsignedInteger('lft_taxonomy_id');
            $table->unsignedInteger('rgt_taxonomy_id');
            $table->integer('priority');

            $table->foreign('type_taxonomy_id')->references('id')->on('taxonomies');
            $table->foreign('lft_taxonomy_id')->references('id')->on('taxonomies');
            $table->foreign('rgt_taxonomy_id')->references('id')->on('taxonomies');

            $table->timestamps();
            $table->softdeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('taxonomy_relations', function(Blueprint $table) {
            $table->dropForeign(['type_taxonomy_id']);
            $table->dropForeign(['lft_taxonomy_id']);
            $table->dropForeign(['rgt_taxonomy_id']);
        });

        Schema::dropIfExists('taxonomy_relations');
    }
}
