<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\ShopifyIntegrationController;
use App\Http\Controllers\ShopifyWebhookController;
use App\Http\Controllers\HubspotShopifyWebhookController;

Route::get('/', function () {
    return view('welcome');
});
Route::get('/hello', function () {
    return "hello";
});

// Route::post('/shopify-webhook', [ShopifyWebhookController::class, 'handleWebhook']);
// Route::get('/shopify-webhook', [ShopifyWebhookController::class, 'getHubSpotContacts']);
Route::get('/shopify-orders', [ShopifyWebhookController::class, 'getShopifyOrders']);

Route::get('/get-orders', [ShopifyIntegrationController::class, 'getOrders']);

Route::post('/shopify-webhook', [ShopifyWebhookController::class, 'handle']);

Route::post('/postApi', function() {
    return "POST API";
});

// Route::post('/hubspot/shopify-webhook', [HubspotShopifyWebhookController::class, 'handle']);