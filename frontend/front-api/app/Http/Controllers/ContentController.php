<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;


class ContentController extends Controller
{

    public function __construct() {
        parent::__construct();
    }

    public function get($contentcode) {
        $content = DB::table('content')->where('code',$contentcode)->get()->first();
        return $this->removeCarriageReturn($content);
    }


    public function content(Request $request) {
        $this->authorize('content');
        $content = DB::table('content')->orderBy('code')->get()->all();
        foreach($content as $k=>$v) {
            $content[$k] = $this->removeCarriageReturn($v);
        }
        return $content;
    }

    public function contentsave(Request $request) {
        $this->authorize('content');

        $rc = json_decode($request->getContent());
        DB::table('content')->where('id',$rc->id)->update((array)$rc);
        return $this->content($request);

    }

    public function statussave(Request $request) {
        $this->authorize('content');

        $rc = json_decode($request->getContent());

        DB::table('status')->delete();
        if (trim($rc->title) == "" && trim($rc->msg) == "") {
        } else {
            DB::table('status')->insert([
                'title' => $rc->title,
                'msg' => $rc->msg
            ]);
            
        }
        $status = DB::table('status')->distinct()->get();
        return $status;
    }


    public function statusdelete(Request $request) {
        $this->authorize('content');

        DB::table('status')->delete();

        $status = DB::table('status')->distinct()->get();

        return $status;
    }

    private function removeCarriageReturn($c) {
        $c->content_nl = str_replace("\r","",$c->content_nl);
        $c->content_en = str_replace("\r","",$c->content_en);
        return $c;

    }

}
