<?
namespace App\Helpers;
use Illuminate\Support\Facades\Log;

class JSON_Output {

    public function __construct($enrichments = []) {
        $this->tmpname = tempnam(storage_path('app/export'),'');
        unlink($this->tmpname);
        mkdir($this->tmpname);
        $this->maxnumber = 10000;
        $this->recordcount = 0;
        $this->filenumber = 1;
        $this->fhandle = fopen($this->tmpname."/".sprintf('file_%06d.jsonld', $this->filenumber), 'w');
        $this->first = True;
        $this->enrichments = $enrichments;
        
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

            if (isset($d->_source->{'prov:wasAttributedTo'})) {
                // sometimes prov:wasAttributedTo and/or prov:wasAssociatedFor are objects and not arrays of objects.
                // this fixes that problem, makes next step (remove unwanted) easier
                
                if (is_object($d->_source->{'prov:wasAttributedTo'})){
                    $tmp = clone $d->_source->{'prov:wasAttributedTo'};
                    $d->_source->{'prov:wasAttributedTo'} = Array();
                    $d->_source->{'prov:wasAttributedTo'}[] = $tmp;
                }
                foreach($d->_source->{'prov:wasAttributedTo'} as $k => $v) {
                    if(is_object($v->{'prov:wasAssociatedFor'})) {
                        $tmp = clone $v->{'prov:wasAssociatedFor'};
                        $d->_source->{'prov:wasAttributedTo'}[$k]->{'prov:wasAssociatedFor'} = Array();
                        $d->_source->{'prov:wasAttributedTo'}[$k]->{'prov:wasAssociatedFor'}[] = $tmp;
                    }
                }

                // remove unwanted enrichments
                if (isset($d->_source->{'prov:wasAttributedTo'})){
                    for($i = count($d->_source->{'prov:wasAttributedTo'})-1 ; $i >= 0; $i--) {
                        for($j = count($d->_source->{'prov:wasAttributedTo'}[$i]->{'prov:wasAssociatedFor'})-1 ; $j >= 0; $j--) {
                            if (!in_array($d->_source->{'prov:wasAttributedTo'}[$i]->{'prov:wasAssociatedFor'}[$j]->name,$this->enrichments)){
                                unset($d->_source->{'prov:wasAttributedTo'}[$i]->{'prov:wasAssociatedFor'}[$j]);
                            }
                        }
                        //cleanup prov:wasAssociatedFor als er niks meer in zit
                        if (count($d->_source->{'prov:wasAttributedTo'}[$i]->{'prov:wasAssociatedFor'}) == 0) {
                            unset($d->_source->{'prov:wasAttributedTo'}[$i]);
                        }
                    }
                    // cleanup prov:wasAttributedTo als er niks meer in zit
                    if (!isset($d->_source->{'prov:wasAttributedTo'}[0]->{'prov:wasAssociatedFor'})) {
                        unset($d->_source->{'prov:wasAttributedTo'});
                    }
                }
            }    
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
    