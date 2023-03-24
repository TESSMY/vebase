<?php

namespace App\Http\Controllers\Api;

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
use Vecapital\Vebase\Http\Controllers\VeApiController;
use Vecapital\Vebase\Http\Controllers\VeController;


class ProductController extends VeApiController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $this->authorize('view', Product::class);
        $limit = min(intval($request->get('limit', 10)), 1000);

        $products = Product::query();
        $products->with('variants');

        $search = $request->input('search');
        if (!empty($search)) {
            $products = $products->where(function($query) use ($search) {
                $query->where('name', 'LIKE', '%' . $search . '%')
                    ->orWhereHas('variants', function($query) use ($search) {
                        $query->where('name', 'LIKE', '%' . $search . '%');
                    });
            });
        }


        return $this->respondPagination($request, $products->paginate($limit));
    }

}
