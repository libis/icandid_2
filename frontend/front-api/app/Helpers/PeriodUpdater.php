<?php
namespace App\Helpers;
use GuzzleHttp\Client as Client;
use Illuminate\Support\Facades\Log;


class PeriodUpdater {
    private $esquery = "";
    private $url = '';
    private $username = '';
    private $password = '';

    public function __construct() {
        $this->esquery = json_decode('{
            "query": {
                "match_all": {}
            },
            "size": 0,
            "sort": {
                "_score": "desc"
            },
            "from": 0,
            "aggs": {
                "datasets": {
                    "terms": {
                        "field": "isBasedOn.isPartOf.@id",
                        "size": 10000
                    },
                    "aggs": {
                        "until": {
                            "max": {
                                "field": "updatetime"
                            }
                        },
                        "from": {
                            "min": {
                                "field": "updatetime"
                            }
                        }
                    }
                }
            }
        }');
        $this->url = config('elastic.url');
        $this->username = config('elastic.user');
        $this->password = config('elastic.password');
        $this->apikey = "ei3a333ca0399a22b50nfcd31de9938i11e4ddf";
    }    

    public function exec() {
        $client = new Client();
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

        foreach ($r->aggregations->datasets->buckets as $k => $v) {
            $this->dataset = \App\Dataset::where("internalident",$v->key)->first();
            if ($this->dataset != Null) {
                $this->dataset->from = substr($v->from->value_as_string,0,10);
                $this->dataset->until = substr($v->until->value_as_string,0,10);
                $this->dataset->save();
            }
        }
    
    }

}
?>