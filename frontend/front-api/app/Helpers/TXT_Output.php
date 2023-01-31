<?
namespace App\Helpers;

class TXT_Output {

    

    public function __construct() {
        $this->tmpname = tempnam(storage_path('app/export'),'');
        $this->fhandle = fopen($this->tmpname, 'w');
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
        }
    }
    
    public function save($job) {
        $fhandle = fopen($this->tmpname."_query.txt", 'w');
        fwrite($fhandle, json_encode($job, JSON_PRETTY_PRINT));
        fclose($fhandle);

        fclose($this->fhandle);
        rename($this->tmpname, $this->tmpname.".txt");
        $cmd = "zip -m -j " . $this->tmpname . ".zip " . $this->tmpname . ".txt" . " " . $this->tmpname."_query.txt";
        exec($cmd);
        return basename($this->tmpname);
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