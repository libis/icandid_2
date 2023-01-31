<?php
namespace App\Helpers;
use GuzzleHttp\Client as Client;
use Illuminate\Support\Facades\Log;


class DocCounter {
    private $job;
    private $esquery = "";
    private $url = '';
    private $username = '';
    private $password = '';

    public function __construct() {
        $this->esquery = json_decode('{
            "size": 0,
            "query": {
              "bool": {
                "should": [
                  {"term": {"isBasedOn.isPartOf.@id": "metronl"} }
                ]
              }
            }
          }');
        $this->url = config('elastic.url');
        $this->username = config('elastic.user');
        $this->password = config('elastic.password');
        $this->apikey = "ei3a333ca0399a22b50nfcd31de9938i11e4ddf";
        $this->today = new \DateTime();
        $this->job = \App\Dataset::where("available",1)->where('recordcountdate','<',$this->today->format("Y-m-d"))->orderBy('recordcountdate','ASC')->first();
    }    

    public function exec() {
        if ($this->hasJobToDo()) {
            $client = new Client();
            $this->esquery->query->bool->should[0] = ["term" => ['isBasedOn.isPartOf.@id' => $this->job->internalident] ];
            Log::info(print_r($this->esquery, True));
            $response = $client->request('POST',  $this->url , [
                    'body' => json_encode($this->esquery), 
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
            $r = json_decode($response->getBody()->getContents());            

            $this->job->recordcount = $r->hits->total->value;
            $this->job->recordcountdate = $this->today->format("Y-m-d");
            $this->job->save();
        }
    }

    public function hasJobToDo() {
      if ($this->job != Null) return True;
      else return False;
    }
}
?>