<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;



class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected $user_id = 0;
    protected $user_eppn = "";
    protected $user = [];

    private $secret = '';
    private $header = Null;
    private $payload = Null;
    private $leeway = 60;
    private $algorithm = 'HS256';

    public function __construct() {
        $this->secret = config('jwt.secret');
        $this->leeway = config('jwt.leeway');
        $this->algorithm = config('jwt.algorithm');
    }

    public function authorize($module = Null){

        if (config('app.env') != 'local') {
            list($authType, $bearerToken) = \explode(" ",$_SERVER['HTTP_AUTHORIZATION']);
            if ($authType == "Bearer" && $bearerToken != "" && $bearerToken != Null) {
                $result = $this->parse_jwt($bearerToken);
                if ($result) {
                    $this->user_eppn = $result["preferred_user"];
                } else {
                    $this->user_eppn = "";
                }                
            } else { 
                $this->user_eppn = $_SERVER["HTTP_EPPN"];
            }
        } 
        else {
            $this->user_eppn = "u0124029@kuleuven.be";
        }

        if (config('app.debug')) {
            Log::info("Access attempt by " . $this->user_eppn);
        }

        try {
            $this->user = \App\User::where('eppn',$this->user_eppn)
                ->where('active',1)
                ->whereDate('startdate', '<=', Carbon::now())
                ->whereDate('enddate', '>=', Carbon::now())
                ->firstOrFail();
            $this->user_authenticated = true;
        } catch (ModelNotFoundException $e) {
            $this->user_authenticated = false;
        }
    
        if ($this->user_authenticated) {
            Log::info($this->user_eppn . " authorized");
            $this->user_id = $this->user->id;

            if ($module != Null) {
                if (in_array($module, array_map(function($val) { return $val->reference ; }, $this->user->getPermissions()["resources"]))) {
                    Log::info("Access to " . $module  . " granted to " . $this->user_eppn);
                } else {
                    abort(403);
                }
            }
        }
    }

    private function parse_jwt($jwt) {
        JWT::$leeway = $this->leeway; // $leeway in seconds
        try 
        {
            $tks = \explode('.', $jwt);
            list($headb64, $bodyb64, $cryptob64) = $tks;
            $payload = JWT::jsonDecode(JWT::urlsafeB64Decode($bodyb64));
            return (array)$payload;
        } 
        catch (\Exception $e) {
            return False;
        }
    }


}