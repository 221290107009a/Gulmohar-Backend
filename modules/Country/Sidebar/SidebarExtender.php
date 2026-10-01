<?php
namespace Modules\Country\Sidebar;

use Maatwebsite\Sidebar\Item;
use Maatwebsite\Sidebar\Menu;
use Maatwebsite\Sidebar\Group;
use Modules\Admin\Sidebar\BaseSidebarExtender;

class SidebarExtender extends BaseSidebarExtender
{
    public function extend(Menu $menu)
    {
        $menu->group(trans('admin::sidebar.content'), function (Group $group) {
            $group->item(trans('country::sidebar.countries'), function (Item $item) {
                $item->icon('fa fa-globe');
                $item->weight(1);
                $item->route('admin.countries.index');
                $item->authorize(
                    $this->auth->hasAccess('admin.countries.index')
                );
            });
        });

        /* $menu->group(trans('admin::sidebar.content'), function (Group $group) {
            $group->item(trans('country::countries.locations'), function (Item $item) {
                $item->icon('fa fa-globe');
                $item->weight(3);
                $item->route('admin.countries.index');
                $item->authorize(
                    $this->auth->hasAccess('admin.countries.index')
                );

                $item->item(trans('country::countries.countries'), function (Item $item) {
                    $item->icon('fa fa-globe');
                    $item->weight(1);
                    $item->route('admin.countries.index');
                    $item->authorize(
                        $this->auth->hasAccess('admin.countries.index')
                    );
                });                
            });
        }); */
    }
}
