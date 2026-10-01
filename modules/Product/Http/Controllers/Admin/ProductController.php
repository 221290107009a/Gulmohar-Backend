<?php

namespace Modules\Product\Http\Controllers\Admin;

use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Contracts\View\View;
use Modules\Product\Entities\Product;
use Modules\Product\Entities\ProductSizeInventory;
use Illuminate\Contracts\View\Factory;
use Illuminate\Foundation\Application;
use Modules\Admin\Traits\HasCrudActions;
use Modules\Product\Http\Requests\SaveProductRequest;
use Modules\Product\Transformers\ProductEditResource;

class ProductController
{
    use HasCrudActions;

    /**
     * Model for the resource.
     *
     * @var string
     */
    protected string $model = Product::class;

    /**
     * Label of the resource.
     *
     * @var string
     */
    protected string $label = 'product::products.product';

    /**
     * View path of the resource.
     *
     * @var string
     */
    protected string $viewPath = 'product::admin.products';

    /**
     * Form requests for the resource.
     *
     * @var array|string
     */
    protected string|array $validation = SaveProductRequest::class;


    /**
     * Store a newly created resource in storage.
     *
     * @return Response|JsonResponse
     */
    public function store()
    {
        $this->disableSearchSyncing();

        $entity = $this->getModel()->create(
            $this->getRequest('store')->all()
        );

        // Save size inventory rows
        $this->saveSizeInventory($entity);

        $this->searchable($entity);

        $message = trans('admin::messages.resource_created', ['resource' => $this->getLabel()]);

        if (request()->query('exit_flash')) {
            session()->flash('exit_flash', $message);
        }

        if (request()->wantsJson()) {
            return response()->json(
                [
                    'success' => true,
                    'message' => $message,
                    'product_id' => $entity->id,
                ], 200
            );
        }

        return redirect()->route("{$this->getRoutePrefix()}.index")
            ->withSuccess($message);
    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     *
     * @return Factory|View|Application
     */
    public function edit($id): Factory|View|Application
    {
        $entity = $this->getEntity($id);
        $productEditResource = new ProductEditResource($entity);

        return view("{$this->viewPath}.edit",
            [
                'product' => $entity,
                'product_resource' => $productEditResource->response()->content(),
            ]
        );
    }


    /**
     * Update the specified resource in storage.
     *
     * @param int $id
     */
    public function update($id)
    {
        $entity = $this->getEntity($id);

        $this->disableSearchSyncing();

        $entity->update(
            $this->getRequest('update')->all()
        );

        $entity->withoutEvents(function () use ($entity) {
            $entity->touch();
        });

        // Save / sync size inventory rows
        $this->saveSizeInventory($entity);

        $productEditResource = new ProductEditResource($entity);

        $this->searchable($entity);

        $message = trans('admin::messages.resource_updated', ['resource' => $this->getLabel()]);

        if (request()->query('exit_flash')) {
            session()->flash('exit_flash', $message);
        }

        if (request()->wantsJson()) {
            return response()->json(
                [
                    'success' => true,
                    'message' => $message,
                    'product_resource' => $productEditResource,
                ], 200
            );
        }
    }


    /**
     * Save / sync the size_inventory rows for a product.
     * Expects request input: size_inventory = [
     *   [ 'size' => 'UK 7', 'qty' => 12, 'in_stock' => 1 ],
     *   ...
     * ]
     */
    protected function saveSizeInventory(Product $product): void
    {
        $rows = request()->input('size_inventory', []);

        if (!is_array($rows)) {
            return;
        }

        // Delete existing sizes for this product and recreate
        $product->sizeInventory()->delete();

        foreach (array_values($rows) as $position => $row) {
            $sizeName = trim($row['size'] ?? '');
            if ($sizeName === '') {
                continue;
            }

            ProductSizeInventory::create([
                'product_id' => $product->id,
                'size'       => $sizeName,
                'qty'        => (int) ($row['qty'] ?? 0),
                'in_stock'   => filter_var($row['in_stock'] ?? true, FILTER_VALIDATE_BOOLEAN),
                'position'   => $position,
            ]);
        }
    }
}
