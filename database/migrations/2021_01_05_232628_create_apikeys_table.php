<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateApikeysTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('apikeys', function (Blueprint $table) {
            $table->unsignedbigInteger('id')->primary();
            $table->string('name')->default("Untitled API Key");
            $table->string('token');
            $table->unsignedBigInteger('owned_by')->nullable();
            $table->foreign('owned_by')->references('id')->on('users')->nullOnDelete();
            $table->boolean('is_disabled')->default(0);
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('apikeys');
    }
}
