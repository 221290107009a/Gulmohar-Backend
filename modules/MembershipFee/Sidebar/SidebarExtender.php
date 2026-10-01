<?php

namespace Modules\MembershipFee\Sidebar;

use Maatwebsite\Sidebar\Item;
use Maatwebsite\Sidebar\Menu;
use Maatwebsite\Sidebar\Group;
use Modules\Admin\Sidebar\BaseSidebarExtender;

class SidebarExtender extends BaseSidebarExtender
{
    public function extend(Menu $menu)
    {
        $menu->group(trans('admin::sidebar.content'), function (Group $group) {
            $group->item(trans('user::sidebar.member_management'), function (Item $item) {
                $item->icon('fa fa-bolt');
                $item->weight(1);
                
                $item->item(trans('user::sidebar.members'), function (Item $item) {
                    $item->weight(1);
                    $item->route('admin.member.index');
                    $item->authorize(
                        $this->auth->hasAccess('admin.member.index')
                    );
                });
                $item->item(trans('user::sidebar.reffered_users_list'), function (Item $item) {
                    $item->weight(2);
                    $item->route('admin.reffered_users_list.index');
                }); 
                $item->item(trans('membershipfee::membership_fees.membership_fees'), function (Item $item) {
                    $item->weight(3);
                    $item->route('admin.membership_fees.index');
                    $item->authorize(
                        $this->auth->hasAccess('admin.membership_fees.index')
                    );
                });
                $item->item(trans('user::sidebar.subscription_histories'), function (Item $item) {
                    $item->weight(4);
                    $item->route('admin.subscription_histories.index');
                }); 
            });
        });
    }
}
