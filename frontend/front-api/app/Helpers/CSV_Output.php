<?
namespace App\Helpers;
use Illuminate\Support\Facades\Log;
    
class CSV_Output {

    public static $header = array("id","type","legislationType","author","author_alternateName","creator","creator_alternateName","name","genre","description","articleBody","text","director","editor","producer","productionCompany","actor","contributor","musicBy","trailerName","trailerDescription","printEdition","articleSection","sender","sender_alternateName","recipient","recipient_alternateName","legislationPassedBy","legislationResponsible","retweet","datePublished","url","provider","publisher","link","pagination","review","keywords","mentions","duration","contentUrl","about","inLanguage","contentLocation","associatedMedia","sdDatePublished","updatetime");

    public function __construct() {
        $this->tmpname = tempnam(storage_path('app/export'),'');
        unlink($this->tmpname);
        mkdir($this->tmpname);
        $this->maxnumber = 100000;
        $this->recordcount = 0;
        $this->filenumber = 1;
        $this->first = True;
        $this->separator = chr(9);
        $this->open();
    }
    public function open() {
        $this->fhandle = fopen($this->tmpname."/".sprintf('file_%06d.csv', $this->filenumber), 'w');
    }
    public function add($data) {
       
        if ($this->first) {
            fputcsv($this->fhandle, self::$header, $this->separator);
            $this->first = false;
        }
        foreach($data as $d) {
            $rec = self::arrayfy(self::selectfields($d));
            foreach($rec as $k => $v) {
                $rec[$k] = str_replace(array("\n","\r","\t",),array(" "," "," "), $v);
            }
            fputcsv($this->fhandle, $rec, $this->separator);
            $this->recordcount++;
            if ($this->recordcount >= $this->maxnumber) {
                $this->nextfile();
                if ($this->first) {
                    fputcsv($this->fhandle, self::$header, $this->separator);
                    $this->first = false;
                }
            }            
        }
    }

