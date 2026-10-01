<?php

namespace Modules\Block\Http\Controllers\Admin;

use Modules\Block\Entities\Block;
use Modules\Admin\Traits\HasCrudActions;
use Modules\Block\Http\Requests\SaveBlockRequest;

class BlockController
{
    use HasCrudActions;

    /**
     * Model for the resource.
     *
     * @var string
     */
    protected $model = Block::class;

    /**
     * Label of the resource.
     *
     * @var string
     */
    protected $label = 'block::blocks.block';

    /**
     * View path of the resource.
     *
     * @var string
     */
    protected $viewPath = 'block::admin.blocks';

    /**
     * Form requests for the resource.
     *
     * @var array|string
     */
    protected $validation = SaveBlockRequest::class;
}
