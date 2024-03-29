<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            // our internal snowflake ID
            $table->unsignedbigInteger('id')->primary();
            $table->string('name');
            $table->string('email');
            $table->string('avatar');
            $table->rememberToken();
            $table->string('api_token')->nullable();
            $table->ipAddress('last_ip');
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
        Schema::dropIfExists('users');
    }
}
