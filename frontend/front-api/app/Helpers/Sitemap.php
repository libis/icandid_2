<?php
namespace App\Helpers;
use GuzzleHttp\Client as Client;
use Illuminate\Support\Facades\Log;
use App\Helpers\Searcher as Searcher;
use Carbon\Carbon;

class Sitemap {
    public function __construct() {
        $this->url=config('app.elastic_url');
        $this->searcher = new Searcher((object)["searchtype"=>"sitemap"]);
        $user = \App\User::where('eppn','publicuser')
                ->where('active',1)
                ->whereDate('startdate', '<=', Carbon::now())
                ->whereDate('enddate', '>=', Carbon::now())
                ->firstOrFail();
        $this->apikey = $user->apikey;
    }    

    public function exec() {
        $data = $this->searcher->first($this->apikey);
        
        // temporary storage leegmaken
        $files = glob(storage_path('app/tmp')."/*"); // get all file names
        foreach($files as $file){ // iterate files
        if(is_file($file))
            echo "Deleting " . $file . "\n";
            unlink($file); // delete file
        }

        $subfiles = [];
        $refs = [];

        // loop while there is more data
        while ($data) {
            foreach($data as $k => $v) {
                $refs[]=Array("loc"=>$v->_source->url,"lastmod"=>$v->_source->sdDatePublished);
            }
            sleep(1);
            if (count($refs) == 50000) {
                $subfiles[] = $this->makexmlfile($refs);
                $refs = [];
            }
            $data = $this->searcher->next($this->apikey);
        }

        if (count($refs) > 0) {
            $subfiles[] = $this->makexmlfile($refs);
        }


        $fp = fopen(storage_path('app/tmp') . '/sitemap.xml', "w");
        fwrite($fp, "<?xml version=\"1.0\"?><sitemapindex xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">");
        foreach($subfiles as $url) {
            fwrite($fp, "<sitemap><loc>" . $url . "</loc></sitemap>");
        }
        fwrite($fp, "</sitemapindex>");
        fclose($fp);


        // public storage leegmaken
        $files = glob(public_path('sitemap/')."*"); // get all file names
        foreach($files as $file){ // iterate files
        if(is_file($file))
            echo "Deleting " . $file . "\n";
            unlink($file); // delete file
        }

        // files van temp storage overkopieren naar public storage

        $files = glob(storage_path('app/tmp')."/*"); // get all file names
        foreach($files as $file){ // iterate files
        if(is_file($file))
            echo "Moving " . $file . "\n";
            $src = $file;
            $dst = public_path('sitemap/') . basename($file);
            rename($src, $dst);
        }
    }


    private function makexmlfile($chunk) {
        $filename = trim(file_get_contents('/proc/sys/kernel/random/uuid')) . ".xml.gz";
        $fp = fopen(storage_path('app/tmp') . "/" . $filename, "w");
        $xml = '<?xml version="1.0"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
        foreach($chunk as $p) {
            $xml .= '<url>';
            $xml .= '<loc>' . $p["loc"] . '</loc>';
            $xml .= '<lastmod>' . $p["lastmod"] . '</lastmod>';
            $xml .= '</url>';
        }
        $xml .= "</urlset>";
        fwrite($fp, gzencode($xml,9));
        fclose($fp);
        return config("app.url")."/sitemap/".$filename;
    }

    

}




?>