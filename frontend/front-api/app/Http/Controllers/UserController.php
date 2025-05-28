<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\User;
use App\Query;
use App\Item;
use App\Eshelf;
use App\Queue;
use App\Helpers\ESQuery;
use App\Organisation;

class UserController extends Controller
{
    //


    public function profile(Request $request) {
        $this->authorize('search');
        $user = User::with('queries')->where('id',$this->user_id)->first();
        if (!$user) {
            $user = new User;
        }         

        $user->eppn = $this->user_eppn;

        $user->history = session('history',[]);
        $user->authenticated = $this->user_authenticated;

        $user->permissions = $user->getPermissions();


        // if user has not api permission then the apikey should not be communicated to the frontend
        if (in_array("api", array_map(function($value) { return $value->reference; }, $user->permissions["resources"]))) {

        } else {
            unset($user->apikey);
        }

        // if user can do searches lets determine which interface (media/collections) he has access to
        if (in_array("search", array_map(function($value) { return $value->reference; }, $user->permissions["resources"]))) {
            $permissions = $user->permissions;

            // if user has access to media
            if (count(array_filter($user->permissions["datasets"], function($value) { return ($value->ismedia == 1); } )) > 0) {
                $permissions["resources"][] = array("name"=>"Search Media", "reference" => "search_media" );
            }
            if (count(array_filter($user->permissions["datasets"], function($value) { return ($value->ismedia == 0); } )) > 0) {
                $permissions["resources"][] = array("name"=>"Search Collections", "reference" => "search_collections");
            }
            $user->permissions = $permissions;
        }


        $permissions = $user->permissions;
        if ($user->authenticated) {
            if ($user->eppn == "publicuser") {
                $permissions["resources"][] = array("name"=>"Login","reference"=>"login");
            } else {
                $permissions["resources"][] = array("name"=>"Logout","reference"=>"logout");
            }
        } else {
            $permissions["resources"][] = array("name"=>"Login","reference"=>"login");
        }
        $user->permissions = $permissions;

        return $user;
    }


    public function getshelves(Request $request) {
        $this->authorize('save');
        $eshelves = Eshelf::where("user_id","=",$this->user_id)->withCount('items')->get()->all();
        return $eshelves;
    }
    public function getshelf(Request $request) {
        $this->authorize('save');

        $shelf = Eshelf::where("user_id","=",$this->user_id)->where("id","=",$request->id)->get()->all();
        if (count($shelf) > 0 ) {
            $lookup = [];
            $items = Item::where("eshelf_id","=",$request->id)->skip($request->start)->take(20)->get()->all();

            foreach ($items as $k=>$item) {
                if (is_string($item->value)) {
                    $lookup[] = $item->value;
                }
            }

            if (count($lookup) > 0) {
                $q = json_decode('{"query": {"terms": { "_id": [] } },"size": 200}');
                $q->query->terms->_id = $lookup;
                $query = new ESQuery($q, $this->user->apikey);
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



            return $items;
        } else {
            abort(400);
        }
    }



    public function storeItem(Request $request) {
        $this->authorize('save');
        $data = json_decode($request->getContent(), true);

        

        $shelf = Eshelf::where("name", $data["shelf"])->where("user_id", $this->user_id)->get();

        if ($shelf->isEmpty()) {
            $shelf = new Eshelf;
            $shelf->name = $data["shelf"];
            $shelf->user_id = $this->user_id;
            $shelf->save();
        }
        $shelf = Eshelf::where("name", $data["shelf"])->where("user_id", $this->user_id)->first();

        $itemids = array_map(function($a) {return (isset($a->value->_id)?$a->value->_id:$a->value);},Item::where("eshelf_id",$shelf->id)->get()->all());

        foreach ($data["values"] as $value) {
            if (!in_array($value, $itemids)) {
                $item = new Item;
                $item->eshelf_id = $shelf->id;
                $item->value = $value;
                $item->save();
            }
        }
        return $this->profile($request);
    }

    public function storeQuery(Request $request) {
        $this->authorize('save');
        $queryobj = $request->getContent();
        $query = new Query;
        $query->user_id = $this->user_id;
        $query->query = $queryobj;
        $query->save();
        return $this->profile($request);
    }

    public function deleteItem(Request $request) {
        $this->authorize('save');
        $item = Item::where('id',$request->id)->first();
        $shelf = Eshelf::where('id',$item->eshelf_id)->where('user_id',$this->user_id)->first();
        if ($shelf) {
            $item->delete();
        }
        return $this->profile($request);
    }

    public function deleteShelf(Request $request) {
        $this->authorize('save');
        $shelf = Eshelf::where('id',$request->id)->where('user_id',$this->user_id)->first();
        if ($shelf) {
            $deletedItems = Item::where('eshelf_id', $request->id)->delete();
            $shelf->delete();
        }
        return $this->profile($request);
    }

    public function deleteQuery(Request $request) {
        $this->authorize('save');
        Query::where('user_id',$this->user_id)->where('id',$request->id)->delete();
        return $this->profile($request);
    }


    public function info(Request $request) {
        $out = [];
        $out["COOKIE"] = $_COOKIE;
        $out["SERVER"] = $_SERVER;
        $out["GET"] = $_GET;
        $out["POST"] = $_POST;
        if (isset($_SESSION)) {
            $out["SESSION"] = $_SESSION;
        }
        $out["REQUEST"] = $_REQUEST;
        $out["ENV"] = $_ENV;

        return $out;
    }


    public function queue(Request $request) {
        $this->authorize('export');
        $obj = $request->getContent();

        if (isset($this->user["email"]) && $this->user["email"] != "") {
            $queue = new Queue;
            $queue->user_id = $this->user_id;
            $queue->job = $obj;
            $queue->email = $this->user["email"];
            $queue->save();
            return $queue;
        }
        abort(500);
    }
    
    public function access($apikey) {
        if ($apikey == "ei3a333ca0399a22b50nfcd31de9938i11e4ddf" ) {
            // special user access to all available datasets to be used to collect statistics
            return array("datasets" => array_map(function($d) { return $d->internalident; },\App\Dataset::where('available',1)->get()->all()), "api"=>true);
        } else {
            $data = User::where('apikey',$apikey)->get()->first();
            if ($data == Null) abort(404);
            return array("datasets" => $data->access(), "api"=> (count(array_filter($data->getPermissions()["resources"], function($v, $k) { return $v->reference == "api"; },ARRAY_FILTER_USE_BOTH )) > 0));
        }

    }


    public function orgs() {
        return Organisation::with('groups')->with('groups.faculty')->orderBy('name', 'asc')->get();
    }
}
