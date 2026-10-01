<?php

namespace Modules\EmailTemplate\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\EmailTemplate\Entities\Newsletter;
use Modules\EmailTemplate\Entities\Schedule;
use Carbon\Carbon;
use SendGrid;

class SendgridCronController
{
    public function sendGridCron()
    {
        $startDate = Carbon::now()->format('Y-m-d');
        $startTime = Carbon::now()->format('H:i');
        $endTime = Carbon::now()->addMinute('30')->format('H:i');
        
        $nowSheduleData = Schedule::where(['schedule_type' => 'now', 'is_published' => '1', 'is_sendgrid_send' => '0', 'date' => $startDate])->get();
        $futureSheduleData = Schedule::where(['schedule_type' => 'future', 'is_published' => '1', 'is_sendgrid_send' => '0', 'date' => $startDate])->whereBetween('time', [$startTime, $endTime])->get();

        $allSchedule = $nowSheduleData->merge($futureSheduleData);

        $apiKey = setting('sendgrid_api');
        $sg = new \SendGrid($apiKey);
        if (count($allSchedule) > 0) {            
            foreach($allSchedule as $all)
            {
                $segmentId = '';
                $sendTime = '';
                $subject = $all->getNewsData()->subject;
                if ($all->getNewsData()->category == "guest") {
                    $segmentId = setting('guest_sid');  
                    $sendTime = setting('schedule_time');
                } elseif($all->getNewsData()->category == "host"){
                    $segmentId = setting('host_sid');
                    $sendTime = setting('schedule_time');
                } elseif($all->getNewsData()->category == "guest-prospect"){
                    $segmentId = setting('guest_prospect');
                    $sendTime = setting('schedule_time');
                } elseif($all->getNewsData()->category == "guest-customer"){
                    $segmentId = setting('guest_customer');
                    $sendTime = setting('schedule_time');
                } elseif($all->getNewsData()->category == "guest-all-exc-customer"){
                    $segmentId = setting('guest_all_exc_customer');
                    $sendTime = setting('schedule_time');
                } elseif($all->getNewsData()->category == "guest-cold"){
                    $segmentId = setting('guest_cold');
                    $sendTime = setting('schedule_time');
                } elseif($all->getNewsData()->category == "cw-team"){
                    $segmentId = setting('cw_team');
                    $sendTime = setting('schedule_time_test');
                    $subject .= ' - INTERNAL';
                }
                $reqHtml = file_get_contents(public_path('themes/storefront/public/images/uploads/'.$all->getNewsData()->file_url));
                $newHtml = the_content($reqHtml, $all->getNewsData()->category);

                $prebody = json_encode([
                    "name" =>  $all->getNewsData()->name,
                    "subject" => $subject,
                    "send_to" => [
                        "segment_ids"=> [$segmentId]
                    ],
                    "email_config"=> [
                        "subject"=> $subject,
                        "html_content"=> $newHtml,
                        "plain_content"=> "",
                        "generate_plain_content"=> false,
                        "editor"=> "code",
                        "suppression_group_id"=> intval(setting('suppression_group_id')),
                        "custom_unsubscribe_url"=> null,
                        "sender_id"=> intval(setting('sender_id')),
                        "ip_pool"=> null
                    ]
                ]);
                $request_body = json_decode($prebody);

                $datetime = Carbon::now();
                $sbody = json_encode([
                    "send_at" => $datetime->addMinute($sendTime)
                ]);    
                
                $request_sbody = json_decode($sbody);
                try {
                    $result = $sg->client->marketing()->singlesends()->post($request_body);
                    $data = json_decode($result->body(), true);
                    try {                   
                        $response = $sg->client->marketing()->singlesends()->_($data['id'])->schedule()->put($request_sbody);
                        $resData = json_decode($response->body(), true);

                        if (count($resData) > 0) { 
                            Schedule::where('id', $all->id)->update(['published_at' => $datetime, 'is_sendgrid_send' => '1', 'sendgrid_send_id' => $data['id'], 'sendgrid_send_at' => $datetime]);
                        } 

                    } catch (Exception $e) {
                        echo 'Caught exception: '. $e->getMessage() ."\n";
                    }

                } catch (Exception $e) {
                    echo 'Caught exception: '. $e->getMessage() ."\n";
                }
                
            }
        }
        return;
    }
    
}
