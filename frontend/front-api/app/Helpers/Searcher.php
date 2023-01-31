<?
namespace App\Helpers;
use GuzzleHttp\Client as Client;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use GuzzleHttp\Exception\ClientException;

class Searcher {

    private $esquery = "";
    private $url = '';
    private $username = '';
    private $password = '';
    private $step = 20;
    private $from = 0;
    private $hits = 0;
    private $scroll = "3000s";
    private $exportstep = 1000;
    private $fieldmapping = [
/*        'any' => ["name.@value", "headline", "isBasedOn.provider.name.keyword",'isBasedOn.provider.alternateName.keyword', 
                    "publisher.name.keyword", "articleBody", "creator.name",'author.name','sender.name','sender.alternateName', 
                    "description","keywords.@value", "text"],
        'title' => ["name.@value", "headline"],
        'author' => ['creator.name','author.name','sender.name','sender.alternateName'],
        'text' => ["articleBody", "description", "text"],
        'publicationdate' => ['datePublished'],
        'period' => ['period'],
        'publisher' => ['publisher.name.keyword'],
        'provider' => ['isBasedOn.provider.name.keyword','isBasedOn.provider.alternateName.keyword'],
        'dataset' => ['isBasedOn.isPartOf.name.keyword','isBasedOn.isPartOf.@id'],
        'type' => ['@type'] */
        'any' => ["*_name", "*_headline", "isBasedOn.provider.name.keyword",'isBasedOn.provider.alternateName.keyword', 
            "publisher.name.keyword", "*_articleBody", "creator.name",'author.name','creator.alternateName','author.alternateName','sender.name','sender.alternateName', 
            "*_description","*_keywords", "*_text"],
        'title' => ["*_name", "*_headline"],
        'author' => ['creator.name','author.name','creator.alternateName','author.alternateName'],
        'sender' => ['sender.name','sender.alternateName'],
        'text' => ["*_articleBody", "*_description", "*_text"],
        'publicationdate' => ['datePublished'],
        'period' => ['period'],
        'publisher' => ['publisher.name.keyword'],
        'provider' => ['isBasedOn.provider.name.keyword','isBasedOn.provider.alternateName.keyword'],
        'dataset' => ['isBasedOn.isPartOf.name.keyword','isBasedOn.isPartOf.@id'],
        'edition' => ['printEdition.keyword'],
        'type' => ['@type'],         
        'legislationType' => ['legislationType.keyword']
    ];
    private $sortmapping = [
        'relevance' => ["_score"=>"desc"],
        'datePublished' => ["datePublished"=>"desc"] ,
        'author' => ["creator.name.keyword"=>"asc"],
        'title' => [["headline.keyword"=>"asc"], ["name.@value.keyword"=>"asc"]]
    ];
    private $filtermapping = [
        'author' => 'creator.name.keyword',
        'publisher' => 'publisher.name.keyword',
        'provider' => 'isBasedOn.provider.name.keyword',
        'edition' => 'printEdition.keyword'
    ];
    private $aggs = [
    	"publisher" => [
    		"terms" => [
    			"field"=>"publisher.name.keyword",
    			"size"=>30
            ]
        ],/*
    	"type" => [
    		"terms" => [
    			"field" => "@type",
    			"size" => 30
            ]
        ],*/
        "author" => [
    		"terms" => [
    			"field"=>"creator.name.keyword",
    			"size"=>30
            ]   
        ],/*
    	"datePublished" => [
    		"terms" => [ 
    			"field"=>"datePublished",
    			"size"=>30
            ]
        ],
    	"articleSection" => [
    		"terms" => [
    			"field"=>"articleSection.keyword",
    			"size"=>30
            ]
        ],*/
    	"provider" => [
    		"terms" => [
    			"field"=>"isBasedOn.provider.name.keyword",
    			"size"=>30
            ]
        ],
    	"edition"=> [
    		"terms" => [
    			"field"=>"printEdition.keyword",
    			"size"=>30
            ]
        ],
        "retweets" => [
             "filter" => [
                "term" => [
                    "identifier.name.keyword"=>"retweeted_tweet_id"
                ]
            ]
        ]
    ];
    private $source = [
        "exclude" => ["*-*_*"]
    ];

    public function __construct($query = Null) {
        $this->url = config('elastic.url');
        $this->username = config('elastic.user');
        $this->password = config('elastic.password');
        if ($query) $this->buildQuery($query);
    }

