<?php

namespace App\Http\Controllers;

use App\ApiKey;
use App\RequestLog;
use Illuminate\Http\Request;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Cache;
use App\Ingest;
use GuzzleHttp\HandlerStack;
use GuzzleRetry\GuzzleRetryMiddleware;

class BackendController extends Controller
{
    private function performGeoIPLookup($ip){
        $cachekey = 'geoip'.$ip;
        /*
         * This function first checks if a certain IP address is in the cache (stored for 1mo),
         * and if it isn't, it will perform a geoip lookup and store that information.
         *
         * Afterwards, it returns the data in json form.
         * Current values include:
         * ip, as
         * geo->area_code,city,continent_code,country_code,country_code3,country_name,latitude,longitude...
         * metro_code,postal_code,region
         * timezone->gmt_offset,utc_offset
         * as well as client info such as if it's a hosting server (client->proxy_type), etc.
         */

        /*
         * https://docs.guzzlephp.org/en/stable/
         */

        if (Cache::has($cachekey)) {
        return Cache::get($cachekey);
        }
        else {
            $stack = HandlerStack::create();
            $stack->push(GuzzleRetryMiddleware::factory());

            $request = new Client(['handler' => $stack]);

            $response = $request->get(env('GEOIP_ENDPOINT_URL').$ip);
            $geojson = $response->getBody()->getContents();
            Cache::put($cachekey, $geojson, now()->addMonth());
            return $geojson;
        }
    }

    /**
     * @param $request
     * @param $ip
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    private function getMapImageForIP($request, $ip)
    {
        $key = 'image-' . $ip;

        if (Cache::store('file')->has($key)) {
            $img = Cache::store('file')->get($key);
            $log = RequestLog::createAPILogEntryWithInfo($request, '200', 'Image served from cache');
            return response($img)
                ->header('X-Cache', 'hit')
                ->header('Content-Type', 'image/png')
                ->header('X-Request-UUID', $log->id);
        } else {


            $data = json_decode($this->performGeoIPLookup($ip));

            $stack = HandlerStack::create();
            $stack->push(GuzzleRetryMiddleware::factory());

            $httprequest = new Client(['handler' => $stack]);
            $lon = $data->geo->longitude;
            $lat = $data->geo->latitude;


            $response = $httprequest->get('https://api.mapbox.com/styles/v1/mapbox/streets-v11/static/pin-s-s+000(' . $lon . ',' . $lat . ')/'
                . $lon . ',' . $lat .
                ',4/600x600@2x?access_token=' . env('MAPBOX_TOKEN'));
            $img = $response->getBody()->getContents();
            Cache::store('file')->put($key, $img, now()->addMonth());
            $log = RequestLog::createAPILogEntry($request, '200');
            return response($img)
                ->header('Content-Type', 'image/png')
                ->header('X-Request-UUID', $log->id);
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function createEvent(Request $request){

        // this request runs through the middleware previously & is inherently trusted
        $geojson = $this->performGeoIPLookup($request->input('ip'));
        $id = Ingest::newIngestEvent($request, $geojson);
        $log = RequestLog::createAPILogEntry($request, '201');
        return response()->json([
            'Status' => '201',
            'Description' => 'Created',
            'ReportID' => $id,
            'RequestUUID' => $log->id,
        ],201);
    }

    public function debugGetEventCount(){
    return Ingest::get()->count();
    }
/*
    public function test(): \Illuminate\Database\Query\Builder
    {
        return DB::table('ingest')->where('country_name', 'united states')->take(2);
    }


    public function getRandomEvent(): \Illuminate\Http\JsonResponse
    {
        return response()->json(Ingest::get()->shuffle()->take(5));
    }
*/


    public function getImageFromIP(Request $request, $ip){

        return $this->getMapImageForIP($request, $ip);
    }


    /**
     * @param Request $request
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getReportByID(Request $request, $id)
    {
        try {
            $data = Ingest::findOrFail($id);
            $log = RequestLog::createAPILogEntry($request, '200');
        }
        catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e){
            $log = RequestLog::createAPILogEntryWithInfo($request, '422', 'Could not find report for ID "'.$id.'"');
            return response()->json([
                'Status' => '422',
                'Description' => 'Unprocessable Entry',
                'RequestUUID' => $log->id,
            ],422);
        }
        return $data;
    }

    public function getImageByID(Request $request, $id){
       return $this->getMapImageForIP($request, Ingest::find($id)->origin_ip);
    }

    public function getGeoIP($request, $ip){
        return response($this->performGeoIPLookup($ip))
            ->header("Content-Type", "text/plain");
    }

    /**
     * @param Request $request
     * @return ApiKey
     */
    public function generateToken(Request $request){
        $key = new ApiKey;
        $key->token = bin2hex(openssl_random_pseudo_bytes(48));
        $key->save();
        $log = RequestLog::createAPILogEntry($request, '200');
        return $key;
    }
}
