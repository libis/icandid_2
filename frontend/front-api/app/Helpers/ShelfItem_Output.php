<?
namespace App\Helpers;
use App\Item;
use App\Eshelf;

use Illuminate\Support\Facades\Log;

class ShelfItem_Output {

    public function __construct($shelfname,$user_id) {
        $this->user_id = $user_id;
        $this->itemids = [];

        $shelf = Eshelf::where("name", $shelfname)->where("user_id", $user_id)->get();
        if ($shelf->isEmpty()) {
            $shelf = new Eshelf;
            $shelf->name = $shelfname;
            $shelf->user_id = $user_id;
            $shelf->save();
        }
        $this->shelf = Eshelf::where("name", $shelfname)->where("user_id", $user_id)->get()->first();
        $this->itemids = array_map(function($a) {return (isset($a->value->_id)?$a->value->_id:$a->value);},Item::where("eshelf_id",$this->shelf->id)->get()->all());

        $this->first = True;
    }
    
    public function add($data) {
        foreach($data as $d) {
            if (!in_array($d->_id, $this->itemids)) {
                unset($d->highlight);
                $item = new Item;
                $item->eshelf_id = $this->shelf->id;
                $item->value = $d->_id;
                $item->save();
            }    
        }
    }
    
    public function save($job) {
        return Null;
    }
}
    