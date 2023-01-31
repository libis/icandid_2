<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;


class JWTController extends Controller
{
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

    public function validation(Request $request) {
        if ($request->bearerToken()) {
            $result = $this->parse_jwt($request->bearerToken());
            if ($result) {
                return $result;
            } else {
                abort(401);
            }
        } else {
            abort(403);
        }
    }

    private function parse_jwt($jwt) {
        JWT::$leeway = $this->leeway; // $leeway in seconds
        try 
        {
            $decoded = JWT::decode($jwt, new Key($this->secret, $this->algorithm));
            return (array)$decoded;
        } 
        catch (\Exception $e) {
            return False;
        }
    }

    public function decode(Request $request) {
        if ($request->bearerToken()) {
            $tks = \explode('.', $request->bearerToken());
            list($headb64, $bodyb64, $cryptob64) = $tks;
            $header = JWT::jsonDecode(JWT::urlsafeB64Decode($headb64));
            $payload = JWT::jsonDecode(JWT::urlsafeB64Decode($bodyb64));
            return ["header"=>$header, "payload"=>$payload];
        } else {
            if (config('app.env') == 'production') {
                abort(403);
            } else {
                return ["header"=>"", "payload"=>["preferred_user"=>"u0124029@kuleuven.be", "email"=>"peter.o@kuleuven.be", "given_name" => "Peter", "family_name" => "O"]];
            }
        }
    }
}
