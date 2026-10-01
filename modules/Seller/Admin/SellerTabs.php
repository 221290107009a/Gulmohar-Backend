<?php
namespace Modules\Seller\Admin;

use Modules\Admin\Ui\Tab;
use Modules\Admin\Ui\Tabs;
use Modules\User\Entities\User;

class SellerTabs extends Tabs
{
    public function make()
    {
        $this->group('seller_information', trans('seller::sellers.tabs.group.seller_information'))
            ->active()
            ->add($this->general())
            ->add($this->images());
    }

    private function general()
    {
        $users = User::all()->pluck('email', 'id')->map(function($email, $id) {
            $user = User::find($id);
            return $user->id . ' - ' . $user->first_name . ' ' . $user->last_name . ' (' . $email . ')';
        });
        
        return tap(new Tab('general', trans('seller::sellers.tabs.general')), function (Tab $tab) use ($users) {
            $tab->active();
            $tab->weight(5);
            $tab->fields(['user_id', 'shop_name', 'owner_name', 'description', 'address', 'phone', 'email', 'category', 'gst_number', 'pan_number', 'status', 'is_active']);
            $tab->view('seller::admin.sellers.tabs.general', compact('users'));
        });
    }

    private function images()
    {
        return tap(new Tab('images', trans('seller::sellers.tabs.images')), function (Tab $tab) {
            $tab->weight(10);
            $tab->view('seller::admin.sellers.tabs.images');
        });
    }
}
