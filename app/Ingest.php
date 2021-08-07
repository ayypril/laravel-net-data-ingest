<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Kra8\Snowflake\HasSnowflakePrimary;

class Ingest extends Model
{
    use HasSnowflakePrimary;
    protected $table = 'ingest';

    public static function newIngestEvent($request, $geodata) {
        $ip = $request->ip;

        /*
{
    "ip": "13.225.165.25",
    "as": {
      "name": "amazon.com inc.",
      "number": 16509
    },
    "geo": {
      "area_code": 0,
      "city": "tokyo",
      "continent_code": "AS",
      "country_code": "JP",
      "country_code3": "JPN",
      "country_name": "japan",
      "latitude": 35.680,
      "longitude": 139.750,
      "metro_code": 392001,
      "postal_code": "100-0001",
      "region": "13"
    },
    "timezone": {
      "gmt_offset": 900,
      "utc_offset": 900
    },
    "client": {
      "conn_speed": "broadband",
      "conn_type": "wired",
      "proxy_description": "cloud",
      "proxy_type": "hosting"
    }
}
         */

        $geojson = json_decode($geodata);
        $ingest = new Ingest;
        $ingest->report_type = $request->report_type;
        if (isset($request->info)) {
            $ingest->info = $request->info;
        }
        else {
            $ingest->info = "";
        }
        $ingest->origin_ip = $ip;
        $ingest->srcport = $request->src_port;
        $ingest->destport = $request->dest_port;
        $ingest->asn_num = $geojson->as->number;
        $ingest->asname = $geojson->as->name;
        $ingest->country = $geojson->geo->country_code;
        $ingest->geo_code = $geojson->geo->area_code;
        $ingest->city = $geojson->geo->city;
        $ingest->continent_code = $geojson->geo->continent_code;
        $ingest->metro_code = $geojson->geo->metro_code;

        $ingest->postalcode = $geojson->geo->postal_code;

        $ingest->country_name = $geojson->geo->country_name;
        $ingest->geoip_lat = $geojson->geo->latitude;
        $ingest->geoip_long = $geojson->geo->longitude;
        $ingest->user_utc_offset = $geojson->timezone->utc_offset;
        $ingest->user_gmt_offset = $geojson->timezone->gmt_offset;
        $ingest->client_conn_speed = $geojson->client->conn_speed; // almost always "broadband"
        $ingest->client_conn_type = $geojson->client->conn_type; // usually "wired" or "mobile"

         if (isset($geojson->client->proxy_description)) {
            $ingest->client_proxy_desc = $geojson->client->proxy_description;
        }

        if (isset($geojson->client->proxy_type)) {
            $ingest->client_proxy_type = $geojson->client->proxy_type;
        }

        $ingest->is_hosting = self::findIfHosting($geojson);
        $ingest->hostname = self::performRDNSQuery($ip);
        $ingest->save();
        return $ingest->id;
    }


    private static function performRDNSQuery($ip){
        return gethostbyaddr($ip);
    }

    private static function findIfHosting($geojson){
        if ($geojson->client->proxy_type == "") {
            return false;
        }
        else {
            return true;
        }
    }
}
