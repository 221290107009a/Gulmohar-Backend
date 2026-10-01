<?php

namespace Modules\Service\Sidebar;

use Maatwebsite\Sidebar\Item;
use Maatwebsite\Sidebar\Menu;
use Maatwebsite\Sidebar\Group;
use Modules\Admin\Sidebar\BaseSidebarExtender;

class SidebarExtender extends BaseSidebarExtender
{
    public function extend(Menu $menu)
    {
        $menu->group(trans('admin::sidebar.content'), function (Group $group) {
            $group->item(trans('service::services.services'), function (Item $item) {
                $item->icon('fa fa-server');
                $item->weight(0);
                $item->route('admin.services.index');
                $item->authorize(
                    $this->auth->hasAccess('admin.services.index')
                );
            });
        });
    }
}
