<?
namespace App\Helpers;
use Illuminate\Support\Facades\Log;

class Flattener {

    public function __construct() {
    }    

    public static function display($hit) {
        $display = Array();
                    
        $display["id"] = ((array)$hit->_source)["@id"];
        $display["type"] = ((array)$hit->_source)["@type"];
        if (isset($hit->_source->keywords)) $display["keywords"] = self::process($hit->_source->keywords);
        if (isset($hit->_source->name)) $display["name"] = self::process($hit->_source->name);
        if (isset($hit->_source->alternateName)) $display["alternateName"] = self::process($hit->_source->alternateName);
        if (isset($hit->_source->author)) $display["author"] = self::process($hit->_source->author); 
        if (isset($hit->_source->creator)) $display["creator"] = self::process($hit->_source->creator); 
        if (isset($hit->_source->editor)) $display["editor"] = self::process($hit->_source->editor); 
        if (isset($hit->_source->sender)) $display["sender"] = self::process($hit->_source->sender); 
        if (isset($hit->_source->recipient)) $display["recipient"] = self::process($hit->_source->recipient); 

        if (isset($hit->_source->contributor)) $display["contributor"] = self::process($hit->_source->contributor); 
        if (isset($hit->_source->illustrator)) $display["illustrator"] = self::process($hit->_source->illustrator); 
        if (isset($hit->_source->translator)) $display["translator"] = self::process($hit->_source->translator); 
        if (isset($hit->_source->description)) $display["description"] = self::process($hit->_source->description);
        if (isset($hit->_source->articleBody)) $display["articleBody"] = self::process($hit->_source->articleBody);
        if (isset($hit->_source->text)) $display["text"] = self::process($hit->_source->text);
        if (isset($hit->_source->url)) $display["url"] = self::process($hit->_source->url);
        if (isset($hit->_source->datePublished)) {
            $display["datePublished"] = self::process($hit->_source->datePublished);
            foreach($display["datePublished"] as $k => $v) {
                if (preg_match('/([0-9]{4})-([0-9]{2})-([0-9]{2})T([0-9]{2}:[0-9]{2}:[0-9]{2})/', $v, $matches)) {
                    $display["datePublished"][$k] = $matches[1];
                }
            } 
        }

        if (isset($hit->_source->dateCreated)) $display["dateCreated"] = self::process($hit->_source->dateCreated);
        if (isset($hit->_source->license)) $display["license"] = self::process($hit->_source->license);
        if (isset($hit->_source->isBasedOn->provider) || isset($hit->_source->isBasedOn[0]->provider)) {
            if (is_array($hit->_source->isBasedOn)) {
                $display["provider"] = self::process($hit->_source->isBasedOn[0]->provider->name);
            } else {
                $display["provider"] = self::process($hit->_source->isBasedOn->provider->name);
            }
        } else {
            if (isset($hit->_source->provider)) {
                $display["provider"] = self::process($hit->_source->provider);
            }
        }

        if (isset($hit->_source->locationCreated)) $display["locationCreated"] = self::process($hit->_source->locationCreated);
        if (isset($hit->_source->publisher)) $display["publisher"] = self::process($hit->_source->publisher);
        if (isset($hit->_source->sdDatePublished)) $display["sdDatePublished"] = self::process($hit->_source->sdDatePublished);
        if (isset($hit->_source->sdPublisher)) $display["sdPublisher"] = self::process($hit->_source->sdPublisher);
        if (isset($hit->_source->sdLicense)) $display["sdLicense"] = self::process($hit->_source->sdLicense);
        if (isset($hit->_source->isPartOf)) $display["isPartOf"] = self::process($hit->_source->isPartOf);
        if (isset($hit->_source->hasPart)) $display["hasPart"] = self::process($hit->_source->hasPart);
        if (isset($hit->_source->pagination)) {
            $display["pagination"] = self::process($hit->_source->pagination);
        } else {
            if (isset($hit->_source->pageStart)) {
                $pagestart = self::process($hit->_source->pageStart);
                $pageend = self::process($hit->_source->pageEnd);
                $display["pagination"] = Array();
                foreach($pagestart as $k => $v) {
                    $display["pagination"][] = $v . " - " . $pageend[$k];
                }
            }
        }
        if (isset($hit->_source->numberOfPages)) $display["numberOfPages"] = self::process($hit->_source->numberOfPages);

        if (isset($hit->_source->volumeNumber)) $display["volumeNumber"] = self::process($hit->_source->volumeNumber);
        if (isset($hit->_source->issueNumber)) $display["issueNumber"] = self::process($hit->_source->issueNumber);

        if (isset($hit->_source->Genre)) $display["Genre"] = self::process($hit->_source->Genre);
        if (isset($hit->_source->isbn)) $display["isbn"] = self::process($hit->_source->isbn);
        if (isset($hit->_source->issn)) $display["issn"] = self::process($hit->_source->issn);

        if (isset($hit->_source->includedInDataCatalog)) $display["includedInDataCatalog"] = self::process($hit->_source->includedInDataCatalog);
        if (isset($hit->_source->Dataset)) $display["Dataset"] = self::process($hit->_source->Dataset);
        
        if (isset($hit->_source->startDate)) $display["startDate"] = self::process($hit->_source->startDate);
        if (isset($hit->_source->endDate)) $display["endDate"] = self::process($hit->_source->endDate);
        if (isset($hit->_source->additionalType)) $display["additionalType"] = self::process($hit->_source->additionalType);

        if (isset($hit->_source->about)) $display["about"] = self::process($hit->_source->about);
        if (isset($hit->_source->subjectOf)) $display["subjectOf"] = self::process($hit->_source->subjectOf);
        if (isset($hit->_source->contentLocation)) $display["contentLocation"] = self::process($hit->_source->contentLocation);
        if (isset($hit->_source->spatialCoverage)) $display["spatialCoverage"] = self::process($hit->_source->spatialCoverage);
        if (isset($hit->_source->temporalCoverage)) $display["temporalCoverage"] = self::process($hit->_source->temporalCoverage);

        if (isset($hit->_source->mentions)) $display["mentions"] = self::process($hit->_source->mentions);
        if (isset($hit->_source->associatedMedia)) $display["associatedMedia"] = self::process($hit->_source->associatedMedia);
        if (isset($hit->_source->distribution)) $display["distribution"] = self::process($hit->_source->distribution);
        if (isset($hit->_source->copyrightHolder)) $copyrightHolder = self::process($hit->_source->copyrightHolder);
        if (isset($hit->_source->copyrightYear)) $copyrightYear = self::process($hit->_source->copyrightYear);
        if (isset($copyrightHolder)) {
            $display["copyright"] = Array();
            foreach($copyrightHolder as $k => $v) {
                $display["copyright"] = (isset($copyrightYear[$k])?$copyrightYear[$k]. " ":"") . $v;
            }
        }

        if (isset($hit->_source->duration)) $display["duration"] = self::process($hit->_source->duration);
        if (isset($hit->_source->mentions)) $display["mentions"] = self::process($hit->_source->mentions);
        if (isset($hit->_source->dateline)) $display["dateline"] = self::process($hit->_source->dateline);


        if (isset($hit->_source->bookEdition)) $display["bookEdition"] = self::process($hit->_source->bookEdition);
        if (isset($hit->_source->material)) $display["material"] = self::process($hit->_source->material);
        if (isset($hit->_source->review)) $display["review"] = self::process($hit->_source->review);
        if (isset($hit->_source->itemReviewed)) $display["itemReviewed"] = self::process($hit->_source->itemReviewed);

        if (isset($hit->_source->translationOfWork)) $display["translationOfWork"] = self::process($hit->_source->translationOfWork);
        if (isset($hit->_source->workTranslation)) $display["workTranslation"] = self::process($hit->_source->workTranslation);
        if (isset($hit->_source->version)) $display["version"] = self::process($hit->_source->version);                    

        if (isset($hit->_source->thumbnailUrl)) {
            $display["thumbnailUrl"] = self::process($hit->_source->thumbnailUrl);
        } else {
            if (isset($hit->_source->associatedMedia->thumbnailUrl)) {
                $display["thumbnailUrl"] = self::process($hit->_source->associatedMedia->thumbnailUrl);
            }
        }

        $thumbnails = Array();
        if (isset($hit->_source->thumbnailUrl)) {
            $tmp = self::process($hit->_source->thumbnailUrl);
            $same = self::process($hit->_source->sameAs);
            foreach ($tmp as $k => $v) {
                if ($v!="") {
                    $thumb = Array("thumbnailUrl"=>$v, "url"=>$same[0]);
                    $thumbnails[] = $thumb;
                }
            }
        }
        if (isset($hit->_source->associatedMedia)) {
            $am = self::process($hit->_source->associatedMedia);

            foreach ($am as $k => $v) {
                if (isset($v["thumbnailUrl"])) {
                    if ($v["thumbnailUrl"] != "") {
                        $thumb = Array("thumbnailUrl"=>$v["thumbnailUrl"]);
                        if(isset($v["url"])) { $thumb["url"] = $v["url"]; }
                        $thumbnails[] = $thumb;
                    }
                }
            }
        }


        if (count($thumbnails) > 0) $display["thumbnails"] = $thumbnails;

        if (isset($hit->_source->sameAs)) {
            $display["source"] = self::process($hit->_source->sameAs);
        }

        if (isset($display["source"])) {
            for($i=count($display["source"])-1;$i>=0;$i--) {
                if(!filter_var($display["source"][$i], FILTER_VALIDATE_URL)) {
                    array_splice($display["source"],$i,1);
                }
            }
        }

        $display["retweet"] = 0;
        if (isset($hit->_source->identifier)) {
            if (is_array($hit->_source->identifier)) {
                foreach ($hit->_source->identifier as $ident) {
                    if ($ident->name ==  "retweeted_tweet_id") {
                        $display["retweet"] = 1;    
                    }
                }

            } else {
                if ($hit->_source->identifier->name == "retweeted_tweet_id") {
                    $display["retweet"] = 1;
                }
            }
        }


        $display["citations"] = Array();

        return $display;
    }

