<?php
namespace App\Helpers;
use GuzzleHttp\Client as Client;
use App\Mail\Mail as Email;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Queue;
use App\User;

class Export {
    private $queue;

    private $mailbody = Array("nl"=>"Uw export van {app_name} is klaar. U kan deze nu downloaden.<br><br>1) Log aan op {app_url} (als u niet reeds ingelogd bent)<br><br>2) Klik nadien op volgende link : {url} <br><br><br>Deze link blijft 10 dagen geldig."
                            ,"en"=>"Your export from {app_name} is ready. You can now download it.<br><br>1) Log on to {app_url} (if you are not logged in yet)<br><br>2) Next click the following hyperlink : {url}<br><br><br>This hyperlink will remain valid for 10 days.");

    public function __construct() {
        $this->queue = Queue::where("status",0)->orderBy('created_at','ASC')->first();
        if ($this->queue) {
            $this->user = User::where("id", $this->queue->user_id)->first();
        }
    }    

    public function exec() {
        if ($this->queue) {
            // set job state : processing
            $this->queue->status = 1;
            $this->queue->save();
            $reccount = 0;

            // get job
            $job = json_decode($this->queue->job);

            // setup interface with searchengine

            if (isset($job->shelfid)) {
                $searcher = new Grabber($job->shelfid,$this->user->id);
            } else {
                $searcher = new Searcher($job->query);
            }



            
            // select output channel based on format
            Log::info($job->format);
            switch($job->format) {
                case 'json':
                case 'jsonld':
                    $output = new JSON_Output();
                break;
                case 'csv':
                    $output = new CSV_Output();
                break;
                case 'txt':
                    $output = new TXT_Output();
                break;
                case 'xlsx':
                    $output = new XLSX_Output();
                break;
                case 'shelfitem':
                    $output = new ShelfItem_Output($job->shelf,$this->user->id);
                break;
            }
            
            // get first set ofo data
            $data = $searcher->first($this->user->apikey);


            // loop while there is more data
            while ($data) {
                $output->add($data);
                $reccount += count($data);
                Log::info("Records in export : " . $reccount);

                //sleep(1);
                $data = $searcher->next($this->user->apikey);
            }

            // save output
            $job->hits = $searcher->hits();
            $job->datetimestamp = $this->queue->created_at;

            $id = $output->save($job);

            // mail with link to id

            if ($id != Null) {
                $url = config('app.url').'/export/' . $id;
                $msg = $this->mailbody[$job->language];

                $search = ["{url}","{app_name}","{app_url}"];
                $replace = [$url,config('app.name'),config('app.url')];

                $body = str_replace($search,$replace,$msg);
                $subject = config('app.name') . " export " . $this->queue->created_at . " UTC";

                Mail::to($this->queue->email)->send(new Email($body,$subject));
            }
            // set job state : complete
            $this->queue->status = 2;
            $this->queue->save();


        }
    }
}




?>