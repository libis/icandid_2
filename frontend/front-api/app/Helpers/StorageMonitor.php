<?php
namespace App\Helpers;
use App\Mail\Mail as Email;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\User;
use Carbon\Carbon;


class StorageMonitor {
    public function __construct() {
        $this->users = User::where('active',1)->whereDate('startdate', '<=', Carbon::now())->whereDate('enddate', '>=', Carbon::now())->get()->all();
    }

    public function exec() {
        $subject="StorageMonitor";
        $body = "";
        foreach($this->users as $user) {
            $size = strlen(json_encode(User::with('queries')->with('eshelves.items')->where('id',$user->id)->first()));
            if ($size > (10*1024*1024)) {
                $body .= $user->eppn. "\t" . $user->firstname . " " . $user->lastname . "\t" . $user->email . "\t"  . floor($size/1024/1024) . "MB\n";
            }
        }
        if ($body !="") {
            Mail::to("peter.o@kuleuven.be")->send(new Email(nl2br($body),$subject));
            print($body);
        }        

    }

}

?>