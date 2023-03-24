<?php

namespace App\Http\Controllers\Admin;

use App\Models\Brand;
use App\Models\ProductBundle;
use App\Models\ProductVariant;
use App\Models\Product;
use App\Models\Supplier;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Vecapital\Vebase\Http\Controllers\VeController;


class ProductController extends VeController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $this->authorize('view', Product::class);
        $products = Product::orderBy('created_at', 'desc');
        $search = $request->input('search');
        if (!empty($search)) {
            $products = $products->where(function($query) use ($search) {
                $query->where('name', 'LIKE', '%' . $search . '%')
                    ->orWhere('sku', 'LIKE', '%' . $search . '%');
            });
        }

        $products = $products->paginate(10);

        return view('admin.products.index', compact('search', 'products'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->authorize('create', Product::class);
        $input = $request->all();
        if (!empty($input['options'])) {
            if (count($input['options']) > 3) {
                flash()->error('Too many options provided for a product variant.');
            } elseif (count($input['options']) <= 3) {
                foreach ($input['options'] as $index => $option) {
                    $input['option_' . ($index + 1)] = $option;
                }
            }
        }
        $validator = Validator::make($input, $this->model->createValidator);
        if ($validator->fails()) {
            flash('Error: ' . implode(" ", $validator->errors()->all()))->error();
            return back()->withInput($request->input())->withErrors($validator);
        }

        try {
            DB::beginTransaction();

            $product = Product::create($input);
            if ($request->hasFile('image')) {
                $url = Storage::url($request->file('image')->store('products/' . $product->id));
                $product->image = $url;
                $product->save();
            } elseif ($product->type == Product::TYPE_SINGLE_PRODUCT) {
                ProductVariant::create([
                    'product_id' => $product->id,
                    'image' => $product->image,
                    'name' => $input['name'],
                    'barcode' => $input['barcode'],
                    'cost_price' => $input['cost_price'],
                    'selling_price' => $input['selling_price'],
                    'measurement_unit' => $input['measurement_unit'],
                    'length' => $input['length'],
                    'width' => $input['width'],
                    'height' => $input['height'],
                    'sku' => $input['sku'],
                    'total_stock' => $input['total_stock'],
                    'status' => $input['status']
                ]);
            } elseif ($product->type == Product::TYPE_VARIANT_PRODUCT) {
                foreach($input['variants'] as $variantData) {
                    $option1 = '';
                    $option2 = '';
                    $option3 = '';
                    $explodedValue = explode('-', $variantData['sku']);
                    if (count($explodedValue) > 0) {
                        $option1 = $explodedValue[1] ?? null;
                        $option2 = $explodedValue[2] ?? null;
                        $option3 = $explodedValue[3] ?? null;
                    }
                    ProductVariant::create([
                        'product_id' => $product->id,
                        'image' => $variantData['image'] ?? null,
                        'option_1' => $option1,
                        'option_2' => $option2,
                        'option_3' => $option3,
                        'name' => $variantData['name'],
                        'barcode' => $product->barcode,
                        'cost_price' => $variantData['unit_cost'],
                        'selling_price' => $variantData['selling_price'],
                        'measurement_unit' => $product->measurement_unit,
                        'length' => $variantData['length'],
                        'width' => $variantData['width'],
                        'height' => $variantData['height'],
                        'sku' => $variantData['sku'],
                        'total_stock' => $input['total_stock'],
                        'status' => $input['status']
                    ]);
                }
            }

            if ($product->type == Product::TYPE_PRODUCT_BUNDLE) {
                $productCost = 0;
                foreach($input['bundles'] as $bundle) {
                    $productVariant = ProductVariant::find($bundle['product_variant_id']);
                    ProductBundle::create([
                        'product_id' => $product->id,
                        'product_variant_id' => $bundle['product_variant_id'],
                        'quantity' => $bundle['quantity'],
                        'selling_price' => $productVariant->selling_price,
                        'grand_total' => $bundle['quantity'] * $productVariant->selling_price,
                        'image' => $bundle['image'] ?? null,
                    ]);
                    $productCost += $productVariant->cost_price;
                }
                $product->cost_price = $productCost;
                $product->bundle_value = $product->bundles->sum('grand_total');
                $product->save();
            }

            DB::commit();
            flash()->success($product->name . ' created successfully!');
            return redirect()->route('admin.products.index');
        }  catch (Exception $exception) {
            DB::rollBack();
            Log::error($exception);
            flash('Error:' . $exception->getMessage());
            return back();
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, $id)
    {
        $product = $this->findModel($id);
        $this->authorize('view', $product);
        $bundles = ProductBundle::where('product_id', $product->id)->with('productVariant')->get();
        $variants = $product->productVariants;

        return view('admin.products.edit', compact('product',  'variants', 'bundles'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $product = $this->findModel($id);
        $this->authorize('edit', $product);
        $input = $request->all();
        if (!empty($input['options'])) {
            foreach ($input['options'] as $index => $option) {
                $input['option_' . ($index + 1)] = $option;
            }
        }

        $validator = Validator::make($input, $this->model->updateValidator);
        if ($validator->fails()) {
            flash('Error: ' . implode(" ", $validator->errors()->all()))->error();
            return back()->withInput($request->input())->withErrors($validator);
        }

        try {
            DB::beginTransaction();
            if ($request->hasFile('image')) {
                $url = Storage::url($request->file('image')->store('products/' . $product->id));
                $product->image = $url;
                $product->save();
            }
            $product->update($input);
            if (!empty($input['variants'])) {
                $variantId = [];
                foreach ($input['variants'] as $variant) {
                    $currentVariant = $product->variants->where('id', $variant['product_variant_id'])->first();
                    if ($currentVariant) {
                        $currentVariant->update($variant);
                    } else {
                        ProductVariant::create($variant + ['product_id' => $product->id]);
                    }
                    $variantId[] = $currentVariant->id;
                }
                $product->variants()->whereNotIn('id', $variantId)->delete();
            }
            if (!empty($input['bundles'])) {
                foreach ($input['bundles'] as $bundle) {
                    $currentBundle = $product->bundles()->where('id', $bundle['product_bundle_id'])->first();
                    if ($currentBundle) {
                        $currentBundle->update($bundle);
                    } else {
                        ProductBundle::create($bundle + ['product_id' => $product->id]);
                    }
                }
            }
            DB::commit();
            flash()->success($product->name . ' updated successfully!');
            return redirect()->route('admin.products.index');
        } catch (Exception $exception) {
            DB::rollBack();
            Log::error($exception);
            flash('Error:' . $exception->getMessage());
            return back();
        }
    }

    public function destroy($id)
    {
        $product = $this->findModel($id);
        $this->authorize('delete', $product);

        try {
            if (!empty($product->bundles)) {
                $product->bundles()->delete();
            }
            $product->variants()->delete();
            $product->delete();

            flash()->success($product->name . ' deleted successfully!');
            return redirect()->route('admin.products.index');
        } catch (Exception $exception) {
            DB::rollBack();
            Log::error($exception);
            flash('Error:' . $exception->getMessage());
            return back();
        }
    }

}
