<?php
namespace Modules\Country\Admin;

use Modules\Admin\Ui\Tab;
use Modules\Admin\Ui\Tabs;
use Modules\Country\Entities\Country;
class CountryTabs extends Tabs
{
    public function make()
    {
        $this->group('country_information', trans('country::countries.tabs.group.country_information'))
            ->active()
            ->add($this->general());
    }

    private function general()
    {
        return tap(new Tab('general', trans('country::countries.tabs.general')), function (Tab $tab) {
            $tab->active();
            $tab->weight(5);
            $tab->fields(['title', 'is_active']);
            $tab->view('country::admin.countries.tabs.general');
        });
    }
}
