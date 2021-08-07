<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRequestlogTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('requestlog', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->smallInteger('request_type')->nullable(); // API vs Web, etc
            $table->string('request_status')->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            $table->text('url'); // full request URL
            $table->ipAddress('ip')->nullable();
            $table->string('user_agent')->nullable();
            $table->text('referrer')->nullable();
            $table->string('accept_language')->nullable();
            $table->string('accept_encoding')->nullable();
            $table->string('domain')->nullable();
            $table->text('browserinfo')->nullable();
            $table->string('exception')->nullable();
            $table->text('info')->nullable();
            $table->boolean('store_forever')->default('0');
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
        Schema::dropIfExists('requestlog');
    }
}
