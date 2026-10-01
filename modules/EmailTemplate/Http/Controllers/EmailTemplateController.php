<?php

namespace Modules\EmailTemplate\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\EmailTemplate\Entities\EmailTemplate;
use Modules\User\Entities\User;
use Modules\Media\Entities\File;
use Mail;

class EmailTemplateController
{

    public function index()
    {
        return redirect()->route("admin.dashboard.index");
    }
    
    public function addressEmailSendCron()
    {
        if (setting('storefront_send_email_enabled') == 1) {
            $templateId = setting('storefront_template_id');
            $emailTemplate = EmailTemplate::find($templateId);

            if (!$emailTemplate) {
                return response()->json(['message' => 'Email template not found.'], 404);
            }

            $templatePath = public_path('storage/email_templates/' . $emailTemplate->file_url);
            if (!file_exists($templatePath)) {
                return response()->json(['message' => 'Email template file not found.'], 404);
            }

            $emailTemplateBody = file_get_contents($templatePath);
            $usersWithoutDefaultAddress = User::whereDoesntHave('defaultAddress')->pluck('id');

            foreach ($usersWithoutDefaultAddress as $userId) {
                $user = User::find($userId);
                if (!$user || empty($user->email)) {
                    continue;
                }
                
                Mail::html($emailTemplateBody, function ($message) use ($user, $emailTemplate) {
                    $message->to($user->email)
                            ->subject($emailTemplate->subject);
                });
            }        
            return response()->json(['message' => 'Email sent successfully.']);
        }
        return response()->json(['message' => 'Email sending is disabled.'], 403);
    }
    
    public function addressNotificationSendCron(Request $request)
    {
        if (setting('send_notification_for_address') == 1) {
            
            $usersWithoutDefaultAddress = User::whereDoesntHave('defaultAddress')->pluck('id');

            foreach ($usersWithoutDefaultAddress as $userId) {
                $user = User::find($userId);

                if (!$user->expo_notification_token) {
                    continue;
                }

                $message = [
                    "to" => "ExponentPushToken[".$user->expo_notification_token."]",
                    "sound" => "default",
                    "title" => "Complete Your Profile",
                    "body" => "Your address is missing. Tap here to update it now.",                
                    "data" => ["url" => ""],
                ]; 
                
                $ch = curl_init('https://exp.host/--/api/v2/push/send');
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    'Content-Type: application/json',
                ]);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($message));
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_TIMEOUT, 300);

                $response = curl_exec($ch); 
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);
            }
            return response()->json(['message' => 'Notification sent successfully.']);
        }
        return response()->json(['message' => 'Notification sending is disabled.'], 403);
    }
}
