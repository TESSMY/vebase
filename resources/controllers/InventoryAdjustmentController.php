<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InventoryAdjustment;
use App\Models\InventoryAdjustmentItem;
use App\Models\Product;
use App\Models\ProductVariant;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Vecapital\Vebase\Http\Controllers\VeController;

class InventoryAdjustmentController extends VeController
{
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->authorize('create', $this->model);
        
        $input = $request->input();

        if (empty($this->model->createValidator)) {
            flash('Error: createValidator is empty')->error();
            return back()->withInput($request->input()); 
        }

        $validator = Validator::make($input, $this->model->createValidator);

        if ($validator->fails()) {
            flash('Error: ' . implode(" ", $validator->errors()->all()))->error();
            return back()->withInput($request->input())->withErrors($validator);
        }
        
        try {
            DB::beginTransaction();

            $inventoryAdjustment = InventoryAdjustment::create($input + ['created_by' => Auth::id()]);

            foreach ($input['products'] as $product) {

                if (isset($product['product_variant_id'])) {
                    // product variant & single product
                    $productVariant = ProductVariant::find($product['product_variant_id']);

                    if (empty($productVariant)) {
                        flash('Error: Product variant with ID #' . $product['product_variant_id'] . ' not found')->error();
                        return back();
                    }
                    
                    InventoryAdjustmentItem::create([
                        'inventory_adjustment_id' => $inventoryAdjustment->id,
                        'product_id' => $productVariant->product_id, 
                        'product_variant_id' => $productVariant->id,
                        'sku' => $productVariant->sku,
                        'old_value' => $productVariant->total_stock,
                        'new_value' => $product['new_value'],
                    ]);
                } else {
                    // product bundle
                    $productModel = Product::find($product['product_id']);

                    if (empty($productModel)) {
                        flash('Error: Product with ID #' . $product['product_id'] . ' not found')->error();
                        return back();
                    }
                    if ($productModel->type != Product::TYPE_PRODUCT_BUNDLE) {
                        flash('Error: Product with ID #' . $product['product_id'] . ' is not a product bundle')->error();
                        return back();
                    }

                    InventoryAdjustmentItem::create([
                        'inventory_adjustment_id' => $inventoryAdjustment->id,
                        'product_id' => $productModel->product_id, 
                        'sku' => $productModel->sku,
                        'old_value' => $productModel->total_stock,
                        'new_value' => $product['new_value'],
                    ]);
                }
            }

            DB::commit();
            flash()->success('Successfully created inventory adjustment');
            return redirect()->route('admin.inventory-adjustments.index');
        } catch (Exception $exception) {
            Log::error($exception);
            DB::rollBack();
            flash()->error('There was an issue creating inventory adjustment');
            return back()->withInput();
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $inventoryAdjustment)
    {
        $this->authorize('create', $this->model);
        $inventoryAdjustment = $this->findModel($inventoryAdjustment);
        $inventoryAdjustment->load(['createdBy', 'adjustmentItems.product', 'adjustmentItems.productVariant']);

        $compact = [
            'inventoryAdjustment' => $inventoryAdjustment,
            'modelName' => $this->modelName,
            'routeName' => $this->routeName,
            'routePrefix' => $this->folder,
        ];
        
        return view('admin.inventory-adjustments.show', $compact);
    }
}
