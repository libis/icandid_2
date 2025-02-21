<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CollectionController extends Controller
{
    //

    public function search(Request $request) {
        $this->authorize('search');
        $req = json_decode($request->getContent());


        $query = \App\Dataset::with('languages')->with('labels');


        if ($req->term != "") {
            $query->where(function($q) use($req) {
/*                $q->where("UPPER(name)","like","%" . strtoupper($req->term) . "%")
                    ->orWhere("UPPER(description)","like","%" . strtoupper($req->term) . "%")
                    ->orWhere("UPPER(internalident)","like","%" . strtoupper($req->term) . "%"); */
                    $q->whereRaw("UPPER(name) like '%" . strtoupper($req->term) . "%'")
                    ->orWhereRaw("UPPER(description) like '%" . strtoupper($req->term) . "%'")
                    ->orWhereRaw("UPPER(internalident) like '%" . strtoupper($req->term) . "%'");

            });
        }

        if ($req->available == 1) {
            $query->where('available',1);
        }
        
        if ($req->from != "" || $req->until != "") {
            if ($req->from == "") $req->from = "1976-05-18T00:00:00.000Z";
            if ($req->until == "") $req->until = "2176-05-18T00:00:00.000Z";

            $d = new \DateTime($req->from);
            $f = $d->format("Y-m-d");
            $d = new \DateTime($req->until);
            $u = $d->format("Y-m-d");

            $query->where('from',"<=",$u)->where('until','>=',$f);
        }

        Log::info($query->toSql());

        $result = $query->get()->all();

        if (count($req->lang) > 0) {
            foreach($result as $k =>$v) {
                $ok = false;
                foreach($v->languages as $kk => $lang)  {
                    if (in_array($lang->isocode, $req->lang)) {
                        $ok = true;
                    }
                }
                if (!$ok) unset($result[$k]);
            }    
        }

        if (count($req->label) > 0) {
            foreach($result as $k =>$v) {
                $ok = false;
                foreach($v->labels as $kk => $label)  {
                    if (in_array($label->id, $req->label)) {
                        $ok = true;
                    }
                }
                if (!$ok) unset($result[$k]);
            }    
        }

        if (count($req->provider) > 0) {
            foreach($result as $k =>$v) {
                if (!in_array($v->provider, $req->provider)) unset($result[$k]);
            }    
        }

        // if collection is hidden and user has no access to it then do not show it
        $allowed_datasets = array_map(function($value) {
            return $value->internalident;
        },$this->user->getPermissions()["datasets"]);
        foreach($result as $k =>$v) {
            if ($v->hidden && !in_array($v->internalident, $allowed_datasets)) unset($result[$k]);
        }    

        return array_values($result);

    }


    public function getbyid($id) {
        $query = \App\Dataset::with('languages')->with('labels');
        $query->where('available',1)->where('hidden',0);
        $query->where(function($q) use($id) {$q->where("internalident",$id)->orWhere("uuid",$id); });
        $result = $query->get()->first();
        return $result;
    }

    public function all(Request $request) {
        $this->authorize('search');
        $query = \App\Dataset::with('languages')->with('labels')->where('hidden',0);
        $result = $query->get()->all();
        return $result;
    }

    public function options(Request $request) {
        $this->authorize('search');
        $options = [];

        $options["languages"] = array_filter(\App\Language::with("datasets")->get()->all(), function($d) { return (count($d->datasets->all())>0) && $this->showOption($d->datasets) ; });
        $options["labels"] = array_filter(\App\Label::with("datasets")->get()->all(), function($d) { return (count($d->datasets->all())>0) && $this->showOption($d->datasets); });
       
        $options["providers"] = DB::table('datasets')->select('provider')->where("hidden",0)->distinct()->get();
//        $options["resources"] = \App\Resource::get()->all(); 
//        $options["datasets"] = \App\Dataset::where('available','1')->get()->all();
//        $options["roles"] = \App\Role::with('resources')->get()->all();

        return $options;
    }


    private function showOption($l) {
        foreach($l as $v) {
            if ($v->hidden == 0) {
                return True;
            }
        }
        return False;
    }
}
