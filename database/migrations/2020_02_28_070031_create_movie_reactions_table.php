<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMovieReactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('movie_reactions', function (Blueprint $table) {
            // $table->increments('id');
            $table->integer('movie_id')->unsigned()->index();
            $table->integer('user_id')->unsigned()->index();
            $table->boolean('liked')->default(0);
            $table->boolean('disliked')->default(0);
            $table->timestamps();

            $table->foreign('movie_id')
                ->references('id')
                ->on('movies')
                ->onDelete('cascade');

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('movie_reactions');
    }
}
