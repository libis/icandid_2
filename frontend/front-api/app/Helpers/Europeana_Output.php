<?
namespace App\Helpers;
use App\Helpers\EdmMapper as EdmMapper;


class Europeana_Output {

    public function __construct() {
        $this->tmpname = tempnam(storage_path('app/export'),'');
        unlink($this->tmpname);
        mkdir($this->tmpname);
    }
    
    
    public function add($data) {
        foreach($data as $d) {
            $mapper = EdmMapper($->_source);
            $fhandle = fopen($this->tmpname."/". $d->_source["@id"] . '.rdf', 'w');
            fwrite($fhandle, $mapper->->export());
            fclose($fhandle);
        }
    }
    

    public function save($job) {
        $fhandle = fopen($this->tmpname."/query.txt", 'w');
        fwrite($fhandle, json_encode($job, JSON_PRETTY_PRINT));
        fclose($fhandle);
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