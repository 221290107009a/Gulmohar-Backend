<?php

namespace Modules\User\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\User\Entities\User;
use Modules\User\Entities\UserData;
use Modules\State\Entities\State;

class MemberController extends Controller
{

    public function getMembers()
    {

        $members = User::all();

        return view('public.members.index',compact('members'));
    }

    public function getMemberdetails($id)
    {
        $profile = User::findOrFail($id);
        return view('public.members.memberprofile',compact('profile'));
    }

    public function getState(){
        $state = State::all();        
    }

    public function meditation()
    {
        return view('public.meditation.index');
    }

    public function buddhism()
    {
        return view('public.buddhism.index');
    }

    public function aboutus()
    {
        return view('public.aboutus.index');
    }

}