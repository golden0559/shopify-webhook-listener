<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Client;

class HubspotShopifyWebhookController extends Controller
{
    public function handleHubspotWebhook(Request $request)
    {
        $data = $request->all();
        Log::info('HubSpot Webhook Received:', $data);

        // Optionally validate the request using a shared secret
        // $isValid = hash_equals($data['secret'] ?? '', env('HUBSPOT_WEBHOOK_SECRET'));
        // if (!$isValid) {
        //     return response()->json(['error' => 'Invalid webhook signature'], 403);
        // }

        if ($data['subscriptionType'] === 'contact.creation') {
            $this->processHubspotContactCreation($data);
        } elseif ($data['subscriptionType'] === 'deal.creation') {
            $this->processHubspotDealCreation($data);
        }

        return response()->json(['status' => 'success'], 200);
    }

    private function processHubspotContactCreation($data)
    {
        // Process the HubSpot contact creation event
        // Example: Sync the new contact with Shopify
        Log::info('New contact created:', $data);
        // You may call Shopify API here to create or update customer data
    }

    private function processHubspotDealCreation($data)
    {
        // Process the HubSpot deal creation event
        Log::info('New HubSpot deal created:', $data);
        // Here, create a deal in your Shopify pipeline or trigger specific Shopify workflows
    }
}
