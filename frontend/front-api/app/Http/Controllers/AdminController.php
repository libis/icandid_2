<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use DateTime;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;


class AdminController extends Controller
{

    public function __construct() {
        parent::__construct();
        $this->authorize('admin');
    }

    public function usersearch(Request $request, $type) {
        
        $t = strtoupper($request->getContent());

        $types = [0,1];
        if ($type == "active") $types = [1];
        if ($type == "inactive") $types = [0];

        $users = \App\User::whereIn('active',$types)
            ->where(function($query) use ($t)  {
/*                $query->where('firstname','like','%'.$t.'%')
                    ->orWhere('lastname','like','%'.$t.'%')
                    ->orWhere('email','like','%'.$t.'%')
                    ->orWhere('institution','like','%'.$t.'%')
                    ->orWhere('researchgroup','like','%'.$t.'%'); */
                    $query->whereRaw("upper(firstname) like '%".$t."%'")
                    ->orWhereRaw("upper(lastname) like '%".$t."%'")
                    ->orWhereRaw("upper(email) like '%".$t."%'")
                    ->orWhereRaw("upper(institution) like '%".$t."%'")
                    ->orWhereRaw("upper(researchgroup) like '%".$t."%'")
                    ->orWhereRaw("upper(eppn) = '".$t."'");
                    
            })
            ->with('roles')->with('resources')->with('datasets')->with('roles.resources')->with('roles.datasets')
            ->get()->all();

        foreach($users as $k => $user) {
            $users[$k]->numQueries = $user->queries()->count();
            $users[$k]->numShelves = $user->eshelves()->count();
            $users[$k]->numItems = 0;
            foreach ($user->eshelves()->get()->all() as $eshelf) {
                $users[$k]->numItems += $eshelf->items()->count();
            }
        }

        return $users;
    }

    public function usersave(Request $request) {
        $rc = json_decode($request->getContent());
        if ($rc->id == 0) {
            $user = new \App\User;
        } else {
            $user = \App\User::find($rc->id);
        }
        $user->firstname = $rc->firstname;
        $user->lastname = $rc->lastname;
        $user->email = $rc->email;
        $user->institution = $rc->institution;
        $user->researchgroup = $rc->researchgroup;
        if (isset($rc->description)) { $user->description = substr($rc->description,0,255); } 

        $d = new DateTime($rc->startdate);
        $user->startdate = $d->format('Y-m-d');

        $d = new DateTime($rc->enddate);
        $user->enddate = $d->format('Y-m-d');

        $user->eppn = $rc->eppn;
        $user->active = $rc->active;
        if (isset($rc->newsletter)) $user->newsletter = $rc->newsletter;
        if (isset($rc->apikey)) $user->apikey = $rc->apikey;

        if (isset($rc->twitter_api_key)) $user->twitter_api_key = $rc->twitter_api_key;
        if (isset($rc->twitter_api_key_secret)) $user->twitter_api_key_secret = $rc->twitter_api_key_secret;
        if (isset($rc->twitter_bearer_token)) $user->twitter_bearer_token = $rc->twitter_bearer_token;

        $user->save();

        $user->roles()->detach();
        foreach($rc->roles as $l) {
            $user->roles()->attach($l->id);
        }

        $user->resources()->detach();
        foreach($rc->resources as $l) {
            $user->resources()->attach($l->id);
        }

        $user->datasets()->detach();
        foreach($rc->datasets as $l) {
            $user->datasets()->attach($l->id);
        }

        $user->save();
        return $user;
    }

    public function userdelete($id) {

        $user = \App\User::find($id);

        $user->delete();

        return Null;
    }

    public function usersexport() {
        $users = \App\User::get()->all();
        $header = array_keys($users[0]->attributesToArray());

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $col = 0;

        foreach($header as $k=>$val) {
            $cell = chr(65+$col) . "1";
            $sheet->setCellValue($cell,$val);
            $col++;
        }

        foreach($users as $k=>$user){
            $col = 0;
            foreach($header as $idx) {
                $val = $user[$idx];
                $cell = chr(65+$col) . ($k+2);
                $sheet->setCellValue($cell,$val);
                $col++;
            }
        }

        $writer = new Xlsx($spreadsheet);
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="'. urlencode("users.xlsx").'"');
        $writer->save('php://output');
    }



