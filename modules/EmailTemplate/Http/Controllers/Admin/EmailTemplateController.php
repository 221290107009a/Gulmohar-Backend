<?php

namespace Modules\EmailTemplate\Http\Controllers\Admin;

use Modules\EmailTemplate\Entities\EmailTemplate;
use Modules\EmailTemplate\Entities\Newsletter;
use Modules\EmailTemplate\Entities\Schedule;
use Modules\EmailTemplate\Entities\EmailTemplatePreview;
use Modules\Admin\Traits\HasCrudActions;
use Modules\EmailTemplate\Http\Requests\SaveEmailTemplateRequest;
use Modules\EmailTemplate\Jobs\SendExpoNotification;
use Modules\User\Entities\User;
use Illuminate\Http\Request;
use Illuminate\Html\HtmlFacade;
use Carbon\Carbon;
use Mail;
use DataTables;
use SendGrid;

class EmailTemplateController
{
    use HasCrudActions;

    protected $model = EmailTemplate::class;

    protected $label = 'emailtemplate::emailtemplates.emailtemplate';

    protected $viewPath = 'emailtemplate::admin.emailtemplates';

    protected $validation = SaveEmailTemplateRequest::class;

    public function table(Request $request)
    {
        if ($request->ajax()) 
        {
            $emailTemplate = EmailTemplate::all();
            
            return Datatables::of($emailTemplate)->addIndexColumn()
            ->addColumn('checkbox', function ($entity) {
                return view('admin::partials.table.checkbox', compact('entity'));
            }) 
            ->editColumn('name', function ($entity) {
                return $entity->name;
            })
            ->editColumn('status', function ($entity) {
                if ($entity->status == '1') {
                    return '<span class="badge badge-success">Active</span>';
                } else{
                    return '<span class="badge badge-warning">In-Active</span>';
                }
            })
            ->editColumn('created', function ($entity) {
                return view('admin::partials.table.date')->with('date', $entity->created_at);
            })
             ->addColumn('action', function($entity){                
                return '<button class="btn btn-primary btn-clone" data-id="' . $entity->id . '">Clone</button>';
            })
            ->rawColumns(['created','status','action'])
            ->make(true);
        }
        return view("emailtemplate::admin.emailtemplates.index");
    }

    public function edit($id)
    {
        $emailTemplate =  EmailTemplate::where('id', $id)->first();
        return view("emailtemplate::admin.emailtemplates.create", compact('emailTemplate'));
    }

    

    public function saveSourceData(Request $request)
    {        
        $tempfilename = !empty($request['fileUrl']) ? $request['fileUrl'] : 'template-' . time() . '.html';
        $tempviewPath = public_path('storage/email_templates');

        $cssContent = file_get_contents(public_path('storage/css/responsive-table.css'));        
        $htmlhead = "<html xmlns='http://www.w3.org/1999/xhtml' xmlns:v='urn:schemas-microsoft-com:vml' xmlns:o='urn:schemas-microsoft-com:office:office'>
            <head>
                <meta charset='UTF-8'>
                <meta http-equiv='X-UA-Compatible' content='IE=edge'>
                <meta name='viewport' content='width=device-width, initial-scale=1'>
                <link href='https://cdn.jsdelivr.net/npm/remixicon@2.5.0/fonts/remixicon.css' rel='stylesheet'>
                <link href='https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap' rel='stylesheet'>
                <style>" . $cssContent . "</style>
            </head>
            <body>";
        $htmlfooter = "</body></html>";
        
        $myFile = $tempviewPath . '/' . $tempfilename;
        $stringData = $htmlhead . $request['html'] . $htmlfooter;
        file_put_contents($myFile, $stringData);

        $tempviewData = [
            'name' => $request['Ename'] ?? "Test",
            'content' => $request['html'],
            'subject' => $request['subject'] ?? "Test",
            'file_url' => $tempfilename,
            'status' => "1"
        ];

        $existingTemplate = EmailTemplate::where('file_url', $request['fileUrl'])->first();
        
        if (!empty($existingTemplate)) {
            if ($existingTemplate) {
                $tempData = $existingTemplate->update($tempviewData);
                return response()->json([
                    'saveData' => $existingTemplate
                    ]
                );
            }
        }
        $tempData = EmailTemplate::create($tempviewData);

        return response()->json([
            'saveData' => $tempData
            ]
        );      
    }

