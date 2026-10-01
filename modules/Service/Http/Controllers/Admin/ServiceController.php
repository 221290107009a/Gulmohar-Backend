<?php

namespace Modules\Service\Http\Controllers\Admin;

use Modules\Admin\Traits\HasCrudActions;
use Modules\Service\Entities\Service;
use Modules\Service\Http\Requests\SaveServiceRequest;
use Illuminate\Http\Request;

class ServiceController
{
    use HasCrudActions;

    /**
     * Model for the resource.
     *
     * @var string
     */
    protected $model = Service::class;

    /**
     * Label of the resource.
     *
     * @var string
     */
    protected $label = 'service::services.service';

    /**
     * View path of the resource.
     *
     * @var string
     */
    protected $viewPath = 'service::admin.services';

    /**
     * Form requests for the resource.
     *
     * @var array
     */
    protected $validation = SaveServiceRequest::class;   

    /**
     * Search users by name, email, or mobile number.
     *
     * @param Request $request
     * @param int $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function searchUsers(Request $request, $id)
    {
        try {
            $query = $request->input('query', '');
            
            if (empty($query)) {
                return response()->json([
                    'success' => true,
                    'users' => []
                ]);
            }
            $users = \DB::table('users')
                ->leftJoin('user_roles', 'users.id', '=', 'user_roles.user_id')
                ->where('role_id', 2)
                ->where('users.fullname', '!=', '')
                ->where('users.email', '!=', '')
                ->where(function($q) use ($query) {
                    $q->where('users.fullname', 'LIKE', "%{$query}%")
                      ->orWhere('users.email', 'LIKE', "%{$query}%")
                      ->orWhere('users.phone', 'LIKE', "%{$query}%");
                })
                ->whereNotIn('users.id', function($subQuery) use ($id) {
                    $subQuery->select('user_id')
                        ->from('user_services')
                        ->where('service_id', $id);
                })
                ->select('users.id', 'users.fullname', 'users.email', 'users.phone')
                ->limit(10)
                ->get();

            return response()->json([
                'success' => true,
                'users' => $users
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error searching users: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Assign a user to the service.
     *
     * @param Request $request
     * @param int $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function assignUser(Request $request, $id)
    {
        try {
            $userId = $request->input('user_id');
            
            if (empty($userId)) {
                return response()->json([
                    'success' => false,
                    'message' => 'User ID is required'
                ], 400);
            }

            // Check if service exists
            $service = Service::findOrFail($id);
            
            // Check if user exists
            $user = \Modules\User\Entities\User::findOrFail($userId);

            // Check if user is already assigned
            $existingAssignment = \DB::table('user_services')
                ->where('user_id', $userId)
                ->where('service_id', $id)
                ->first();

            if ($existingAssignment) {
                return response()->json([
                    'success' => false,
                    'message' => 'User is already assigned to this service'
                ], 400);
            }

            // Create the assignment
            \DB::table('user_services')->insert([
                'user_id' => $userId,
                'service_id' => $id,
                'is_enabled' => true,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'User assigned to service successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error assigning user: ' . $e->getMessage()
            ], 500);
        }
    }

}
