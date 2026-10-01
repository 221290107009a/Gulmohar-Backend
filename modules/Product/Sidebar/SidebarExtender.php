<?php

namespace Modules\Product\Sidebar;

use Maatwebsite\Sidebar\Item;
use Maatwebsite\Sidebar\Menu;
use Maatwebsite\Sidebar\Group;
use Modules\Admin\Sidebar\BaseSidebarExtender;

class SidebarExtender extends BaseSidebarExtender
{
    public function extend(Menu $menu)
    {
        $menu->group(trans('admin::sidebar.content'), function (Group $group) {
            $group->item(trans('product::sidebar.online_store'), function (Item $item) {
                $item->icon('fa fa-cube');
                $item->weight(10);
                $item->route('admin.products.index');
                $item->authorize(
                    $this->auth->hasAnyAccess([
                        'admin.products.create',
                        'admin.products.index',
                        'admin.categories.index',
                        'admin.attributes.index',
                        'admin.attribute_sets.index',
                        'admin.variations.index',
                        'admin.options.index',
                    ])
                );
                $item->item(trans('seller::sidebar.sellers'), function (Item $item) {                    
                    $item->weight(1);
                    $item->route('admin.sellers.index');
                    $item->authorize(
                        $this->auth->hasAccess('admin.sellers.index')
                    );                
                });

                $item->item(trans('product::sidebar.create_product'), function (Item $item) {
                    $item->weight(2);
                    $item->route('admin.products.create');
                    $item->authorize(
                        $this->auth->hasAccess('admin.products.create')
                    );
                });

                $item->item(trans('product::sidebar.all_products'), function (Item $item) {
                    $item->weight(6);
                    $item->route('admin.products.index');
                    $item->isActiveWhen(route('admin.products.index', null, false));
                    $item->authorize(
                        $this->auth->hasAccess('admin.products.index')
                    );
                });

                $item->item(trans('order::orders.orders'), function (Item $item) {
                    $item->weight(8);
                    $item->route('admin.orders.index');
                    $item->authorize(
                        $this->auth->hasAccess('admin.orders.index')
                    );
                });
            });
        });
    }
}
