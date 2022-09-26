<?php

namespace App\Service;

use Stripe\StripeClient;

class StripeService
{
  private string $api_key;
  private string $api_version;
  private static ?StripeClient $client = null;

  public function __construct(string  $api_key, string $api_version)
  {
    $this->api_key = $api_key;
    $this->api_version = $api_version;
  }

  public function getClient(): StripeClient
  {
    if (is_null(self::$client)) {
      self::$client = new StripeClient([
        'api_key' => $this->api_key,
        'stripe_version' => $this->api_version,
      ]);
    }

    return self::$client;
  }
}
