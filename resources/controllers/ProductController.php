<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
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


class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
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
        $suppliers = Supplier::all();
        $products = Product::all();
        $brands = Brand::all();

        return view('admin.products.form', compact('suppliers', 'products', 'brands'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $input = $request->all();
        foreach ($input['options'] as $index => $option) {
            $input['option_' . ($index + 1)] = $option;
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
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Product $product)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Product $product)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Product $product)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'name' => 'required|max:255',
            'sku' => 'required|max:255|unique:App\Models\Product,sku',
            'supplier_id' => 'required',
            'total_stock' => 'required|numeric'
        ]);

        if ($validator->fails()) {
            flash('Error: ' . $validator->errors())->error();
            return back()->withInput($input)->withErrors($validator);
        }

        try {
            DB::beginTransaction();
            if ($request->hasFile('image')) {
                $url = Storage::url($request->file('image')->store('products/' . $product->id));
                $product->image = $url;
                $product->save();
            }
            $product->update($input);
            foreach ($input['variants'] as $variant) {
                $currentVariant = ProductVariant::find($variant['id']);
                if ($currentVariant) {
                    $currentVariant->update($variant);
                } else {
                    ProductVariant::create($variant);
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

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Product $product)
    {
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
