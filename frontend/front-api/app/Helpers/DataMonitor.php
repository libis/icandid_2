<?php
namespace App\Helpers;
use App\Mail\Mail as Email;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Client as Client;


class DataMonitor {
    public function __construct() {
        $this->url = config('elastic.url');
        $this->username = config('elastic.user');
        $this->password = config('elastic.password');
        $this->apikey = "ei3a333ca0399a22b50nfcd31de9938i11e4ddf";
    }

    public function exec() {
        $q1 = json_decode('{
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
                        "lt": "2022-09-05"
                      }
                    }
                  }
                ]
              }
            }
          }');

        $q2 = json_decode('{
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
                    "gte": "2020-09-05",
                    "lt": "2020-09-05"
                    }
                }
                }
            ]
            }
        }
        }');

        $today = date_format(now(),"Y-m-d");
        $yesterday = date_format(date_sub(now(),date_interval_create_from_date_string("1 days")),"Y-m-d");

        $q1->query->bool->filter[0]->range->datePublished->lt = $yesterday;
        $q2->query->bool->filter[0]->range->datePublished->gte = $yesterday;
        $q2->query->bool->filter[0]->range->datePublished->lt = $today;

        $r1 = $this->query($q1);
        $r2 = $this->query($q2);

        $arr = [];
        $body = "";

        foreach ($r2->aggregations->publ->buckets as $publ) {
          print $publ->key . "\n";
          $data = $this->find($publ->key, $r1->aggregations->publ->buckets);
          
          if ($data) {

            foreach($data->date->buckets as $datapoint) {
              $arr[]  = $datapoint->doc_count;
            }

            $value = $publ->date->buckets[0]->doc_count;
            if ($this->anomalyDetected($value,$arr)) {
              $body .= $publ->key . "\t\t\t" . "Anomaly Detected\n";
            } else {
              $body .= $publ->key . "\t\t\t" . "OK\n";
            }
          }
        }

        $subject = "Data Anomaly Detection Report";

        Mail::to("peter.o@kuleuven.be")->send(new Email(nl2br($body),$subject));
    }

    private function find($needle, $haystack) {
      foreach($haystack as $item) {
        if ($item->key == $needle) {
          return $item;
        }
      }
      return False;
    }

    private function query($querybody) {
        $client = new Client();
        Log::info("POST " . $this->url);
        Log::info(json_encode($querybody,JSON_PRETTY_PRINT));

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
        return json_decode($response->getBody()->getContents());                                    
    }

    private function StandardDeviation($arr)
    {
        $num_of_elements = count($arr);
          
        $variance = 0.0;
          
                // calculating mean using array_sum() method
        $average = $this->Average($arr);
          
        foreach($arr as $i)
        {
            // sum of squares of differences between 
                        // all numbers and means.
            $variance += pow(($i - $average), 2);
        }
          
        return (float)sqrt($variance/$num_of_elements);
    }

    private function Average($arr)
    {
        $num_of_elements = count($arr);

                // calculating mean using array_sum() method
        $average = array_sum($arr)/$num_of_elements;
          
         
        return (float)$average;
    }

    private function Range($avg, $sd) {
        return [($avg-$sd),($avg+$sd)];
    }

    private function anomalyDetected($value, $arr) {
        $range = $this->Range($this->Average($arr), $this->StandardDeviation($arr));
        return ($value < $range[0] || $value > $range[1]);
    }


}



?>