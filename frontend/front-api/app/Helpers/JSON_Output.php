<?
namespace App\Helpers;
use Illuminate\Support\Facades\Log;

class JSON_Output {

    public function __construct() {
        $this->tmpname = tempnam(storage_path('app/export'),'');
        unlink($this->tmpname);
        mkdir($this->tmpname);
        $this->maxnumber = 10000;
        $this->recordcount = 0;
        $this->filenumber = 1;
        $this->fhandle = fopen($this->tmpname."/".sprintf('file_%06d.jsonld', $this->filenumber), 'w');
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
            $this->recordcount++;
            if ($this->recordcount >= $this->maxnumber) {
                $this->nextfile();
            }            
        }
    }

    private function nextfile() {
        fwrite($this->fhandle, "\n]");
        fclose($this->fhandle);
        $this->filenumber++;
        $this->recordcount = 0;
        $this->first=True;
        $this->fhandle = fopen($this->tmpname."/".sprintf('file_%06d.jsonld', $this->filenumber), 'w');
    }    
    

    public function save($job) {
        $fhandle = fopen($this->tmpname."/query.txt", 'w');
        fwrite($fhandle, json_encode($job, JSON_PRETTY_PRINT));
        fclose($fhandle);

        fwrite($this->fhandle, "\n]");
        fclose($this->fhandle);
        $cmd = "zip -m -j " . $this->tmpname . ".zip " . $this->tmpname . "/*";
        exec($cmd);
        $this->cleanup();
        return basename($this->tmpname);
    }

    private function cleanup() {
        $files = scandir($this->tmpname);
        if ($files) {
            foreach ($files as $file) {
                if (!is_dir("$this->tmpname/$file")) {
                    unlink("$this->tmpname/$file");
                }
            }
        }
        rmdir($this->tmpname);
    }    
}
    