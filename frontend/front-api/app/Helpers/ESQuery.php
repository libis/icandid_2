<?php
namespace App\Helpers;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Client as Client;

class ESQuery{

    private $url = '';
    private $username = '';
    private $password = '';
    private $query = '';
    private $apikey = '';

    public function __construct($q,$apikey) {
        $this->url = config('elastic.url');
        $this->username = config('elastic.user');
        $this->password = config('elastic.password');
        $this->query = $q;
        $this->apikey = $apikey;
    }

    public function exec() {

        $client = new Client();
        Log::info("POST " . $this->url);
        Log::info(json_encode($this->query,JSON_PRETTY_PRINT));

        $response = $client->request(   'POST', 
                                        $this->url, 
                                        [
                                            'body' => json_encode($this->query), 
                                            'headers' => [
                                                'Content-Type' => 'application/json',
                                                'APIKEY' => $this->apikey
                                            ], 
                                            'auth' => [
                                                $this->username, 
                                                $this->password
                                            ], 
                                            'verify' => false
                                        ]
                                    );
        return $response;
    }

}



?>