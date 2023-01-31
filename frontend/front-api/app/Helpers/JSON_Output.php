<?
namespace App\Helpers;
use Illuminate\Support\Facades\Log;

class JSON_Output {

    public function __construct() {
        $this->tmpname = tempnam(storage_path('app/export'),'');

        $this->fhandle = fopen($this->tmpname, 'w');
        $this->first = True;
    }
    
    public function add($data) {
        foreach($data as $d) {
            if ($this->first) {
                fwrite($this->fhandle, "[\n");
                $this->first = False;
            } else {
                fwrite($this->fhandle, ",\n");
            }
            unset($d->highlight);
            fwrite($this->fhandle,json_encode($d, JSON_PRETTY_PRINT+JSON_UNESCAPED_SLASHES));
        }
    }
    
    public function save($job) {
        $fhandle = fopen($this->tmpname."_query.txt", 'w');
        fwrite($fhandle, json_encode($job, JSON_PRETTY_PRINT));
        fclose($fhandle);

        fwrite($this->fhandle, "\n]");
        fclose($this->fhandle);
        rename($this->tmpname, $this->tmpname.".jsonld");
        $cmd = "zip -m -j " . $this->tmpname . ".zip " . $this->tmpname . ".jsonld" . " " . $this->tmpname."_query.txt";
        exec($cmd);
        return basename($this->tmpname);
    }
}
    