    public function PreviewData(Request $request)
    {
        $tempfilename = !empty($request['previewUrl']) ? $request['previewUrl'] : 'template-' . time() . '.html';
        $previewPath = public_path('storage/email_templates');

        $cssContent = file_get_contents(public_path('storage/css/responsive-table.css'));        
        $htmlhead = "<html xmlns='http://www.w3.org/1999/xhtml' xmlns:v='urn:schemas-microsoft-com:vml' xmlns:o='urn:schemas-microsoft-com:office:office'>
            <head>
                <meta charset='UTF-8'>
                <meta http-equiv='X-UA-Compatible' content='IE=edge'>
                <meta name='viewport' content='width=device-width, initial-scale=1'>
                <link href='https://cdn.jsdelivr.net/npm/remixicon@2.5.0/fonts/remixicon.css' rel='stylesheet'>
                <link href='https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap' rel='stylesheet'>
                <style>" . $cssContent . "</style>
            </head>
            <body>";
        $htmlfooter = "</body></html>";
        
        $myFile = $previewPath . '/' . $tempfilename;
        $stringData = $htmlhead . $request['html'] . $htmlfooter;
        file_put_contents($myFile, $stringData);

        $previewData = [
            'name' => $request['name'] ?? "Test",            
            'content' => $request['html'],
            'preview_url' => $tempfilename
        ];

        if (!empty($request['previewUrl'])) {
            $existingPreview = EmailTemplatePreview::where('preview_url', $request['previewUrl'])->first();
            if ($existingPreview) {
                $preData = $existingPreview->update($previewData);                
                return $existingPreview;
            }
        }
        $preData = EmailTemplatePreview::create($previewData);
        
        return $preData;
    }

    public function duplicate($id) 
    {

        $emailTemplate = EmailTemplate::findorFail($id);

        $tempfilename = 'template-'.time().'.html';
        $tempviewPath = public_path('storage/email_templates');

        $myFile = $tempviewPath . '/' . $tempfilename;        
        file_put_contents($myFile, $emailTemplate->content);

        $newEmailTemplate = [];
        $newEmailTemplate['name'] = $emailTemplate->name;
        $newEmailTemplate['subject'] = $emailTemplate->subject;
        $newEmailTemplate['content'] = $emailTemplate->content;
        $newEmailTemplate['file_url'] = $tempfilename;
        $newEmailTemplate['status'] = "1";

        $duplicateRecord = EmailTemplate::create($newEmailTemplate);

        return redirect()->route('admin.emailtemplates.edit',$duplicateRecord->id)->withSuccess('EmailTemplate Copied successfully');
    }

    public function destroy(Request $request)
    {
        $data = $request->input('id');
        
        if ($data !== null) {
            foreach ($data as $id) {
                $template = EmailTemplate::findOrFail($id);
                $fileUrl = $template->file_url;
                $filePath = public_path('storage/email_templates/'.$fileUrl);
                
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
                $template->delete();
                $value = '0';
            }
            return response()->json($value);
        }
        
        return redirect()->route('admin.emailtemplates.index');
    }

    public function sendEmailManually(Request $request)
    {
        if ($request->template_id) {
            $emailTemplate = EmailTemplate::find($request->template_id);
            
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
                if (!$user) {
                    continue;
                }
                /* Mail::html($emailTemplateBody, function ($message) use ($user, $emailTemplate) {
                    $message->to($user->email, $user->fullname)
                            ->subject($emailTemplate->subject);
                }); */
            }
            return response()->json(['message' => 'Email sent successfully.']);            
        }
        return response()->json(['message' => 'Email sending is disabled.'], 403);
    }

    public function sendNotificationManually(Request $request)
    {
        $usersWithoutDefaultAddress = User::whereDoesntHave('defaultAddress')->pluck('id');

        foreach ($usersWithoutDefaultAddress as $userId) {
            SendExpoNotification::dispatch($userId);
        }
        /* foreach ($usersWithoutDefaultAddress as $userId) {
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
        } */
        return response()->json(['message' => 'Notification sent successfully.']);
    }
}
