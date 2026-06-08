<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use App\Mail\Mail as Email;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Dataset;
use App\User;



class FormController extends Controller
{
    public function form(Request $request) {
        $request_content = json_decode($request->getContent());
        $body = "";
        $subject = $request_content->subject;
        foreach ($request_content->formdata as $k => $v) {
            if (is_array($v)) {
                $body .= $this->label($k) . " :\t\t" . implode(", ", $v) . "\n";
            } else {
                $body .= $this->label($k) . " :\t\t" . $v . "\n";
            }
        }
        
        if ($request->bearerToken()) {
            $tks = \explode('.', $request->bearerToken());
            list($headb64, $bodyb64, $cryptob64) = $tks;
            $header = JWT::jsonDecode(JWT::urlsafeB64Decode($headb64));
            $payload = JWT::jsonDecode(JWT::urlsafeB64Decode($bodyb64));
            $body .= "/n/n/n" . print_r(["header"=>$header, "payload"=>$payload], True);
        }    

        $r = (array)$request_content->formdata;

        if (isset($r["loginname"])) {

            // check to see if user already exists
            // if not, create new user, but leave inactive
            $user = User::where("eppn",$r["loginname"])->first();
            if ($user === Null) {
                $user = new User();
                $user->eppn = $r["loginname"];
                $user->firstname = $r["firstname"];
                $user->lastname = $r["lastname"];
                $user->email = $r["email"];
                $user->institution = $r["institution"];
                $user->active = 0;
                $user->researchgroup =  $r["researchgroup"];
                $user->startdate = date("Y-m-d");
                $user->enddate = "2050-12-31";
                $user->description = $r["reason"];
                $user->newsletter = ($r["newsletter"]?1:0);
                $user->faculty = $r["faculty"];
                $user->function = $r["function"];
                $user->language_id = 0;
                if ($r["language"] == "nl") $user->language_id = 1;
                if ($r["language"] == "en") $user->language_id = 3;
                $user->promotor = $r["promotor"];
                $user->termsofuse = ($r["termsofuse"]?1:0);

                $user->save();
            }
        }

        Mail::to(config('app.contact'))->send(new Email(nl2br($body),$subject));
    }

    public function mail(Request $request) {
        $this->authorize('search');
        $m = json_decode($request->getContent());
        Mail::to($m->to)->send(new Email(nl2br($m->body),$m->subject));
    }


    private function label($k) {
        $l = array(
            "email"=>"Email",
            "institution"=>"Instelling",
            "institutionname"=>"Naam instelling",
            "function"=>"Positie",
            "promotor"=>"Promotor",
            "faculty" =>"Faculteit",
            "researchgroup"=>"Onderzoeksgroep",
            "loginname"=>"Loginnaam",
            "reason"=>"Reden aanvraag",
            "from"=>"Vanaf",
            "until"=>"Tot",
            "functionality"=>"Functionaliteit",
            "termsofuse"=>"Servicevoorwaarden aanvaard",
            "newsletter"=>"Nieuwsbrief",
            "language"=>"Taal",
            "firstname"=>"Voornaam",
            "lastname"=>"Familienaam",
            "name"=>"Naam",
            "datasets"=>"Collecties",
            "provider"=>"Provider",
            "title"=>"Titel",
            "searchquery"=>"Zoektermen",
            "info"=>"Bijkomende informatie"
        );

        if (isset($l[$k])) {
            return $l[$k];
        } else {
            return $k;
        }
    }
}
