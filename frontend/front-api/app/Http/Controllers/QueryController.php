<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client as Client;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Config;
use Session;
use Illuminate\Support\Facades\Cache;
use App\Helpers\ESQuery;

class QueryController extends Controller
{
    //


    public function __construct()
    {

    }

    public function cachelessquery(Request $request) {
        return $this->query($request, False);
    }

    public function query(Request $request, $cache=True) {
        $this->authorize('search');
        $querybody = json_decode($request->getContent());


        if ($cache) {
            $md5 = md5($request->getContent()."-".Session::getId());
            Log::info("Hash : " . $md5);
            if (Cache::has($md5)) {
                Log::info("Cache found");
                $response = Cache::get($md5);
                return $response;
            }
        }

        $query = new ESQuery($querybody,$this->user->apikey);
        $response = $query->exec();
        
        if ($cache) {
            Cache::put($md5,json_encode(json_decode($response->getBody())), now()->addMinutes(config('cache.lifetime')));
            Log::info("Cache saved"); 
        }
        return $response->getBody();
    }
  

    public function flush(Request $request) {
        $this->authorize('admin');
        Cache::flush();
        return "Cache flushed";
    }

    public function status(Request $request) {

        $status = DB::table('status')->distinct()->get();

        return $status;
    }
    
}