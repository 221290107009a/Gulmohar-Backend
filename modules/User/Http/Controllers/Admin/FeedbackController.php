<?php

namespace Modules\User\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\User\Entities\Feedback;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;

class FeedbackController extends Controller
{
    public function index()
    {
        return view('user::admin.feedback.index');
    }

    public function table(Request $request)
    {
        if ($request->ajax()) {
            $data = Feedback::with('user')->latest()->get();
            return DataTables::of($data)->addIndexColumn()
                ->editColumn('user_id', function ($entity) {
                    if (!$entity->user) return 'N/A';
                    $memberlistUrl = route('admin.member.edit', $entity->user_id);
                    return "<a href='{$memberlistUrl}' target='_blank'>{$entity->user_id}</a>";
                })
                ->editColumn('status', function ($entity) {
                    if ($entity->status == 'resolved') {
                        return '<span class="badge badge-success">Resolved</span>';
                    } else {
                        return '<span class="badge badge-warning">Pending</span>';
                    }
                })
                ->editColumn('created', function ($entity) {
                    $created_at = $entity->created_at;
                    if (is_string($created_at)) {
                        $created_at = Carbon::parse($created_at);
                    }
                    return view('admin::partials.table.date')->with('date', $created_at);
                })
                ->rawColumns(['user_id', 'status', 'created'])
                ->make(true);
        }
    }

    public function show($id)
    {
        $feedback = Feedback::with('user')->findOrFail($id);
        return view('user::admin.feedback.show', compact('feedback'));
    }

    public function update(Request $request, $id)
    {
        $feedback = Feedback::findOrFail($id);
        $feedback->update(['status' => $request->status]);

        return redirect()->route('admin.feedback.index')->withSuccess('Feedback status updated successfully.');
    }

    public function destroy($id)
    {
        $feedback = Feedback::findOrFail($id);
        $feedback->delete();

        return redirect()->route('admin.feedback.index')->withSuccess('Feedback deleted successfully.');
    }
}
