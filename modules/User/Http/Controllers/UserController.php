<?php

namespace Modules\User\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\User\Entities\User;
use Modules\User\Entities\UserData;
use Spatie\Browsershot\Browsershot;
use Modules\Media\Entities\File;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Modules\User\Http\Requests\UpdateProfileRequest;
use Tymon\JWTAuth\Facades\JWTAuth;


class UserController extends Controller
{
    public function saveImage(Request $request) {
        
        $imagedata = $request->input('imagedata');

        $userId = $request->input('id');

        if($imagedata){
            list($type, $imagedata) = explode(';', $imagedata);
            list(, $imagedata)      = explode(',', $imagedata);
            $imagedata = base64_decode($imagedata);
        }
        file_put_contents(public_path('uploads/pictures') . '/'.$userId.'_'.'member_registration.png', $imagedata);
    }   

    /**
     * Display a listing of the resource.
     * @return Renderable
     */
    public function index()
    {
        return view('user::index');
    }

    /**
     * Show the form for creating a new resource.
     * @return Renderable
     */
    public function create()
    {
        return view('user::create');
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Renderable
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Show the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function show($id)
    {
        return view('user::show');
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Renderable
     */
    public function edit($id)
    {
        return view('user::edit');
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int $id
     * @return Renderable
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Renderable
     */
    public function destroy($id)
    {
        //
    }

    public function memberRegisted($id)
    {   
       $user = User::findOrFail($id);

       $logo = File::findOrNew(setting('storefront_header_logo'))->path;
      
       return view('public.members.memberdetail',compact('user', 'logo'));   
    }

    public function getUserDetails(Request $request)
    {        
        $authenticatedUser = $request->user();        
        if (!$authenticatedUser) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 401);
        }

        $userData = UserData::where('user_id', $authenticatedUser->id)->first();        
        if ($userData) {
            $authenticatedUser = array_merge($authenticatedUser->toArray(), $userData->toArray());
        }        
        $authenticatedUser['photo'] = url('/uploads/pictures/') . '/' . $authenticatedUser['photo'];

        return response()->json([
            'success' => true,
            'user' => $authenticatedUser,
        ]);        
    }


    public function updateProfile(UpdateProfileRequest $request)
    {
        $authUser = $request->user();
        if (!$authUser) {
            return response()->json(['success' => false, 'message' => 'User not found.'], 404);
        }
        $user = User::find($authUser->id);
        $userData = UserData::find($request->id);
        if (!$userData) {
            return response()->json(['success' => false, 'message' => 'User data not found.'], 404);
        }

        if ($request->filled('password')) {
            $request->merge(['password' => bcrypt($request->password)]);
        }

        if ($request->hasFile('photo')) {
            $file = $request->file('photo'); 
            $extension = $file->getClientOriginalExtension();
            $filename = time() . '.' . $extension;
            $file->move(public_path('uploads/pictures'), $filename);
            $userData->photo = $filename; 
        }

        $user->first_name = $request->first_name;
        $user->last_name = $request->last_name;
        $user->email = $request->email;
        $user->phone = $request->phone;
        $user->save();

        $userData->dob = $request->dob;
        $userData->whatsapp_mobile = $request->whatsapp_mobile;
        $userData->address = $request->address;
        $userData->city = $request->city;
        $userData->category = $request->category;
        $userData->education = $request->education;
        $userData->religion = $request->religion;
        $userData->cast = $request->cast;
        $userData->gender = $request->gender;
        $userData->save();

        return response()->json(['success' => true, 'message' => 'Profile updated successfully.']);
    }

}
