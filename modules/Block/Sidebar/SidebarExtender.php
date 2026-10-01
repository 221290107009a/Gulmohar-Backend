<?php
namespace Modules\Block\Sidebar;

use Maatwebsite\Sidebar\Item;
use Maatwebsite\Sidebar\Menu;
use Maatwebsite\Sidebar\Group;
use Modules\Admin\Sidebar\BaseSidebarExtender;

class SidebarExtender extends BaseSidebarExtender
{
    public function extend(Menu $menu)
    {
        $menu->group(trans('admin::sidebar.content'), function (Group $group) {
            $group->item(trans('block::sidebar.blocks'), function (Item $item) {
                $item->icon('fa fa-sticky-note');
                $item->weight(11);
                $item->route('admin.blocks.index');
                $item->authorize(
                    $this->auth->hasAccess('admin.blocks.index')
                );                
            });
        });
    }
}