    public function datasetsearch(Request $request) {
        $t = strtoupper($request->getContent());

/*        $datasets = \App\Dataset::where('name','like','%'.$t.'%')
            ->orWhere('internalident','like','%'.$t.'%')
            ->orWhere('description','like','%'.$t.'%')
            ->orWhere('requestor','like','%'.$t.'%')
            ->orWhere('requestoremail','like','%'.$t.'%')
            ->orWhere('query','like','%'.$t.'%')
            ->orWhere('provider','like','%'.$t.'%') */
         $datasets = \App\Dataset::whereRaw("upper(name) like '%" .$t. "%'")
            ->orwhereRaw("upper(internalident) like '%" .$t. "%'")
            ->orwhereRaw("upper(description) like '%" .$t. "%'")
            ->orwhereRaw("upper(requestor) like '%" .$t. "%'")
            ->orwhereRaw("upper(requestoremail) like '%" .$t. "%'")
            ->orwhereRaw("upper(query) like '%" .$t. "%'")
            ->orwhereRaw("upper(provider) like '%" .$t. "%'")
            ->with('languages')
            ->with('labels')
            ->get()->all();

        foreach($datasets as $k => $d) {
            $labels_nl = $labels_en = [];    
            foreach ($d->labels as $label) {
                $labels_nl[] = $label->name_nl;
                $labels_en[] = $label->name_en;
            }

            $datasets[$k]->labels_nl = implode(", ", $labels_nl);
            $datasets[$k]->labels_en = implode(", ", $labels_en);
        }
        return $datasets;
    }

    public function datasetsave(Request $request) {
        $rc = json_decode($request->getContent());
        if ($rc->id == 0) {
            $dataset = new \App\Dataset;
        } else {
            $dataset = \App\Dataset::find($rc->id);
        }
        $dataset->name = $rc->name;
        $dataset->internalident = $rc->internalident;
        if (isset($rc->description)) $dataset->description = substr($rc->description,0,255);
        if (isset($rc->license)) $dataset->license = $rc->license;
        if (isset($rc->requestor)) $dataset->requestor = $rc->requestor;
        if (isset($rc->requestoremail)) $dataset->requestoremail = $rc->requestoremail;
        if (isset($rc->from)) {
            $d = new \DateTime($rc->from);
            $dataset->from = $d->format("Y-m-d");
        }

        if (isset($rc->until)) {
            $d = new \DateTime($rc->until);
            $dataset->until = $d->format("Y-m-d");
        }
        if (isset($rc->query)) $dataset->query = $rc->query;
        if (isset($rc->provider)) $dataset->provider = $rc->provider;
        if (isset($rc->available)) $dataset->available = $rc->available;
        if (isset($rc->hidden)) $dataset->hidden = $rc->hidden;
        // if (isset($rc->ismedia))$dataset->ismedia = $rc->ismedia;

        $dataset->save();

        $dataset->languages()->detach();
        if (isset($rc->languages)) {
            foreach($rc->languages as $l) {
                $dataset->languages()->attach($l->id);
            }
        }

        $dataset->labels()->detach();
        if (isset($rc->labels)) {
            foreach($rc->labels as $l) {
                $dataset->labels()->attach($l->id);
            }
        }

        $dataset->save();
        return $dataset;
    }

    public function datasetrefresh() {
        $dc = new \App\Helpers\Doccounter;
        while ($dc->hasJobToDo()) {
            $dc->exec();
            $dc = new \App\Helpers\Doccounter;
        }
    }

    public function options(Request $request) {
        $options = [];

        $options["languages"] = \App\Language::get()->all();
        $options["labels"] = \App\Label::get()->all();
        $options["providers"] = DB::table('datasets')->select('provider')->distinct()->get();
        $options["institutions"] = DB::table('users')->select('institution')->distinct()->get();
        $options["resources"] = \App\Resource::get()->all(); 
        $options["datasets"] = \App\Dataset::where('available','1')->get()->all();
        $options["roles"] = \App\Role::with('datasets')->with('resources')->get()->all();

        return $options;
    }

    public function groups(Request $request) {
        $roles = \App\Role::orderBy('name')->with('resources')->with('datasets')->get()->all();

        return $roles;
    }

    public function groupsave(Request $request) {
        $rc = json_decode($request->getContent());
        if ($rc->id == 0) {
            $group = new \App\Role;
        } else {
            $group = \App\Role::find($rc->id);
        }
        $group->name = $rc->name;
        if (isset($rc->description)) { $group->description = substr($rc->description,0,255); }

        $group->save();

        $group->resources()->detach();
        foreach($rc->resources as $l) {
            $group->resources()->attach($l->id);
        }        
        $group->datasets()->detach();
        foreach($rc->datasets as $l) {
            $group->datasets()->attach($l->id);
        }        


        $group->save();
        return $group;
    }  
    
    
    public function labels(Request $request) {
        $roles = \App\Label::orderBy('name_nl')->get()->all();

        return $roles;
    }


    public function labelsave(Request $request) {
        $rc = json_decode($request->getContent());
        if ($rc->id == 0) {
            $label = new \App\Label;
        } else {
            $label = \App\Label::find($rc->id);
        }
        $label->name_nl = $rc->name_nl;
        $label->name_en = $rc->name_en;
        if (isset($rc->description)) { $label->description = substr($rc->description,0,255); } 

        $label->save();
        return $label;

    }

    

}