    private function nextfile() {
        fclose($this->fhandle);
        $this->filenumber++;
        $this->recordcount = 0;
        $this->first=True;
        $this->fhandle = fopen($this->tmpname."/".sprintf('file_%06d.csv', $this->filenumber), 'w');
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

    protected function cleanup() {
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

    static function selectfields($d) {
        $disp = [];
        $disp["id"] = ((array)$d->_source)["@id"];
        $disp["type"] = ((array)$d->_source)["@type"];
        if (isset($d->_source->legislationType)) $disp["legislationType"] = Flattener::process($d->_source->legislationType);
        if (isset($d->_source->creator)) $disp["creator"] = Flattener::process($d->_source->creator);
        if (isset($d->_source->author)) $disp["author"] = Flattener::process($d->_source->author);
        if (isset($d->_source->creator->alternateName)) $disp["creator_alternateName"] = Flattener::process($d->_source->creator->alternateName);
        if (isset($d->_source->author->alternateName)) $disp["author_alternateName"] = Flattener::process($d->_source->author->alternateName);
        if (isset($d->_source->name)) $disp["name"] = Flattener::process($d->_source->name);
        if (isset($d->_source->genre)) $disp["genre"] = Flattener::process($d->_source->genre);
        if (isset($d->_source->description)) $disp["description"] = Flattener::process($d->_source->description);
        if (isset($d->_source->articleBody)) $disp["articleBody"] = Flattener::process($d->_source->articleBody);
        if (isset($d->_source->text)) $disp["text"] = Flattener::process($d->_source->text);
        if (isset($d->_source->director)) $disp["director"] = Flattener::process($d->_source->director);
        if (isset($d->_source->editor)) $disp["editor"] = Flattener::process($d->_source->editor);
        if (isset($d->_source->producer)) $disp["producer"] = Flattener::process($d->_source->producer);
        if (isset($d->_source->productionCompany)) $disp["productionCompany"] = Flattener::process($d->_source->productionCompany);
        if (isset($d->_source->actor)) $disp["actor"] = Flattener::process($d->_source->actor);

        if (isset($d->_source->contributor)) $disp["contributor"] = Flattener::process($d->_source->contributor);
        if (isset($d->_source->musicBy)) $disp["musicBy"] = Flattener::process($d->_source->musicBy);
        if (isset($d->_source->trailer->name)) $disp["trailerName"] = Flattener::process($d->_source->trailer->name);
        if (isset($d->_source->trailer->description)) $disp["trailerDescription"] = Flattener::process($d->_source->trailer->description);


        if (isset($d->_source->printEdition)) $disp["printEdition"] = Flattener::process($d->_source->printEdition);
        if (isset($d->_source->articleSection)) $disp["articleSection"] = Flattener::process($d->_source->articleSection);
        if (isset($d->_source->sender)) $disp["sender"] = Flattener::process($d->_source->sender);
        if (isset($d->_source->sender->alternateName)) $disp["sender_alternateName"] = Flattener::process($d->_source->sender->alternateName);
        if (isset($d->_source->recipient)) $disp["recipient"] = Flattener::process($d->_source->recipient);
        if (isset($d->_source->recipient->alternateName)) $disp["recipient_alternateName"] = Flattener::process($d->_source->recipient->alternateName);
        if (isset($d->_source->legislationPassedBy)) $disp["legislationPassedBy"] = Flattener::process($d->_source->legislationPassedBy);
        if (isset($d->_source->legislationResponsible)) $disp["legislationResponsible"] = Flattener::process($d->_source->legislationResponsible);
        if (isset($d->_source->datePublished)) $disp["datePublished"] = Flattener::process($d->_source->datePublished);
        if (isset($d->_source->url)) $disp["url"] = Flattener::process($d->_source->url);
        if (isset($d->_source->publisher)) $disp["publisher"] = Flattener::process($d->_source->publisher);
        if (isset($d->_source->sameAs)) $disp["link"] = Flattener::process($d->_source->sameAs);
        if (isset($d->_source->pagination)) $disp["pagination"] = Flattener::process($d->_source->pagination);

        if (isset($d->_source->review)) $disp["review"] = Flattener::process($d->_source->review);


        if (isset($d->_source->keywords)) $disp["keywords"] = Flattener::process($d->_source->keywords);
        if (isset($d->_source->mentions)) $disp["mentions"] = Flattener::process($d->_source->mentions);
        if (isset($d->_source->duration)) $disp["duration"] = Flattener::process($d->_source->duration);
        if (isset($d->_source->contentUrl)) $disp["contentUrl"] = Flattener::process($d->_source->contentUrl);
        if (isset($d->_source->about)) $disp["about"] = Flattener::process($d->_source->about);
        if (isset($d->_source->inLanguage)) $disp["inLanguage"] = Flattener::process($d->_source->inLanguage);
        if (isset($d->_source->contentLocation)) $disp["contentLocation"] = Flattener::process($d->_source->contentLocation);
        if (isset($d->_source->associatedMedia)) $disp["associatedMedia"] = Flattener::process($d->_source->associatedMedia);
        if (isset($d->_source->sdDatePublished)) $disp["sdDatePublished"] = Flattener::process($d->_source->sdDatePublished);
        if (isset($d->_source->updatetime)) $disp["updatetime"] = Flattener::process($d->_source->updatetime);

        // @ voor de twitter handle zetten
        if (is_array($d->_source->isBasedOn)) {
            $provider = $d->_source->isBasedOn[0]->provider;
        } else {
            $provider = $d->_source->isBasedOn->provider;
        }
        if (((array)$provider)["@id"] == "twitter") {
            $l = array("author_alternateName","sender_alternateName","recipient_alternateName");
            foreach($l as $n) {
                if (isset($disp[$n])) {
                    foreach($disp[$n] as $k => $v) {
                        $disp[$n][$k] = "@" . $v;
                    }
                }
            }
        }

        // uitvissen of een tweet een retweet is
        $disp["retweet"] = 0;
        if (isset($d->_source->identifier)) {
            if (is_array($d->_source->identifier)) {
                foreach ($d->_source->identifier as $ident) {
                    if ($ident->name ==  "retweeted_tweet_id") {
                        $disp["retweet"] = 1;    
                    }
                }
            } else {
                if ($d->_source->identifier->name == "retweeted_tweet_id") {
                    $disp["retweet"] = 1;
                }
            }
        }

        if (isset($d->_source->isBasedOn->provider) || isset($d->_source->isBasedOn[0]->provider)) {
            if (is_array($d->_source->isBasedOn)) {
                $disp["provider"] = Flattener::process($d->_source->isBasedOn[0]->provider->name);
            } else {
                $disp["provider"] = Flattener::process($d->_source->isBasedOn->provider->name);
            }
        } else {
            if (isset($d->_source->provider)) {
                $disp["provider"] = Flattener::process($d->_source->provider);
            }
        }
        return $disp;
    }

    static function arrayfy($disp) {
        return array(
            $disp["id"],
            $disp["type"],
            join(", ", (isset($disp["legislationType"])?self::nonl($disp["legislationType"]):[])),
            join(", ", (isset($disp["author"])?self::nonl($disp["author"]):[])),
            join(", ", (isset($disp["author_alternateName"])?self::nonl($disp["author_alternateName"]):[])),
            join(", ", (isset($disp["creator"])?self::nonl($disp["creator"]):[])),
            join(", ", (isset($disp["creator_alternateName"])?self::nonl($disp["creator_alternateName"]):[])),
            join(" ",  (isset($disp["name"])?self::nonl($disp["name"]):[])),
            join(", ",  (isset($disp["genre"])?self::nonl($disp["genre"]):[])),
            join(" ",  (isset($disp["description"])?self::nonl($disp["description"]):[])),
            join(" ", (isset($disp["articleBody"])?self::nonl($disp["articleBody"]):[])),
            join(" ", (isset($disp["text"])?self::nonl($disp["text"]):[])),

            join(", ",  (isset($disp["director"])?self::nonl($disp["director"]):[])),
            join(", ",  (isset($disp["editor"])?self::nonl($disp["editor"]):[])),
            join(", ",  (isset($disp["producer"])?self::nonl($disp["producer"]):[])),
            join(", ",  (isset($disp["productionCompany"])?self::nonl($disp["productionCompany"]):[])),

            join(", ",  (isset($disp["actor"])?self::nonl($disp["actor"]):[])),



            join(", ",  (isset($disp["contributor"])?self::nonl($disp["contributor"]):[])),
            join(", ",  (isset($disp["musicBy"])?self::nonl($disp["musicBy"]):[])),
            join(" ",  (isset($disp["trailerName"])?self::nonl($disp["trailerName"]):[])),
            join(" ",  (isset($disp["trailerDescription"])?self::nonl($disp["trailerDescription"]):[])),

            join(" ", (isset($disp["printEdition"])?self::nonl($disp["printEdition"]):[])),
            join(" ", (isset($disp["articleSection"])?self::nonl($disp["articleSection"]):[])),
            join(", ", (isset($disp["sender"])?self::nonl($disp["sender"]):[])),
            join(", ", (isset($disp["sender_alternateName"])?self::nonl($disp["sender_alternateName"]):[])),
            join(", ", (isset($disp["recipient"])?self::nonl($disp["recipient"]):[])),
            join(", ", (isset($disp["recipient_alternateName"])?self::nonl($disp["recipient_alternateName"]):[])),
            join(", ", (isset($disp["legislationPassedBy"])?self::nonl($disp["legislationPassedBy"]):[])),
            join(", ", (isset($disp["legislationResponsible"])?self::nonl($disp["legislationResponsible"]):[])),
            (isset($disp["retweet"])?strval($disp["retweet"]):"0"),
            join(", ", (isset($disp["datePublished"])?self::nonl($disp["datePublished"]):[])),
            join(", ", (isset($disp["url"])?self::nonl($disp["url"]):[])),
            join(", ", (isset($disp["provider"])?self::nonl($disp["provider"]):[])),
            join(", ", (isset($disp["publisher"])?self::nonl($disp["publisher"]):[])),
            join(", ", (isset($disp["link"])?self::nonl($disp["link"]):[])),
            join(", ", (isset($disp["pagination"])?self::nonl($disp["pagination"]):[])),

            join(" --- ", (isset($disp["review"])?self::nonl($disp["review"]):[])),





            join(", ", (isset($disp["keywords"])?self::nonl($disp["keywords"]):[])),
            join(", ", (isset($disp["mentions"])?self::nonl($disp["mentions"]):[])),
            join(", ", (isset($disp["duration"])?self::nonl($disp["duration"]):[])),
            join(", ", (isset($disp["contentUrl"])?self::nonl($disp["contentUrl"]):[])),
            join(", ", (isset($disp["about"])?self::nonl($disp["about"]):[])),
            join(", ", (isset($disp["inLanguage"])?self::nonl($disp["inLanguage"]):[])),
            join(", ", (isset($disp["contentLocation"])?self::nonl($disp["contentLocation"]):[])),
            join(", ", (isset($disp["associatedMedia"])?self::nonl($disp["associatedMedia"]):[])),
            join(", ", (isset($disp["sdDatePublished"])?self::nonl($disp["sdDatePublished"]):[])),
            join(", ", (isset($disp["updatetime"])?self::nonl($disp["updatetime"]):[]))
        );
    }

    static function nonl($s) {
        if (gettype($s) == "array") {
            $out = array();
            foreach ($s as $v) {
                $out[] = self::nonl($v);
            }
            if (count($out) > 0 && gettype($out[0]) == "array") {
                $out = array_merge(...$out);
            }
            return $out;
        } else {
            return ($s == Null?$s:str_replace(array("\n","\r","\t",),array(" "," "," "), $s));
        }
    }
}