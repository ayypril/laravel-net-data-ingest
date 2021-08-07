<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIngestTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ingest', function (Blueprint $table) {
            $table->unsignedbigInteger('id')->primary();
            $table->ipAddress('origin_ip');
            $table->integer('srcport');
            $table->integer('destport');

            $table->string('report_type');
            $table->text('info');

            // IPs can change owners. We want current data at the time of ingest, so this will work.
            $table->string('geoip_origin')->default('fastly');

            $table->integer('asn_num');
            $table->string('asname');
            $table->string('country')->nullable();
            $table->integer('geo_code')->nullable();
            $table->string('city')->nullable();
            $table->string('continent_code')->nullable();
            $table->string('country_name')->nullable();
            $table->decimal('geoip_lat')->nullable();
            $table->decimal('geoip_long')->nullable();

            $table->integer('metro_code')->nullable();

            $table->string('postalcode')->nullable();
            $table->string('geoip_region')->nullable();
            $table->integer('user_gmt_offset')->nullable();
            $table->integer('user_utc_offset')->nullable();
            $table->string('client_conn_speed')->nullable();
            $table->string('client_conn_type')->nullable();
            $table->string('client_proxy_desc')->nullable();
            $table->string('client_proxy_type')->nullable();

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
        Schema::dropIfExists('ingest');
    }
}