    public function first($apikey) {
        $client = new Client();
        $this->esquery->size = $this->exportstep;
//        $this->esquery->sort = [];
//        $this->esquery->sort['datePublished'] = 'asc';
        Log::info("POST " . $this->url . "&scroll=" . $this->scroll . "s");
        Log::info(json_encode($this->esquery, JSON_PRETTY_PRINT));
        $response = $client->request(   'POST', 
                                        $this->url . "&scroll=". $this->scroll, 
                                        [
                                            'body' => json_encode($this->esquery), 
                                            'headers' => [
                                                'Content-Type' => 'application/json',
                                                'APIKEY' => $apikey
                                            ], 
                                            'auth' => [
                                                $this->username, 
                                                $this->password
                                            ], 
                                            'verify' => false
                                        ]
                                    );
        $r = json_decode($response->getBody()->getContents());
        $this->_scroll_id = $r->_scroll_id;
        Log::info($response->getStatusCode() . " " . $response->getReasonPhrase() . " : " . count($r->hits->hits));                                    
        if (count($r->hits->hits) > 0) {
            $this->hits = $r->hits->total->value;
            Log::info($r->hits->total->value);
            return $r->hits->hits;
        } else {
            return False;        
        }

    }


    public function hits() {
        return $this->hits;
    }

    public function next($apikey) {
        $client = new Client();

        $p = parse_url($this->url);
        $url = $p["scheme"] . "://" . $p["host"] . ((isset($p["port"]) && $p["port"] != "")?(":" . $p["port"]):"") . "/_search/scroll";
        Log::info("POST " . $url);

        $query = (object)["scroll" => $this->scroll, "scroll_id" => $this->_scroll_id];
        Log::info(json_encode($query, JSON_PRETTY_PRINT));

        $response = $client->request(   'POST', 
                                        $url, 
                                        [
                                            'body' => json_encode($query), 
                                            'headers' => [
                                                'Content-Type' => 'application/json',
                                                'APIKEY' => $apikey
                                            ], 
                                            'auth' => [
                                                $this->username, 
                                                $this->password
                                            ], 
                                            'verify' => false
                                        ]
                                    );
        $r = json_decode($response->getBody()->getContents());
        Log::info($response->getStatusCode() . " " . $response->getReasonPhrase()); // . " : " . count($r->hits->hits));                                    
        $this->_scroll_id = $r->_scroll_id;
        if (count($r->hits->hits) > 0) {
            Log::info($r->hits->total->value);
            return $r->hits->hits;
        } else {
            Log::info(print_r($r, True));
            return False;        
        }
    }


    public function query($request, $apikey) {
        $this->buildQuery($request);

        $this->esquery->highlight = (object)[
            "max_analyzer_offset" => 200000,
            "pre_tags" => ["<span class='highlighted'>"],
            "post_tags" => ["</span>"],
            "fields" => (object)["*" => (object)["number_of_fragments"=> 0]],
            "require_field_match" => false
        ];

        $this->esquery->aggs = $this->aggs;

        if (isset($request->nav)) {
            if ($request->nav == 'first') {
                $this->from = 0;
            } else {
                $this->from = session('from') + $this->step;
            }
        } else {
            $this->from = 0;
        }

        session(['from' => $this->from]);
        $this->esquery->from = $this->from;
        $this->esquery->size = $this->step;

        Log::info("POST " . $this->url);
        Log::info(json_encode($this->esquery, JSON_PRETTY_PRINT));
        //$client = new Client(['http_errors' => false]);
        $client = new Client();
        try {
            $response = $client->request(   'POST', 
                                        $this->url, 
                                        [
                                            'body' => json_encode($this->esquery), 
                                            'headers' => [
                                                'Content-Type' => 'application/json',
                                                "APIKEY" => $apikey

                                            ], 
                                            'auth' => [
                                                $this->username, 
                                                $this->password
                                            ], 
                                            'verify' => false
                                        ]
                                    );
        } catch(Exception $e) {
            Log::info(print_r($e, True));
        }

        $result = json_decode($response->getBody()->getContents());

        Log::info($response->getStatusCode() . " " . $response->getReasonPhrase()); // . " : " . count($r->hits->hits));                                    

        foreach ($result->hits->hits as $k => $v) {
            if (!isset($v->highlight) ) {
                $result->hits->hits[$k]->highlight = (object)[];
            } 
        }
        
        if (isset($result->aggregations->retweets)) {
            $tweet_count = 0;
            
            foreach ($result->aggregations->provider->buckets as $k => $v) {
                if ($v->key == "Twitter") {
                    $tweet_count = $v->doc_count;
                }
            }

            if ($tweet_count > 0) {
                $result->aggregations->retweet = [
                    "buckets"=>[
                        ["key"=>"onlyretweet", "doc_count"=>$result->aggregations->retweets->doc_count],
                        ["key"=>"noretweet", "doc_count"=>$tweet_count - $result->aggregations->retweets->doc_count],
                    ]
                ];
            }

            unset($result->aggregations->retweets);
        }





        return $result;
    }

