<?php

namespace Modules\User\Admin;

use Modules\Admin\Ui\Tab;
use Modules\Admin\Ui\Tabs;
use Modules\User\Entities\Role;
use Modules\User\Entities\ReferralUsage;
use Modules\User\Repositories\Permission;
use Modules\Service\Entities\Service;
use Modules\User\Entities\UserService;
use Modules\Support\State;

class UserTabs extends Tabs
{
    public function make()
    {
        $this->group('user_information', trans('user::users.tabs.group.user_information'))
            ->active()
            ->add($this->account())
            ->add($this->permissions())
            ->add($this->newPassword())
            ->add($this->images())
            ->add($this->refferedUsers())
            ->add($this->services());
    }

    private function account()
    {
        $states = State::get('IN');
        return tap(new Tab('account', trans('user::users.tabs.account')), function (Tab $tab) use ($states) {
            $tab->active();
            $tab->weight(10);

            $tab->fields([
                'first_name',
                'last_name',
                'email',
                'phone',
                'fullname',
                'activated',
                'roles',
            ]);

            $tab->view('user::admin.users.tabs.account', [
                'roles' => Role::list(),
                'states' => $states,
            ]);
        });
    }


    private function permissions()
    {
        if (!request()->routeIs('admin.users.edit')) {
            return;
        }
        return tap(new Tab('permissions', trans('user::users.tabs.permissions')), function (Tab $tab) {
            $tab->weight(20);

            $tab->view(function ($data) {
                return view('user::admin.partials.permissions.index', [
                    'entity' => $data['user'],
                    'permissions' => Permission::all(),
                ]);
            });
        });
    }

    private function newPassword()
    {
        if (request()->routeIs('admin.member.create')  || request()->routeIs('admin.member.edit')) {
            return;
        }
        return tap(new Tab('new_password', trans('user::users.tabs.new_password')), function (Tab $tab) {
            $tab->weight(30);
            $tab->fields(['password', 'password_confirmation']);
            $tab->view('user::admin.users.tabs.new_password');
        });
    }

    private function refferedUsers()
    {
        if (request()->routeIs('admin.member.edit')) {
            $userId = request()->route('id');
            $refferedUsers = ReferralUsage::with('referredUser')->where('referrer_id', $userId)->latest()->paginate(10);
            $refferedUsersCount = ReferralUsage::where('referrer_id', $userId)->count();
            $print = '';
            if ($refferedUsersCount != 0) {
                $print = ' ('.$refferedUsersCount.')';
            }
            return tap(new Tab('reffered_users', trans('user::users.tabs.reffered_users').$print ), function (Tab $tab) use ($refferedUsers) {
                $tab->weight(40);
                $tab->view('user::admin.member.tabs.reffered_users', [
                    'refferedUsers' => $refferedUsers,
                ]);
            });
        } else{
            return;
        }
    }

    private function services()
    {
        if (request()->routeIs('admin.member.edit')) {
            $userId = request()->route('id');
            
            // Get all services (including inactive ones)
            $allServices = Service::orderBy('position')->get();
            
            // Get user's service settings
            $userServices = UserService::where('user_id', $userId)->get()->keyBy('service_id');
            
            // Merge service data with user's enabled status
            $services = $allServices->map(function ($service) use ($userServices) {
                $userService = $userServices->get($service->id);
                if (!empty($userService)) {
                    $service->is_enabled = $userService->is_enabled;
                }else{
                    if ($service->is_active) {
                        $service->is_enabled = true;
                    }else{
                        $service->is_enabled = false;
                    }
                }
                return $service;
            });
            
            return tap(new Tab('services', trans('user::users.tabs.services')), function (Tab $tab) use ($services, $userId) {
                $tab->weight(43);
                $tab->view('user::admin.member.tabs.services', [
                    'services' => $services,
                    'userId' => $userId,
                ]);
            });
        } else{
            return;
        }
    }

    private function images()
    {
        if (request()->routeIs('admin.member.edit') || request()->routeIs('admin.member.create')) {
            return tap(new Tab('images', "Profile Banner"), function (Tab $tab) {
                $tab->weight(15);
                $tab->view('user::admin.member.tabs.images');
            });
        }
    }
}