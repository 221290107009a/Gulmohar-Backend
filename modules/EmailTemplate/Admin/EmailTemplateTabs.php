<?php
namespace Modules\EmailTemplate\Admin;

use Modules\Admin\Ui\Tab;
use Modules\Admin\Ui\Tabs;
use Modules\EmailTemplate\Entities\EmailTemplate;
class EmailTemplateTabs extends Tabs
{
    public function make()
    {
        $this->group('emailtemplate_information', trans('emailtemplate::emailtemplates.tabs.group.emailtemplate_information'))
            ->active()
            ->add($this->general())
            ->add($this->images());
    }

    private function general()
    {
        return tap(new Tab('general', trans('emailtemplate::emailtemplates.tabs.general')), function (Tab $tab) {
            $tab->active();
            $tab->weight(5);
            $tab->fields(['name', 'is_active']);
            $tab->view('emailtemplate::admin.emailtemplates.tabs.general');
        });
    }

    private function images()
    {
        return tap(new Tab('images', trans('emailtemplate::emailtemplates.tabs.images')), function (Tab $tab) {
            $tab->weight(8);
            $tab->view('emailtemplate::admin.emailtemplates.tabs.images');
        });
    }
}