    public function getStep() {
        return $this->step;
    }

    public function getQuery() {
        return $this->esquery;
    }

    private function parseSimpleRequest($req) {
        $query = json_decode('{
            "query": {
                "query_string" : {
                    "query" : "",
                    "fields": []
                }
            }
        }');
        $query->query->query_string->query = $req->q;
        $query->query->query_string->fields = $this->fieldmapping['any'];


        return $query;
    }

    private function parseIdRequest($req) {
        $query = json_decode('{
            "query": {
                "match": {
                    "_id": ""
                }
            }
        }');
        $query->query->match->_id = $req->q;
        return $query;
    }

    private function parseNormalRequest($req) {
        $query = json_decode('{
            "query": {
                "bool": {
                    "must": [ 
                        {
                            "multi_match": {
                                "query": "",
                                "fields": []
                            }
                        }
                    ],
                    "should": [],
                    "filter": []
                }
            }
        }');


        if (count($req->datasets) == 0) {   // if no datasets are selected 
            $req->q = "";                   // make it so no results are returned
        }

        $query->query->bool->must[0]->multi_match->query = $req->q;
        $query->query->bool->must[0]->multi_match->fields = $this->fieldmapping['any'];        
        if ($req->type == 'all') $query->query->bool->must[0]->multi_match->operator = "and";
        if ($req->type == 'one') $query->query->bool->must[0]->multi_match->operator = "or";
        if ($req->type == 'phrase') $query->query->bool->must[0]->multi_match->type = "phrase";

        if (isset($req->publications) && count($req->publications) > 0) {
            $query->query->bool->filter[] = ["terms"=>["publisher.name.keyword"=>$req->publications]];
        }
        if (isset($req->datasets) && count($req->datasets) > 0) {
            $query->query->bool->filter[] = ["terms"=>["isBasedOn.isPartOf.@id"=>$req->datasets]];
        }


        switch ($req->period) {
            case "today":
                $date = date("Y-m-d");
            break;
            case "yesterday":
                $date = date("Y-m-d",strtotime("-1 day"));
            break;
            case "week":
                $date = date("Y-m-d",strtotime("-1 week"));
            break;
            case "month":
                $date = date("Y-m-d",strtotime("-1 month"));
            break;
            case "year":
                $date = date("Y-m-d",strtotime("-1 year"));
            break;
            case "entire":
            default:
                $date = "";
            break;

        }
        if ($date != "") {
            $query->query->bool->filter[] = ["range" => ["datePublished" => ["gte" => $date]]];
        }

        return $query;
    }

    private function parseAdvancedRequest($req) {
        $q = $req->q;
        $subq = [];
        Log::info(print_r($q, True));
        foreach ($q as $k => $v) {
            $tmpq = (object)[];
            $tmpq->operator = $v->operator;
            $tmpq->query=(object)[];
            if ($v->field == 'publicationdate') {
                $tmpq->query->query_string = (object)[];
                $tmpq->query->query_string->fields = $this->fieldmapping[$v->field];
                $tmpq->query->query_string->query = date("Y-m-d", strtotime($v->query));

            } else if ($v->field == 'period') {
                $tmpq->query = json_decode('{
                    "range": {
                        "datePublished" : {
                            "gte" : "1970-01-01",
                            "lte": "2100-12-31"
                        }
                    }
                }');
                if (isset($v->queryfrom) && $v->queryfrom != "") $tmpq->query->range->datePublished->gte = date("Y-m-d", strtotime($v->queryfrom));
                if (isset($v->queryto) && $v->queryto != "")  $tmpq->query->range->datePublished->lte = date("Y-m-d", strtotime($v->queryto));
            } else if ($v->field == 'language') {
                $tmpq->query->bool = (object)[];
                $tmpq->query->bool->filter = [];
                $datasets = array_map(function($d) { return $d->internalident; },DB::table('datasets')->select('internalident')->join('dataset_language',"datasets.id", "=", "dataset_language.dataset_id")->where("dataset_language.language_id", "=", $v->query)->get()->all());
                $tmpq->query->bool->filter[] = ["terms"=>["isBasedOn.isPartOf.@id"=>$datasets]];
            } else if ($v->field == 'label') {
                $tmpq->query->bool = (object)[];
                $tmpq->query->bool->filter = [];
                $datasets = array_map(function($d) { return $d->internalident; },DB::table('datasets')->select('internalident')->join('dataset_label',"datasets.id", "=", "dataset_label.dataset_id")->where("dataset_label.label_id", "=", $v->query)->get()->all());
                $tmpq->query->bool->filter[] = ["terms"=>["isBasedOn.isPartOf.@id"=>$datasets]];
            } else if ($v->field == 'retweet') {
                $tmpq->query->bool = (object)[];
                $tmpq->query->bool->filter = [];
                if ($v->query == "only") {
                    $tmpq->query->bool->filter[] = ["bool"=>["must"=>["terms"=>["identifier.name.keyword"=>array("retweeted_tweet_id")]]]];
                }
                if ($v->query == "no") {
                    $tmpq->query->bool->filter[] = ["bool"=>["must_not"=>["terms"=>["identifier.name.keyword"=>array("retweeted_tweet_id")]]]];
                }
            } else {
                $tmpq->query->query_string = (object)[];
                $tmpq->query->query_string->fields = $this->fieldmapping[$v->field];
                switch ($v->condition) {
                    case 'contains':
                        $tmpq->query->query_string->query = $v->query;
                    break;
                    case 'starts':
                        $tmpq->query->query_string->query = $v->query . "*";

                    break;
                    case 'phrase':
                        $tmpq->query->query_string->query = '"' . $v->query . '"';
                    break;
                }
            }
            $subq[] = $tmpq;
        }
        $parser = new Parser($subq);
        $esq = (object)[];
        $esq->query = $parser->generateQuery();

        return $esq;
    }


