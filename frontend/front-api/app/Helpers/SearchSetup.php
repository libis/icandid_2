<?
namespace App\Helpers;

class SearchSetup {

    private $setup = [];

    public function __construct() {
        if (config('elastic.searchsetupfile')) {
            $fullsearchsetupfile = base_path() . "/" . config('elastic.searchsetupfile');
            if (config('elastic.searchsetupfile') != "" && file_exists($fullsearchsetupfile)){
                $string = file_get_contents($fullsearchsetupfile);
                $this->setup = json_decode($string, true);
                if (isset($setup['csvexport'])) {
                    $this->header = $setup['csvexport'];
                }
            }
        }
    }

    public function has($f) {
        return isset($this->setup[$f]);
    }

    public function get($f){
        if ($this->has($f)) {
            return $this->setup[$f];
        } else {
            return False;
        }
    }

    public function all() {
        return $this->setup;
    }

    public function merge($existing=[],$f) {
        if ($this->has($f)) {
            return array_merge($existing,$this->get($f));
        } else {
            return $existing;
        }
    }

}
?>