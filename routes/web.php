<?php

use Illuminate\Support\Facades\Route;
use Laravel\Cashier\Exceptions\IncompletePayment;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\StripeController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});
Route::post('/subscribe', function (Request $request) {
    $user = $request->user();
    if (!$user) {
        return response()->json(['error' => 'Usuario no autenticado'], 401);
    }

    $paymentMethod = $request->input('payment_method');
    $methodType = $request->input('method_type'); // Tipo de método de pago

    try {
        if ($methodType === "sepa_debit") {
            // Crear una suscripción con SEPA Débito
            $user->newSubscription('default', env('STRIPE_PLAN_BASIC'))
                 ->create($paymentMethod, [
                     'payment_behavior' => 'default_incomplete',
                     'payment_method_types' => ['sepa_debit'],
                 ]);
        } else {
            // Suscripción normal con tarjeta o Google Pay/Apple Pay
            $user->newSubscription('default', env('STRIPE_PLAN_BASIC'))
                 ->create($paymentMethod);
        }

        return response()->json(['message' => 'Suscripción exitosa']);

    } catch (\Exception $e) {
        return response()->json(['error' => 'Error en el pago: ' . $e->getMessage()], 402);
    }
})->middleware('auth');

Route::get('/subscription', function () {
    return view('subscription');
}); // Protege la ruta para que solo usuarios autenticados puedan suscribirse
Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');
Route::get('/stripe/products', [StripeController::class, 'getProducts'])->middleware('auth');
Route::get('/invoices', function (Request $request) {
    $user = $request->user();
    
    if (!$user->subscribed('default')) {
        return redirect('/subscription')->with('error', 'No tienes una suscripción activa.');
    }

    $invoices = $user->invoices();

    return view('invoices', compact('invoices'));
})->middleware('auth');

Route::get('/invoice/{invoiceId}', function (Request $request, $invoiceId) {
    $user = $request->user();
    
    return $user->downloadInvoice($invoiceId, [
        'vendor'  => 'Tu Empresa',
        'product' => 'Suscripción Premium'
    ]);
})->middleware('auth');