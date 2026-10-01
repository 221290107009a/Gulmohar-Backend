<?php

namespace Modules\User\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\User\Entities\Feedback;
use Illuminate\Support\Facades\Validator;

class FeedbackController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $imagePath = null;
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads/feedback'), $filename);
            $imagePath = 'uploads/feedback/' . $filename;
        }

        $feedback = Feedback::create([
            'user_id' => $request->user_id,
            'subject' => $request->subject,
            'message' => $request->message,
            'image' => $imagePath,
            'status' => 'pending',
            'app_version' => $request->app_version,
            'os_version' => $request->os_version,
            'device_model' => $request->device_model,
            'network_type' => $request->network_type,
            'current_route' => $request->current_route,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Feedback submitted successfully.',
            'data' => $feedback,
        ]);
    }
}