    public static function process($data) {
        switch(gettype($data)) {
            case "string":
            case "integer":                
                return array($data);
                break;
            case "array":
                $ret = Array();
                foreach($data as $k => $v) {
                    $add = self::process($v);
                    if ($add != Null) $ret = array_merge($ret, $add);
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
                                if (isset($data["name"])) {
                                    $str = $data["name"];
                                }
                            }
                            return array($str);
                            break;
                        case "PerformanceRole":
                            if (isset($data["characterName"])) {
                                $str = "(" . $data["characterName"] . ")";
                            }
                            if (isset($data["actor"]) && isset(((array)$data["actor"])["name"])){
                                $str = ((array)$data["actor"])["name"] . " " . $str;
                            }
                            return array($str);
                            break;
                        case "Review":
                            $name = $author = $date = $body = "";
                            if (isset($data["dateCreated"])) $date = $data["dateCreated"];
                            if (isset($data["name"])) $name = $data["name"];
                            if (isset($data["author"]) && isset(((array)$data["author"])["name"])) $author = ((array)$data["author"])["name"];
                            if (isset($data["reviewBody"]) && isset(((array)$data["reviewBody"])["@value"])) $body = ((array)$data["reviewBody"])["@value"];
                            return array($name . " - " . $author . " - " . $date . " - " . $body);
                            break;
                        default:
                            $name = $url = $thumbnailurl = "";
                            if (isset($data["name"]) && isset(((array)$data["name"])["@value"])) {
                                $name = ((array)$data["name"])["@value"];
                            } else {
                                if(isset($data["name"])) {
                                    $name = $data["name"];
                                }
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
    }

}