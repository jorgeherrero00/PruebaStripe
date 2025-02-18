<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Stripe\StripeClient;

class StripeController extends Controller
{
    public function getProducts()
{
    $stripe = new StripeClient(env('STRIPE_SECRET')); // Clave secreta de Stripe

    try {
        $products = $stripe->products->all();
        $prices = $stripe->prices->all();

        $productList = [];

        foreach ($products->data as $product) {


            // Verificar qué contiene el array de imágenes
            if (!empty($product->images)) {
                $image = $product->images[0]; // Toma la primera imagen si existe
            } else {
                $image = "https://via.placeholder.com/150"; // Imagen de respaldo
            }

            foreach ($prices->data as $price) {
                if ($price->product === $product->id) {
                    $productList[] = [
                        'id' => $product->id,
                        'name' => $product->name,
                        'description' => $product->description ?? 'Sin descripción',
                        'image' => $image,
                        'price_id' => $price->id,
                        'amount' => $price->unit_amount / 100, // Stripe devuelve en centavos
                        'currency' => strtoupper($price->currency),
                    ];
                }
            }
        }

        return response()->json($productList);

    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
}

}
