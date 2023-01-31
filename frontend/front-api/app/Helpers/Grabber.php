<?php 
namespace App\Helpers;
use GuzzleHttp\Client as Client;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Eshelf;
use App\Item;
use App\Helpers\ESQuery;


class Grabber {

    private $shelfid = 0;
    private $userid = 0;
    private $start = 0;
    private $step = 200;
    
    public function __construct($shelfid, $userid){
        $this->shelfid = $shelfid;
        $this->userid = $userid;
    }

    public function first($userapi) {
        $this->start = 0;
        return $this->next($userapi);
    }

    public function next($userapi) {

        $shelf = Eshelf::where("user_id","=",$this->userid)->where("id","=",$this->shelfid)->get()->all();
        if (count($shelf) > 0 ) {
            $lookup = [];
            $items = Item::where("eshelf_id","=",$this->shelfid)->skip($this->start)->take($this->step)->get()->all();
            if (count($items) == 0) {
                return false;
            }

            foreach ($items as $k=>$item) {
                if (is_string($item->value)) {
                    $lookup[] = $item->value;
                }
            }

            if (count($lookup) > 0) {
                $q = json_decode('{"query": {"terms": { "_id": [] } },"size": 0}');
                $q->query->terms->_id = $lookup;
                $q->size=$this->step;
                $query = new ESQuery($q, $userapi);
                $response = $query->exec();
                $data = json_decode((string)$response->getBody());
                foreach ( $data->hits->hits as $v) {
                    for ($i=0; $i<count($items); $i++) {
                        if ($items[$i]->value == $v->_id) {
                            $items[$i]->value = $v;
                        }
                    }
                }
            }
            $this->start += count($items);
            $content = [];
            foreach($items as $k=>$item) {
                $content[] = $item->value;
            }

            return $content;
        } else {
            return false;
        }
    }

    public function hits() {
        return $this->start;
    }




}

?>