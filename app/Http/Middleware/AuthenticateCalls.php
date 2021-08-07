<?php

namespace App\Http\Middleware;

use App\RequestLog;
use Closure;
use App\ApiKey;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;

class AuthenticateCalls
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // validate URL signature
        // basic setup: sha256(url|tokenid|token|create|expire)
        // url is something like "/api/v1/CreateEvent" without the query string.
        $unixtime = now()->timestamp;

        $url = $request->getPathInfo();

        //$request->validate(
        $validator = Validator::make($request->all(), [
            'id' => 'bail|required|integer|exists:apikeys,id',
            'time' => 'bail|required|integer',
            'expires' => 'bail|required|integer',
            'signature' => 'bail|required|string',
            'nonce' => 'nullable|string',
        ]);
        if ($validator->fails()) {
            //$validator->messages();
            $log = RequestLog::createAPILogEntryWithInfo($request, '422', $validator->messages());
            return response()->json([
                'Status' => '422', // "Unprocessable Entry"
                'Message' => 'A required authentication parameter was missing or malformed.',
                'RequestUUID' => $log->id,
            ], 422);
        } else {

            // this should never fail due to the validation above
            $token = ApiKey::find($request->id);

            $usertime = $request->time;
            $userexpires = $request->expires;


            if (!$userexpires > $usertime) {
                $log = RequestLog::createAPILogEntryWithInfo($request, '422', "Expiry time \"" . $userexpires .
                    "\" is not greater than declared creation time \"".$usertime."\"." );
                return response()->json([
                    'Status' => '422',
                    'Message' => 'A required authentication parameter was missing or malformed.',
                    'RequestUUID' => $log->id,
                ], 422);
            }


            if ($userexpires < $unixtime) {
                $log = RequestLog::createApiLogEntryWithInfo($request, '403', "Expiry time \"" . $userexpires .
                    "\" is less than current unix time \"" . $unixtime . "\".");
                return response()->json([
                    'Status' => '403',
                    'Message' => 'Your request has expired.',
                    'RequestUUID' => $log->id,
                ], 403);
            }

            if ($userexpires > $usertime+86400) {
                $log = RequestLog::createApiLogEntryWithInfo($request, '422', "Request valid for too long: expiry time \"" . $userexpires .
                    "\" is greater than  \"" . $usertime . "\" + 86400.");
                return response()->json([
                    'Status' => '422',
                    'Message' => 'A required authentication parameter was missing or malformed.',
                    'RequestUUID' => $log->id,
                ], 422);
            }

            // if the request's signature time on the client is greater than our current unix time
            if ($usertime > $unixtime) {
                $log = RequestLog::createAPILogEntryWithInfo($request, '422', "Signature time \"" . $usertime .
                    "\" is not greater than our current server time \"".$unixtime."\"." );
                return response()->json([
                    'Status' => '422',
                    'Message' => 'A required authentication parameter was missing or malformed.',
                    'RequestUUID' => $log->id,
                ], 422);
            }

            /*
             * If a request has a nonce header, change the signature to also require it.
             * Otherwise, adding the nonce is useless!
             */

            if($request->has('nonce')){
                $ourhash = hash('sha256', $url . '|' . $token->id . '|' . $token->token . '|' . $usertime . '|' . $userexpires . '|' . $request->nonce);
            }
            else {
                $ourhash = hash('sha256', $url . '|' . $token->id . '|' . $token->token . '|' . $usertime . '|' . $userexpires);
            }

            if ($request->signature === $ourhash) {

                if($request->has('nonce')){
                    $noncekey = $usertime . '_' . $userexpires . '_' . $request->id . '_' . $request->signature . '_' . $request->nonce;

                    if (Cache::has($noncekey)) {
                        $log = RequestLog::createApiLogEntryWithInfo($request, '403', "Nonce \"" . $noncekey .
                            "\" already exists.");
                        return response()->json([
                            'Status' => '403',
                            'Message' => 'A request with this nonce has already been processed.',
                            'RequestUUID' => $log->id,
                        ], 403);
                    }
                    else {
                        Cache::put($noncekey, 'true', now()->addWeek());
                    }
                }
                if (rand(1, 100) === 1){
                    DB::table('cache')->where('expiration', '<', $unixtime)->delete();
                    //DB::raw('delete from cache where expiration < UNIX_TIMESTAMP()');
                }
                return $next($request);

            } else {
                $log = RequestLog::createApiLogEntryWithInfo($request, '403', "Provided request signature \"" .
                    $request->signature . "\" does not match our signature \"" . $ourhash . "\".");
                return response()->json([
                    'Status' => '403',
                    'Description' => 'The signature we calculated did not match the signature you provided.',
                    'RequestUUID' => $log->id,
                ], 403);
            }
        }
    }
}
