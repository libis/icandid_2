<?php
namespace App\Helpers;
use GuzzleHttp\Client as Client;
use Illuminate\Support\Facades\Log;
use App\Helpers\Searcher as Searcher;
use Carbon\Carbon;

class UuidChecker {
    public function __construct() {
        $this->url=config('app.elastic_url');
        $this->searcher = new Searcher((object)["searchtype"=>"sitemap"]);
        $user = \App\User::where('eppn','uuiduser')
                ->where('active',1)
                ->whereDate('startdate', '<=', Carbon::now())
                ->whereDate('enddate', '>=', Carbon::now())
                ->firstOrFail();
        $this->apikey = $user->apikey;
        $this->c = 0;
    }    

    public function exec() {
        $client = new Client(['http_errors' => false]);
        $data = $this->searcher->first($this->apikey);
        
        // loop while there is more data
        while (count($data) > 0) {
            foreach($data as $k => $v) {
                if (isset($v->_source->{'@uuid'})) {
                    $this->c++;
                    $url = "https://icandid.libis.be/_/" . $v->_source->{'@uuid'};

                    $response = $client->request('GET', $url); 
                    //print($response->getStatusCode() . " " . $url . "\n");

                    if ($response->getStatusCode() == 404) {
                        print($url . "\n");
                    }
                    
                }
                sleep(0.3);
                if (isset($v->_source->{'@id'})) {
                    $this->c++;
                    $url = "https://icandid.libis.be/_/" . $v->_source->{'@id'};

                    $response = $client->request('GET', $url); 
                    //print($response->getStatusCode() . " " . $url . "\n");

                    if ($response->getStatusCode() == 404) {
                        print($url . "\n");
                    }
                    
                }
                sleep(0.3);

            }
            $data = $this->searcher->next($this->apikey);
        }

    }
   

}




?>