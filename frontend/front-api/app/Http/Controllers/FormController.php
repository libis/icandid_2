<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use App\Mail\Mail as Email;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Dataset;



class FormController extends Controller
{
    public function form(Request $request) {
        $request_content = json_decode($request->getContent());
        $body = "";
        $subject = $request_content->subject;
        foreach ($request_content->formdata as $k => $v) {
            if (is_array($v)) {
                $body .= $k . " :\t\t" . implode(", ", $v) . "\n";
            } else {
                $body .= $k . " :\t\t" . $v . "\n";
            }
        }
        Mail::to(config('app.contact'))->send(new Email(nl2br($body),$subject));
    }

}
