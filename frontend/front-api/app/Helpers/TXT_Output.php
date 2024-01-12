<?
namespace App\Helpers;

class TXT_Output {

    

    public function __construct() {
        $this->tmpname = tempnam(storage_path('app/export'),'');
        unlink($this->tmpname);
        mkdir($this->tmpname);
        $this->maxnumber = 100000;
        $this->recordcount = 0;
        $this->filenumber = 1;
        $this->fhandle = fopen($this->tmpname."/".sprintf('file_%06d.txt', $this->filenumber), 'w');
    }
    
    
    public function add($data) {
        foreach($data as $d) {
            $article = "";
//            if (isset($d->_source->name)) $article .= self::process($d->_source->name)[0] . "\n";
//            if (isset($d->_source->description)) $article .= self::process($d->_source->description)[0] . "\n";
            if (isset($d->_source->articleBody)) $article .= Flattener::process($d->_source->articleBody)[0] . "\n";
            if (isset($d->_source->text)) $article .= Flattener::process($d->_source->text)[0] . "\n";
            if ($article !="" ) $article .= "\n";
            fwrite($this->fhandle, $article);
            $this->recordcount++;
            if ($this->recordcount >= $this->maxnumber) {
                $this->nextfile();
            }
        }
    }
    
    private function nextfile() {
        fclose($this->fhandle);
        $this->filenumber++;
        $this->recordcount = 0;
        $this->fhandle = fopen($this->tmpname."/".sprintf('file_%06d.txt', $this->filenumber), 'w');
    }

    public function save($job) {
        $fhandle = fopen($this->tmpname."/query.txt", 'w');
        fwrite($fhandle, json_encode($job, JSON_PRETTY_PRINT));
        fclose($fhandle);

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

    /*
    static function process($data) {
        switch(gettype($data)) {
            case "string":
            case "integer":                
                return array($data);
                break;
            case "array":
                $ret = Array();
                foreach($data as $k => $v) {
                    $ret = array_merge($ret, self::process($v));
                }
                return $ret;
                break;
            case "object":
                $data = (array)$data;
                if (isset($data["@value"])) {
                    return array($data["@value"]);
                }
                if (isset($data["@type"])) {
                    switch ($data["@type"]) {
                        case "Organization":
                            if (isset($data["name"])) {
                                $str = $data["name"];
                                if (isset($data["location"])) {
                                    if (is_string($data["location"])) {
                                        $str .= ", " . $data["location"];
                                    }
                                    if (is_object($data["location"])) {
                                        $str .= ", " . ((array)$data["location"])["name"];
                                    }
                                }
                                return array($str);
                            } else {
                                return "";
                            }
                            break;
                        case "Person":
                            $str = "";
                            if (isset($data["name"]) && isset(((array)$data["name"])["@value"])) {
                                $str = ((array)$data["name"])["@value"];
                            } else {
                                $str = $data["name"];
                            }
                            return array($str);
                            break;
                        default:
                            $name = $url = $thumbnailurl = "";
                            if (isset($data["name"]) && isset(((array)$data["name"])["@value"])) {
                                $name = ((array)$data["name"])["@value"];
                            } else {
                                $name = $data["name"];
                            }

                            if (isset($data["url"])) {
                                $url = $data["url"];
                            }

                            if (isset($data["thumbnailUrl"])) {
                                $thumbnailurl = $data["thumbnailUrl"];
                            }


                            if ($name == "" && $url != "") {
                                $name = $url;
                            }

                            $ret = Array();
                            if ($name != "") $ret["name"] = $name;
                            if ($url != "") $ret["url"] = $url;
                            if ($thumbnailurl != "") $ret["thumbnailUrl"] = $thumbnailurl;


                            return array($ret);
                            break;
                    }
                }
                break;
        }
    } */
}