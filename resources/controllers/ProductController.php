<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\ProductBundle;
use App\Models\ProductVariant;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\User;
use Exception;
use HaydenPierce\ClassFinder\ClassFinder;
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
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $this->authorize('create', Product::class);
        $suppliers = Supplier::all();
        $products = Product::all();
        $variants = ProductVariant::get(['id', 'name', 'sku' , 'selling_price']);
        $brands = Brand::all();

        return view('admin.products.form', compact('suppliers', 'products', 'brands', 'variants'));
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
        $input = $request->all();;
        if (!empty($input['options'])) {
            foreach ($input['options'] as $index => $option) {
                $input['option_' . ($index + 1)] = $option;
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
            }
            $product->save();

            if ($product->type == Product::TYPE_SINGLE_PRODUCT) {
                $variant = ProductVariant::create([
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
                $variant->save();
            }

            if ($product->type == Product::TYPE_VARIANT_PRODUCT) {
                foreach($input['variants'] as $variantData) {
                    $option_1 = '';
                    $option_2 = '';
                    $option_3 = '';
                    $explodedValue = explode('-', $variantData['sku']);
                    if (count($explodedValue) > 0) {
                        $option_1 = $explodedValue[1] ?? null;
                        $option_2 = $explodedValue[2] ?? null;
                        $option_3 = $explodedValue[3] ?? null;
                    }
                    $variant = ProductVariant::create([
                        'product_id' => $product->id,
                        'image' => $variantData['image'] ?? null,
                        'option_1' => $option_1,
                        'option_2' => $option_2,
                        'option_3' => $option_3,
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
                    $variant->save();
                }
            }

            if ($product->type == Product::TYPE_PRODUCT_BUNDLE) {
                $productCost = 0;
                foreach($input['bundles'] as $bundle) {
                    $productVariant = ProductVariant::find($bundle['product_variant_id']);
                    $bundle = ProductBundle::create([
                        'product_id' => $product->id,
                        'product_variant_id' => $bundle['product_variant_id'],
                        'quantity' => $bundle['quantity'],
                        'selling_price' => $productVariant->selling_price,
                        'grand_total' => $bundle['quantity'] * $productVariant->selling_price,
                        'image' => $bundle['image'] ?? null,
                    ]);
                    $bundle->save();
                    $productCost += $productVariant->cost_price;
                }
                $product->cost_price = $productCost;
                $product->bundle_value = $product->bundles->sum('grand_total');
                $product->save();
            }

            DB::commit();
            flash()->success($product->name . ' created successfully!');
            return redirect()->route('admin.products.index');
        } catch (Exception $exception) {
            DB::rollBack();
            Log::error($exception);
            flash('Error:' . $exception)->error();
            throw $exception;
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, $id)
    {
        $this->authorize('edit', Product::class);
        $suppliers = Supplier::all();
        $product = $this->findModel($id);
        $bundles = ProductBundle::where('product_id', $product->id)->with('productVariant')->get();
        $variants = $product->variants;
        $brands = Brand::all();

        return view('admin.products.form', compact('suppliers', 'product', 'brands', 'variants', 'bundles'));
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $id)
    {
        $this->authorize('view', Product::class);
        $product = $this->findModel($id);

        return view('admin.products.view', compact('product'));
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

        $validator = Validator::make($input, $this->model->createValidator);
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
                foreach ($input['variants'] as $variant) {
                    $currentVariant = $product->variants->where('id', $variant['product_variant_id'])->first();
                    if ($currentVariant) {
                        $currentVariant->update($variant);
                    } else {
                        ProductVariant::create($variant + ['product_id' => $product->id]);
                    }
                }
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
        } catch (\PHPUnit\Exception $exception) {
            DB::rollBack();
            Log::error($exception);
            flash('Error:' . $exception)->error();
            throw $exception;
        }
    }

    public function destroy($id)
    {
        $product = $this->findModel($id);
        $this->authorize('delete', $product);

        try {
            $product->variants()->delete();
            $product->delete();

            DB::commit();
            return redirect()->route('admin.products.index')->with('message', $product->name . ' removed successfully.');
        } catch (Exception $exception) {
            DB::rollback();
            Log::error($exception);
            flash()->error('Error:' . $exception);
            throw $exception;
        }
    }
}
