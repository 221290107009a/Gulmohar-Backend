<?php

namespace Modules\MembershipFee\Admin;

use Modules\Admin\Ui\Tab;
use Modules\Admin\Ui\Tabs;

class MembershipFeeTabs extends Tabs
{
    /**
     * Indicate that submit button should add offset class.
     *
     * @var bool
     */
    protected $buttonOffset = false;


    public function make()
    {
        $this->group('membership_fee_information', trans('membershipfee::membership_fees.tabs.group.membership_fee_information'))
            ->active()
            ->add($this->general());
    }

    private function general()
    {
        return tap(new Tab('general', trans('membershipfee::membership_fees.tabs.general')), function (Tab $tab) {
            $tab->active();
            $tab->weight(5);
            $tab->fields(['name']);
            $tab->view('membershipfee::admin.membership_fees.tabs.general');
        });
    }

}
