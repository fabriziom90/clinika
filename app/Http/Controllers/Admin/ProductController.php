<?php

namespace App\Http\Controllers\Admin;

use App\Models\Product;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\http\Controllers\Controller;
use Inertia\Inertia;

class ProductController extends Controller
{   
    public function __construct()
    {
        $this->authorizeResource(\App\Models\Product::class, 'product');
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $products = Product::all();
        return Inertia::render('Products/IndexProduct', [
            'products' => $products,
            'columns' => [
                'id' => 'ID',
                'name' => 'Nome',
                'unit_price' => 'Prezzo unitario'
            ]
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProductRequest $request)
    {
        $form_data = $request->validated();
        
        $newProduct = new Product();
        $newProduct->name = $form_data['name'];
        $newProduct->unit_price = $form_data['unit_price'];

        $newProduct->save();

        return redirect()->route('admin.products.index')->with([
            'toast' => [
                'type' => 'success',
                'message' => 'Prodotto medico creato con successo'
            ]
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProductRequest $request, Product $product)
    {
        $form_data = $request->validated();

        $product->name = $form_data['name'];
        $product->unit_price = $form_data['unit_price'];

        $product->save();

        return redirect()->route('admin.products.index')->with([
            'toast' => [
                'type' => 'success',
                'message' => "Prodotto medico modificato con successo"
            ]
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        $product->delete();

        return redirect()->route('admin.products.index')->with([
            'toast' => [
                'type' => 'success',
                'message' => "Prodotto cancellato con successo"
            ]
        ]);
    }
}
