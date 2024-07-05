<?php

namespace App\Services;

use GuzzleHttp\Client;

class BkashService
{
    protected $client;
    protected $token;

    public function __construct()
    {
        $this->client = new Client([
            'base_uri' => config('services.bkash.base_url'),
        ]);
        $this->token = $this->authenticate();
    }

    protected function authenticate()
    {
        $response = $this->client->post('/checkout/token/grant', [
            'json' => [
                'app_key' => config('services.bkash.app_key'),
                'app_secret' => config('services.bkash.app_secret'),
            ],
            'headers' => [
                'username' => config('services.bkash.username'),
                'password' => config('services.bkash.password'),
            ],
        ]);

        $data = json_decode($response->getBody(), true);
        return $data['id_token'];
    }

    public function createPayment($amount, $invoiceNumber)
    {
        $response = $this->client->post('/checkout/payment/create', [
            'json' => [
                'amount' => $amount,
                'currency' => 'BDT',
                'intent' => 'sale',
                'merchantInvoiceNumber' => $invoiceNumber,
            ],
            'headers' => [
                'Authorization' => $this->token,
                'X-APP-Key' => config('services.bkash.app_key'),
            ],
        ]);

        return json_decode($response->getBody(), true);
    }

    public function executePayment($paymentID)
    {
        $response = $this->client->post('/checkout/payment/execute/' . $paymentID, [
            'headers' => [
                'Authorization' => $this->token,
                'X-APP-Key' => config('services.bkash.app_key'),
            ],
        ]);

        return json_decode($response->getBody(), true);
    }

    public function queryPayment($paymentID)
    {
        $response = $this->client->get('/checkout/payment/query/' . $paymentID, [
            'headers' => [
                'Authorization' => $this->token,
                'X-APP-Key' => config('services.bkash.app_key'),
            ],
        ]);

        return json_decode($response->getBody(), true);
    }
}
