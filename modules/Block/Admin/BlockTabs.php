<?php
namespace Modules\Block\Admin;

use Modules\Admin\Ui\Tab;
use Modules\Admin\Ui\Tabs;
class BlockTabs extends Tabs
{
    public function make()
    {
        $this->group('block_information', trans('block::blocks.tabs.group.block_information'))
            ->active()
            ->add($this->general());
    }

    private function general()
    {
        return tap(new Tab('general', trans('block::blocks.tabs.general')), function (Tab $tab) {
            $tab->active();
            $tab->weight(5);
            $tab->fields(['name', 'identifier','content', 'is_active']);
            $tab->view('block::admin.blocks.tabs.general');
        });
    }
}
