<?php
namespace Modules\Seller\Sidebar;

use Maatwebsite\Sidebar\Item;
use Maatwebsite\Sidebar\Menu;
use Maatwebsite\Sidebar\Group;
use Modules\Admin\Sidebar\BaseSidebarExtender;

class SidebarExtender extends BaseSidebarExtender
{
    public function extend(Menu $menu)
    {
        /* $menu->group(trans('admin::sidebar.content'), function (Group $group) {
            $group->item(trans('seller::sidebar.sellers'), function (Item $item) {
                $item->icon('fa fa-sticky-note');
                $item->weight(8);
                $item->route('admin.sellers.index');
                $item->authorize(
                    $this->auth->hasAccess('admin.sellers.index')
                );                
            });
        }); */
    }
}
