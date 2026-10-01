<?php

namespace Modules\Service\Admin;

use Modules\Admin\Ui\Tab;
use Modules\Admin\Ui\Tabs;
use Modules\User\Entities\UserService;

class ServiceTabs extends Tabs
{
    /**
     * Indicate that submit button should add offset class.
     *
     * @var bool
     */
    protected $buttonOffset = false;


    public function make()
    {
        $this->group('service_information', trans('service::services.tabs.group.service_information'))
            ->active()
            ->add($this->general())
            ->add($this->images())
            ->add($this->users());
    }

    private function general()
    {
        return tap(new Tab('general', trans('service::services.tabs.general')), function (Tab $tab) {
            $tab->active();
            $tab->weight(1);
            $tab->fields(['name', 'link_url', 'webpage_url', 'gradient_colors']);
            $tab->view('service::admin.services.tabs.general');
        });
    }

    private function images()
    {
        if (!auth()->user()->hasAccess('admin.media.index')) {
            return;
        }

        return tap(new Tab('images', trans('service::services.tabs.icons')), function (Tab $tab) {
            $tab->weight(2);
            $tab->fields(['files.logo']);
            $tab->view('service::admin.services.tabs.images');
        });
    }

    private function users()
    {
        if (!request()->routeIs('admin.services.edit')) {
            return;
        }

        $serviceId = request()->route('id');
        $usersList = UserService::where('service_id', $serviceId)->get();

        return tap(new Tab('users', trans('service::services.tabs.assigned_service_users_list')), function (Tab $tab) use ($usersList) {
            $tab->weight(3);
            $tab->view('service::admin.services.tabs.users', compact('usersList'));
        });
    }
}
