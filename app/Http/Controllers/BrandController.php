<?php

namespace App\Http\Controllers;

use App\Models\ProductBrand;
use Illuminate\Http\Request;

class BrandController extends Controller
{
    public function store(Request $request)
    {
        $request->validate(['name' => 'required|string|max:100|unique:product_brands,name']);

        ProductBrand::create(['name' => trim($request->name)]);

        return back()->with('brand_success', 'Brand "' . $request->name . '" berhasil ditambahkan.');
    }

    public function destroy(ProductBrand $brand)
    {
        if ($brand->models()->whereHas('units')->exists()) {
            return back()->with('brand_error', 'Brand tidak bisa dihapus karena masih memiliki unit.');
        }

        $brand->delete();

        return back()->with('brand_success', 'Brand berhasil dihapus.');
    }
}
