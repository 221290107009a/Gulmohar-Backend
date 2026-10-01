<?php
namespace Modules\EmailTemplate\Sidebar;

use Maatwebsite\Sidebar\Item;
use Maatwebsite\Sidebar\Menu;
use Maatwebsite\Sidebar\Group;
use Modules\Admin\Sidebar\BaseSidebarExtender;

class SidebarExtender extends BaseSidebarExtender
{
    public function extend(Menu $menu)
    {
        $menu->group(trans('admin::sidebar.content'), function (Group $group) {
            $group->item(trans('emailtemplate::sidebar.emailtemplates'), function (Item $item) {
                $item->weight(1);
                $item->icon('fa fa-envelope');
                $item->route('admin.emailtemplates.index');
                $item->authorize(
                        $this->auth->hasAccess('admin.emailtemplates.index')
                    );    

            });
        });

    }
}
