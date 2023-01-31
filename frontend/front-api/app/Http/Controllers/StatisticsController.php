<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client as Client;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Config;
use Illuminate\Support\Facades\Cache;

class StatisticsController extends Controller
{
    //

    private $url = '';
    private $username = '';
    private $password = '';

    public function __construct()
    {
        $this->url = config('elastic.url');
        $this->username = config('elastic.user');
        $this->password = config('elastic.password');
        $this->apikey = "ei3a333ca0399a22b50nfcd31de9938i11e4ddf";
    }

    public function counter(Request $request) {
        $q = ["size" => 0];
        return $this->query($q);
    }

    public function newspaperpie(Request $request) {
        $q = json_decode('{
          "query": {
            "bool": {
              "must_not": [
                {
                  "terms": {
                    "publisher.name.keyword" : ["testSource"]
                  }
                }
              ]
            }
          },          
            "aggs": {
              "two": {
                "terms": {
                  "field": "publisher.name.keyword",
                  "order": {
                    "_count": "desc"
                  },
                  "size": 50
                }
              }
            },
            "size": 0
          }');
        return $this->query($q);
    }

    public function recordsdaybar(Request $request) {
        $q = json_decode('{
            "aggs": {
              "publ": {
                "terms": {
                  "field": "publisher.name.keyword",
                  "order": {
                    "_count": "desc"
                  },
                  "size": 5000
                },
                "aggs": {
                  "date": {
                    "date_histogram": {
                      "field": "datePublished",
                      "calendar_interval": "1d",
                      "time_zone": "Europe/Brussels",
                      "min_doc_count": 1
                    }
                  }
                }
              }
            },
            "size": 0,
            "query": {
              "bool": {
                "filter": [
                  {
                    "range": {
                      "datePublished": {
                        "gte": "2020-01-01",
                        "lt": "2020-01-01"
                      }
                    }
                  }
                ]
              }
            }
          }');


        $querybody = json_decode($request->getContent());  
        
        $date = now();
        $date->modify('-' . $querybody->period . ' day');
        $q->query->bool->filter[0]->range->datePublished->gte = date_format($date,"Y-m-d");
        $q->query->bool->filter[0]->range->datePublished->lt = date_format(now(),"Y-m-d");  

        if (isset($querybody->paper)) {
          $q->query->bool->must = (object)[];
          $q->query->bool->must->query_string = (object)[];
          $q->query->bool->must->query_string->fields = ["publisher.name.keyword"];
          $q->query->bool->must->query_string->query = $querybody->paper;
        }


        return $this->query($q);
    }

    public function typepie(Request $request) {
        $q = json_decode('{
            "aggs": {
              "two": {
                "terms": {
                  "field": "@type",
                  "order": {
                    "_count": "desc"
                  },
                  "size": 50
                }
              }
            },
            "size": 0
          }');        
        return $this->query($q);
    }

    public function articleline(Request $request) {
        $q = json_decode('{
            "aggs": {
              "date": {
                "date_histogram": {
                  "field": "datePublished",
                  "calendar_interval": "1w",
                  "time_zone": "Europe/Brussels",
                  "min_doc_count": 1
                }
              }
            },
            "size": 0,
            "query": {
                "bool": {
                    "filter":[
                        {
                            "range": {
                                "datePublished": {
                                    "gte":"2019-01-01",
                                    "lt":"2020-12-31"
                                }
                            }
                        }
                    ]
                }
            }}');
            $date = now();
            $date->modify('-364 day');
            $q->query->bool->filter[0]->range->datePublished->gte = date_format($date,"Y-m-d");
            $q->query->bool->filter[0]->range->datePublished->lt = date_format(now(),"Y-m-d");

        return $this->query($q);
    }

    public function ner(Request $request) {
      $es_query_np = json_decode('{
        "query": {
          "bool": {
            "must_not": [
              {
                "exists": {
                  "field": "_named_entities.generated"
                }
              }
            ]
          }
        },
        "size": 0
      }');

      $es_query_p = json_decode('{
        "query": {
          "bool": {
            "must": [
              {
                "exists": {
                  "field": "_named_entities.generated"
                }
              }
            ]
          }
        },
        "size": 0
      }');


      $processed = json_decode($this->query($es_query_p, False));
      $nonprocessed = json_decode($this->query($es_query_np, False));

      return ["count" => $processed->hits->total->value, "nonecount"=>$nonprocessed->hits->total->value];

    }

    private function query($querybody, $cache=True) {
        $client = new Client();
        Log::info("POST " . $this->url);
        Log::info(json_encode($querybody,JSON_PRETTY_PRINT));

        if ($cache) {
            $md5 = md5(json_encode($querybody));
            Log::info("Hash : " . $md5);
            if (Cache::has($md5)) {
                Log::info("Cache found");
                $response = Cache::get($md5);
                return $response;
            }
        }

        $response = $client->request(   'POST', 
                                        $this->url, 
                                        [
                                            'body' => json_encode($querybody), 
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
//        Log::info(json_encode(json_decode($response->getBody()),JSON_PRETTY_PRINT));   
        
        if ($cache) {
            Cache::put($md5,json_encode(json_decode($response->getBody())), now()->addMinutes(config('cache.lifetime')));
            Log::info("Cache saved"); 
        }
        return $response->getBody();
    }

}