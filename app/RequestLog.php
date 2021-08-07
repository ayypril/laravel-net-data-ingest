<?php

namespace App;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Ramsey\Uuid\Uuid;

class RequestLog extends Model
{
    protected $table = 'requestlog';
    public $incrementing = false;


    public static function createEntry($request, $type, $status){
        $entry = new RequestLog;
        $entry->id = Str::orderedUuid()->toString();
        if (Auth::check()) {
            $entry->user_id = Auth::id();
        }
        $entry->request_type = $type; // int: 0 = api, 1 = web, more later???
        $entry->request_status = $status;
        $entry->url = $request->fullUrl();
        $entry->ip = $request->ip();
        // trim to ensure people trying weird things don't cause a database exception
        $entry->user_agent = substr($request->userAgent(), 0, 254);
        $entry->referrer = $request->header('referrer');
        $entry->accept_language = substr($request->header('accept-language'), 0, 254);
        $entry->accept_encoding = substr($request->header('accept-encoding'), 0, 254);
        $entry->domain = $request->header('host');
        try {
            $entry->save();
        }
        catch (Exception $e){
            // this should never happen, but if it does...
            $uuid = Uuid::uuid4()->toString();
            Log::error("[" . $uuid . "]" . ": " . $request->ip . " produced an error: " . $e->getMessage());

            return response()->json([
                'Status' => '500',
                'Description' => 'Internal Error',
                'Message' => 'An error occurred while processing your request.',
                'RequestUUID' => $uuid,
            ],500);
        }
        return $entry;
    }


    public static function createAPILogEntry($request, $status){
        return self::CreateEntry($request, 0, $status);
    }


    public static function createAPILogEntryWithInfo($request, $status, $info){
        $entry = self::CreateAPILogEntry($request, $status);

        $entry->info = $info;
        $entry->save();
        return $entry;
    }

}
