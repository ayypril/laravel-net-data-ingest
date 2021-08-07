<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSomeColumnsUsers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        /*
         * verified column will be used to only allow verified users to create api keys.
         * suspended column is, as the name says, to disallow anyone who has a suspended account from logging in.
         * The "CheckSuspended" middleware will immediately log out anyone who is suspended, and give them an error.
         */
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('verified')->default('0');
            $table->boolean('suspended')->default('0');
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
    }
}
