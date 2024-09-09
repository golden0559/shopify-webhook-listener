<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use HubSpot\Factory;
use GuzzleHttp\Client;

class ShopifyWebhookController extends Controller
{
    public function handleWebhook(Request $request)
    {
        // Validate the HMAC header to ensure the webhook is legitimate
        $hmacHeader = $request->header('X-Shopify-Hmac-Sha256');
        $data = $request->getContent();
        $calculatedHmac = base64_encode(hash_hmac('sha256', $data, env('SHOPIFY_SECRET'), true));

        if (!hash_equals($hmacHeader, $calculatedHmac)) {
            return response()->json(['message' => 'Invalid signature'], 403);
        }

        // Parse the webhook data
        $payload = $request->json()->all();
        Log::info('Shopify Webhook Received:', $payload);

        // Example: Handle customer creation
        if ($request->header('X-Shopify-Topic') === 'customers/create') {
            $this->createHubSpotContact($payload['customer']);
        }

        return response()->json(['message' => 'Webhook processed successfully'], 200);
    }

    private function createHubSpotContact($customerData)
    {
        // HubSpot API integration
        $hubSpot = Factory::createWithAccessToken(env('HUBSPOT_ACCESS_TOKEN'));

        $contactData = [
            'properties' => [
                'email' => $customerData['email'],
                'firstname' => $customerData['first_name'],
                'lastname' => $customerData['last_name'],
            ],
        ];

        try {
            $hubSpot->crm()->contacts()->basicApi()->create($contactData);
            Log::info('HubSpot contact created:', $contactData);
        } catch (\Exception $e) {
            Log::error('Error creating HubSpot contact:', ['error' => $e->getMessage()]);
        }
    }
    function getHubSpotContacts()
    {
        try {
            // Create the HubSpot client
            $hubSpot = Factory::createWithAccessToken(env('HUBSPOT_ACCESS_TOKEN'));

            // Make a GET request to retrieve contacts
            $response = $hubSpot->crm()->contacts()->basicApi()->getPage();

            // Return the response
            return $response;
        } catch (\Exception $e) {
            // Handle exceptions
            return ['error' => $e->getMessage()];
        }
    }
    function getShopifyOrders()
    {
        $SHOPIFY_SHOP_URL=env('SHOPIFY_SHOP_URL');
        try {
            $client = new Client();

            $response = $client->get("https://{$SHOPIFY_SHOP_URL}/admin/api/2024-01/orders.json", [
                'headers' => [
                    'X-Shopify-Access-Token' => env('SHOPIFY_SECRET')
                ]
            ]);

            $orders = json_decode($response->getBody(), true);

            return $orders;
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }
    public function handle(Request $request) {

        return $request->getContent();

        // $hmacHeader = $request->header('X-Shopify-Hmac-Sha256');
        // $data = $request->getContent();
        // $secret = env('SHOPIFY_WEBHOOK_SECRET');

        // // Recalculate HMAC
        // $calculatedHmac = base64_encode(hash_hmac('sha256', $data, $secret, true));

        // // Compare HMACs securely
        // if (!hash_equals($hmacHeader, $calculatedHmac)) {
        //     Log::error('Webhook HMAC validation failed.');
        //     return response('Unauthorized', 401);
        // }
        // // Log the entire request for debugging purposes
        // Log::info('Shopify Webhook received:', $request->all());
    
        // print_r($data['topic']); // Example: to check the event topic
    
        // // Handle the data from Shopify
        // switch ($data['topic']) {
        //     case 'orders/create':
        //         // Handle new order creation
        //         handleNewOrder($data);
        //         break;
        //     // Add cases for other events as needed
        // }
    
        // // Return a response to acknowledge receipt of the webhook
        // return response()->json(['message' => 'Webhook received']);
    }

    function handleNewOrder($data)
    {
        // Extract necessary data
        $orderId = $data['id'];
        $customerName = $data['customer']['first_name'] . ' ' . $data['customer']['last_name'];

        // Implement the logic you want to execute when a new order is created
        // For example, sending a notification, updating a database, etc.

        // Example: Log the new order creation
        Log::info("New order created: Order ID {$orderId} by {$customerName}");

        // You can also integrate with HubSpot here if needed
        // For instance:
        // notifyHubSpotAboutOrder($orderId, $customerName);
    }
}