    private function applyFilters($query, $filters) {
        $terms = [];
        $retweet_terms = [];
        foreach($filters as $f) {
            $part = explode(":", $f, 2);  // 2 = enkel splitten op eerste dubbele punt

            if ($part[0] == "retweet") {
                if ($part[1] == "onlyretweet") {
                    $retweet_terms[] = ["bool"=>["must"=>["terms"=>["identifier.name.keyword"=>array("retweeted_tweet_id")]]]];
                }
                if ($part[1] == "noretweet") {
                    $retweet_terms[] = ["bool"=>["must_not"=>["terms"=>["identifier.name.keyword"=>array("retweeted_tweet_id")]]]];
                }

            } else {
                if (isset($terms[$this->filtermapping[$part[0]]]))  {
                    $terms[$this->filtermapping[$part[0]]][] = $part[1];
                } else {
                    $terms[$this->filtermapping[$part[0]]] = [$part[1]];
                }
            }


        }

        $new = json_decode('{
            "query": {
                "bool": {
                    "must": [],
                    "should": [],
                    "filter": []
                }
            }
        }');

        $new->query->bool->must = $query->query;
        foreach($terms as $k => $v) {
            $new->query->bool->filter[] = (object)["terms" => (object)[$k=>$v]];
        }

        if (count($retweet_terms) > 0) {
            $tmp = ["bool"=>["should"=>[]]];
            foreach($retweet_terms as $k => $v) {
                $tmp["bool"]["should"][] = (object)$v;
            }
            $new->query->bool->filter[] = (object)$tmp;
        }

        return $new;
    }

    private function buildQuery($req, $size = 1000) {
        
        switch ($req->searchtype) {
            case "simple":
                $query = $this->parseSimpleRequest($req);
            break;
            case "normal":
                $query = $this->parseNormalRequest($req);
            break;
            case "advanced":
                $query = $this->parseAdvancedRequest($req);
            break;
            case "id":
                $query = $this->parseIdRequest($req);
            break;
        }

        if (isset($req->f) && count($req->f) > 0) {
            $query = $this->applyFilters($query, $req->f);
        }

        $query->size = $this->step;

        $query->_source = $this->source;

        if (isset($req->s)) $query->sort = $this->sortmapping[$req->s];
        $this->from = 0;

        $query->from = $this->from;

        $this->esquery = $query;
        
        
    }

    public function ddlists($apikey) {
        
        $client = new Client();
        $query = '{
            "query": {
                "match_all": {}
            },
            "size": 0,
            "aggregations":{
                "providers":{
                    "terms": {
                        "field":"isBasedOn.provider.name.keyword",
                        "size":500
                    }
                },
                "publishers":{
                    "terms": {
                        "field":"publisher.name.keyword",
                        "size":500
                    }
                },
                "editions":{
                    "terms": {
                        "field":"printEdition.keyword",
                        "size":500
                    }
                },
                "legislationTypes":{
                    "terms": {
                        "field":"legislationType.keyword",
                        "size":500
                    }
                }                
            }
        }';
        Log::info("POST " . $this->url);
        Log::info($query);
        $response = $client->request(   'POST', 
                                        $this->url,
                                        [
                                            'body' => $query, 
                                            'headers' => [
                                                'Content-Type' => 'application/json',
                                                'APIKEY' => $apikey
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


}


?>