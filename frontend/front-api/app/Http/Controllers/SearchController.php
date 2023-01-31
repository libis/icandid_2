<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Log;
use GuzzleHttp\Client as Client;
use Illuminate\Http\Request;
use App\Helpers\CSV_Output as CSV_Output;
use App\Helpers\TXT_Output as TXT_Output;
use App\Helpers\Searcher;
use App\Helpers\Flattener as Flattener;
use App\Eshelf;
use Illuminate\Support\Facades\Cache;
use Config;


class SearchController extends Controller
{

    public function __construct()
    {

        $this->searcher = new Searcher();
    }

    public function search(Request $request) {
        $this->authorize('search');

        $req = json_decode($request->getContent());
        $data = $this->searcher->query($req, $this->user->apikey);

        $data->hits->step = $this->searcher->getStep();       
        $data->hits->from = session('from');
        $data->elastic_query = $this->searcher->getQuery();
        
        $history = session('history',[]);

        if (isset($req->nav) && $req->nav ==  'first') {
            $d = new \DateTime();
            $req = json_decode($request->getContent());
            $hist = ['query' => $req, 'created_at' => $d->format('c')];
            $history[] = $hist;
            session(['history' => $history]);
        }

        // ervoor zorgen dat ook de keywords gehighlight worden
        foreach($data->hits->hits as $k => $v) {
            // neem alle gehighlite keywords en stop ze al in de keywords lijst
            if (is_object($data->hits->hits[$k]->highlight)) {
                $hl = json_decode(json_encode($v->highlight), true);
                if (isset($hl["keywords.@value"])) {
                    $data->hits->hits[$k]->highlight->keywords = $hl["keywords.@value"];
                } else {
                    $data->hits->hits[$k]->highlight->keywords = [];
                }
            }
            // loop door alle niet highlighted keywords en voeg toe aan keywords lijst als er niet reeds in
            if (isset($v->_source->keywords)) {
                if (is_object($v->_source->keywords)) {
                    $v2 = json_decode(json_encode($v->_source->keywords), true);
                    if (isset($v2["@value"])) {
                        $keyw = $v2["@value"];
                        // check of andere keywords reeds in de lijst zitten
                        if (in_array($keyw, array_map('strip_tags',$data->hits->hits[$k]->highlight->keywords))) {

                        } else {
                            // indien niet voeg toe aan lijst
                            $data->hits->hits[$k]->highlight->keywords[] = $keyw;
                        }
                    }
                } else {
                    foreach($v->_source->keywords as $k2 => $v2) {
                        if (is_object($v2)) {
                            $keyw = json_decode(json_encode($v2), true)["@value"];
                        } else {
                            $keyw = $v2;
                        }
                        // check of andere keywords reeds in de lijst zitten
                        if (in_array($keyw, array_map('strip_tags',$data->hits->hits[$k]->highlight->keywords))) {

                        } else {
                            // indien niet voeg toe aan lijst
                            $data->hits->hits[$k]->highlight->keywords[] = $keyw;
                        }
                    }
                }
            }
            if (isset($data->hits->hits[$k]->highlight->keywords) && count($data->hits->hits[$k]->highlight->keywords) == 0) {
                unset($data->hits->hits[$k]->highlight->keywords);
            }
        }
        $result = $data;
        $result->history = $history;
        return json_encode($result, JSON_UNESCAPED_SLASHES);
    }

    public function exportset(Request $request, $id, $format = 'json') {
        $this->authorize('export');

        $shelf = Eshelf::with('items')->where('user_id', $this->user_id)->where('id',$id)->first();

        switch ($format) {
            case 'json':
                $response = [];
                foreach($shelf->items as $v) {
                    $response[]  = $v->value;
                }
                return response()->json($response,200,[],JSON_UNESCAPED_SLASHES);
            break;
            case 'csv':
               
                $csvdata = fopen('php://temp/maxmemory:'. (512*1024*1024), 'r+');

                fputcsv($csvdata, CSV_Output::$header);
        
                $data = (object)[];
                $data->hits = (object)[];
                foreach($shelf->items as $v) {
                    $data->hits->hits[]  = $v->value;
                }

                $this->setDisplay($data);
                foreach($data->hits->hits as $k => $v) {
                    $rec = CSV_Output::arrayfy(CSV_Output::selectfields($v));
                    fputcsv($csvdata, $rec);
                }
        
                rewind($csvdata);

                return response(stream_get_contents($csvdata))
                        ->header('Content-Type', 'text/csv')
                        ->header("content-disposition", "attachment; filename=\"resultset.csv\"");;
            break;
            case 'txt':
                $fhandle = fopen('php://temp/maxmemory:'. (512*1024*1024), 'r+');
                
                foreach($shelf->items as $v) {
                    $article = "";
                    $d = $v->value;
                    if (isset($d->_source->name)) $article .= Flattener::process($d->_source->name)[0] . "\n";
                    if (isset($d->_source->description)) $article .= Flattener::process($d->_source->description)[0] . "\n";
                    if (isset($d->_source->articleBody)) $article .= Flattener::process($d->_source->articleBody)[0] . "\n";
                    if ($article !="" ) $article .= "\n";
                    fwrite($fhandle, $article);                    

                }

                rewind($fhandle);

                return response(stream_get_contents($fhandle))
                        ->header('Content-Type', 'text/plain')
                        ->header("content-disposition", "attachment; filename=\"resultset.txt\"");;

            break;
        }
        
    }

    public function query(Request $request) {
        $this->searcher->buildQuery($request, $size = 0) ;
        return $this->searcher->es_query;
    }

    public function getjson(Request $request) {
        $this->authorize('search');
        $req = (object)array("q" => $request->id, "searchtype" => "id");
        $data = $this->searcher->query($req, $this->user->apikey);
        if (!isset($data->hits->hits[0])) abort(404);

        return response(json_encode($data->hits->hits[0]->_source, JSON_PRETTY_PRINT+JSON_UNESCAPED_SLASHES))->header('Content-Type', 'application/json');
    }

    private function setDisplay($data) {
        foreach ($data->hits->hits as $k => $v) {
            $data->hits->hits[$k]->_display = Flattener::display($v);
        }
        return $data;
    }

    public function ddlists(Request $request) {
        $this->authorize('search');
        if (Cache::has('ddlists')) {
            return Cache::get('ddlists');
        }

        $list = [];

        $r = $this->searcher->ddlists($this->user->apikey);

        $lists["providers"] = [];
        foreach ($r->aggregations->providers->buckets as $b) {
            $lists["providers"][] = $b->key;
        }
        sort($lists["providers"]);

        $lists["publishers"] = [];
        foreach ($r->aggregations->publishers->buckets as $b) {
            $lists["publishers"][] = $b->key;
        }
        sort($lists["publishers"]);

        $lists["editions"] = [];
        foreach ($r->aggregations->editions->buckets as $b) {
            $lists["editions"][] = $b->key;
        }

        $lists["editions"] = array_diff($lists["editions"], array("ALL")); // verwarrend element verwijderen

        usort($lists["editions"],
            function($a,$b) {
                return strcasecmp($a,$b);
            }
        );

        $lists["legislationTypes"] = [];
        foreach ($r->aggregations->legislationTypes->buckets as $b) {
            $lists["legislationTypes"][] = $b->key;
        }
        sort($lists["legislationTypes"]);



        Cache::put('ddlists',$lists, now()->addMinutes(config('cache.lifetime')));

        return $lists;
    }


}

