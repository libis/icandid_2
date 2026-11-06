<?php
namespace App\Helpers;
use GuzzleHttp\Client as Client;
use Illuminate\Support\Facades\Log;


class LogRotate {
    private $logfiles = [];

    public function __construct() {
        $this->logfiles = glob(storage_path('logs') . "/*.log");
        print_r($this->logfiles);
    }

    public function exec() {
        foreach($this->logfiles as $filename){
            $this->gzip($filename . ".1");
            $this->rotate($filename);
            print("rename " . $filename."\n");
            rename($filename, $filename.".1");
            touch($filename);
            chmod($filename, 0666);
        }
    }


    private function gzip($filename) {
        print("gzip " . $filename."\n");
        $data = file_get_contents($filename);
        $gzdata = gzencode($data, 9);
        file_put_contents($filename. ".gz", $data);
    }

    private function rotate($filename) {
        
        unlink($filename.".9.gz");
        for ($i=8; $i>=1; $i--) {
            print("rotate " . $filename.".".$i.".gz -> " . $filename.".".($i+1).".gz\n");
            rename($filename.".".$i.".gz", $filename.".".($i+1).".gz");

        }
    }
}