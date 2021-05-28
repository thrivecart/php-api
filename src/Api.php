<?php

namespace ThriveCart;

use ThriveCart\http\ThriveCartCurlHttpClient;
use ThriveCart\http\ThriveCartGuzzleHttpClient;
use ThriveCart\http\ThriveCartHttpClientInterface;

/**
 * ThriveCart library.
 *
 * @package ThriveCart
 */
class Api {

  const SDK_VERSION = '1.0.9';
  const API_VERSION = '1.0.0';

  public $api_config = array(
    'transactionTypes' => array(
      null,
      'any',
      'charge',
      'rebill',
      'refund',
      'cancel',
    ),
  );

  /**
   * SDK version.
   *
   * @var string $sdk_version
   */
  public $sdk_version = self::SDK_VERSION;

  /**
   * API version.
   *
   * @var string $api_version
   */
  public $api_version = self::API_VERSION;

  /**
   * The HTTP client.
   *
   * @var ThriveCartHttpClientInterface $client
   */
  protected $client;

  /**
   * The base URI.
   *
   * @var string $baseUri
   */
  public static $baseUri = 'https://thrivecart.com';

  /**
   * The REST API endpoint.
   *
   * @var string $endpoint
   */
  public $endpoint = '/api/external';

  /**
   * The order mode to use ('live' or 'test')
   *
   * @var string $mode
   */
  private static $mode = 'live';

  /**
   * The ThriveCart API access token to authenticate with.
   *
   * @var string $access_token
   */
  private $access_token;

  /**
   * ThriveCart constructor.
   *
   * @param string $access_token
   *   The ThriveCart access token
   * @param array $http_options
   *   HTTP client options.
   * @param ThriveCartHttpClientInterface $client
   *   Optional custom HTTP client. $http_options are ignored if this is set.
   */
  public function __construct($access_token, $http_options = [], ThriveCartHttpClientInterface $client = NULL) {
    $this->access_token = $access_token;

    if (!empty($client)) {
      $this->client = $client;
    }
    else {
      $this->client = $this->getDefaultHttpClient($http_options);
    }
  }

  /**
   * Sets a custom base URL for ThriveCart; this is used for development and testing and is not required in production.
   *
   * @param string $baseUri
   *   The new baseUri to send all requests to.
   */
  public static function setBaseUri($baseUri) {
    self::$baseUri = $baseUri;
  }

  /**
   * Gets the base URL for ThriveCart.
   *
   * @return string
   */
  public static function getBaseUri() {
    return self::$baseUri;
  }

  /**
   * Sets the mode to either 'live' or 'test'
   *
   * @param string $mode
   */
  public static function setMode($mode) {
    if(!in_array($mode, array('test', 'live'))) throw new Exception('Invalid mode provided to the API ("'.$mode.'").');
    self::$mode = $mode;
  }

  /**
   * Gets the current mode setting.
   *
   * @return string
   */
  public static function getMode() {
    return self::$mode;
  }

  /**
   * Sets a custom HTTP client to be used for all API requests.
   *
   * @param \ThriveCart\http\ThriveCartHttpClientInterface $client
   *   The HTTP client.
   */
  public function setClient(ThriveCartHttpClientInterface $client) {
    $this->client = $client;
  }

  /**
   * Gets ThriveCart account information for the authenticated account.
   *
   * @return array
   */
  public function ping($parameters = []) {
    return $this->request('GET', '/ping', NULL, $parameters);
  }

  /**
   * Makes a request to the ThriveCart API.
   *
   * @param string $method
   *   The REST method to use when making the request.
   * @param string $path
   *   The API path to request.
   * @param array $tokens
   *   Associative array of tokens and values to replace in the path.
   * @param array $parameters
   *   Associative array of parameters to send in the request body.
   *
   * @return array
   *   Array
   *
   * @throws ThriveCart\Exception
   */
  public function request($method, $path, $tokens = NULL, $parameters = NULL) {
    if (!empty($tokens)) {
      foreach ($tokens as $key => $value) {
        $path = str_replace('{' . $key . '}', $value, $path);
      }
    }

    // Set default request options with auth header.
    $options = [
      'headers' => [
        'Authorization' => 'Bearer '.$this->access_token,
        'X-TC-Mode' => $this->getMode(),
        'X-TC-Sdk' => 'php/'.$this->sdk_version,
        'X-TC-Version' => $this->api_version,
      ],
    ];

    return $this->client->handleRequest($method, $this->getBaseUri() . $this->endpoint . $path, $options, $parameters, true);
  }

  /**
   * Instantiates a default HTTP client based on the local environment.
   *
   * @param array $http_options
   *   HTTP client options.
   *
   * @return ThriveCartHttpClientInterface
   *   The HTTP client.
   */
  private function getDefaultHttpClient($http_options) {
    // Process HTTP options.
    // Handle deprecated 'timeout' argument.
    if (is_int($http_options)) {
      $http_options = [
        'timeout' => $http_options,
      ];
    }

    // Default timeout is 10 seconds.
    $http_options += [
      'timeout' => 20,
    ];

    $client = new ThriveCartGuzzleHttpClient($http_options);

    return $client;
  }

  /**
   * Gets information about all products owned by the authenticated account.
   *
   * @param array $parameters
   *   Associative array of optional request parameters.
   *   status: 'live' or 'test' (returns only live products, or only test products)
   *
   * @return array
   */
  public function getProducts($parameters = []) {
    return $this->request('GET', '/products', NULL, $parameters);
  }

  /**
   * Gets a ThriveCart product.
   *
   * @param string $product_id
   *   The ID of the product.
   * @param array $parameters
   *   Associative array of optional request parameters.
   *
   * @return object
   */
  public function getProduct($product_id, $parameters = []) {
    $tokens = [
      'product_id' => $product_id,
    ];

    return $this->request('GET', '/products/{product_id}', $tokens, $parameters);
  }

  /**
   * Gets a ThriveCart product's pricing options.
   *
   * @param string $product_id
   *   The ID of the product.
   * @param array $parameters
   *   Associative array of optional request parameters.
   *
   * @return object
   */
  public function getProductPricing($product_id, $parameters = []) {
    $tokens = [
      'product_id' => $product_id,
    ];

    return $this->request('GET', '/products/{product_id}/pricing_options', $tokens, $parameters);
  }

  /**
   * Gets information about all bumps owned by the authenticated account.
   *
   * @param array $parameters
   *   Associative array of optional request parameters.
   *
   * @return array
   */
  public function getBumps($parameters = []) {
    return $this->request('GET', '/bumps', NULL, $parameters);
  }

  /**
   * Gets a ThriveCart bump.
   *
   * @param string $bump_id
   *   The ID of the bump. This is the same as the product ID but will return info about the bump specifically.
   * @param array $parameters
   *   Associative array of optional request parameters.
   *
   * @return object
   */
  public function getBump($bump_id, $parameters = []) {
    $tokens = [
      'bump_id' => $bump_id,
    ];

    return $this->request('GET', '/bumps/{bump_id}', $tokens, $parameters);
  }

  /**
   * Gets a ThriveCart bump's pricing options.
   *
   * @param string $bump_id
   *   The ID of the bump.
   * @param array $parameters
   *   Associative array of optional request parameters.
   *
   * @return object
   */
  public function getBumpPricing($bump_id, $parameters = []) {
    $tokens = [
      'bump_id' => $bump_id,
    ];

    return $this->request('GET', '/bumps/{bump_id}/pricing_options', $tokens, $parameters);
  }

  /**
   * Gets information about all upsells owned by the authenticated account.
   *
   * @param array $parameters
   *   Associative array of optional request parameters.
   *
   * @return array
   */
  public function getUpsells($parameters = []) {
    return $this->request('GET', '/upsells', NULL, $parameters);
  }

  /**
   * Gets a ThriveCart upsell.
   *
   * @param string $upsell_id
   *   The ID of the upsell.
   * @param array $parameters
   *   Associative array of optional request parameters.
   *
   * @return object
   */
  public function getUpsell($upsell_id, $parameters = []) {
    $tokens = [
      'upsell_id' => $upsell_id,
    ];

    return $this->request('GET', '/upsells/{upsell_id}', $tokens, $parameters);
  }

  /**
   * Gets a ThriveCart upsell's pricing options.
   *
   * @param string $upsell_id
   *   The ID of the upsell.
   * @param array $parameters
   *   Associative array of optional request parameters.
   *
   * @return object
   */
  public function getUpsellPricing($upsell_id, $parameters = []) {
    $tokens = [
      'upsell_id' => $upsell_id,
    ];

    return $this->request('GET', '/upsells/{upsell_id}/pricing_options', $tokens, $parameters);
  }

  /**
   * Gets information about all downsells owned by the authenticated account.
   *
   * @param array $parameters
   *   Associative array of optional request parameters.
   *
   * @return array
   */
  public function getDownsells($parameters = []) {
    return $this->request('GET', '/downsells', NULL, $parameters);
  }

  /**
   * Gets a ThriveCart downsell.
   *
   * @param string $downsell_id
   *   The ID of the downsell.
   * @param array $parameters
   *   Associative array of optional request parameters.
   *
   * @return object
   */
  public function getDownsell($downsell_id, $parameters = []) {
    $tokens = [
      'downsell_id' => $downsell_id,
    ];

    return $this->request('GET', '/downsells/{downsell_id}', $tokens, $parameters);
  }

  /**
   * Gets a ThriveCart downsell's pricing options.
   *
   * @param string $downsell_id
   *   The ID of the downsell.
   * @param array $parameters
   *   Associative array of optional request parameters.
   *
   * @return object
   */
  public function getDownsellPricing($downsell_id, $parameters = []) {
    $tokens = [
      'downsell_id' => $downsell_id,
    ];

    return $this->request('GET', '/downsells/{downsell_id}/pricing_options', $tokens, $parameters);
  }

  /**
   * Paginate through transactions.
   *
   * @param array $parameters
   *   Associative array of optional request parameters.
   *   
   *   query: Search query to run (customer email address, order ID, invoice ID, etc)
   *   transactionType: null|'any'|'charge'|'rebill'|'refund'|'cancel'
   *   perPage: Results per page (maximum of 25)
   *   page: 1 through N
   *
   * @return array
   */
  public function transactions($parameters = []) {
    if(isset($parameters['transactionType']) && !empty($parameters['transactionType'])) {
      if(!in_array($parameters['transactionType'], $this->api_config['transactionTypes'])) {
        throw new Exception('Invalid transaction type provided (you provided "'.$parameters['transactionType'].'").');
      }
    }

    if(isset($parameters['perPage'])) {
      if(!is_numeric($parameters['perPage']) || $parameters['perPage'] < 0) {
        throw new Exception('You must provide a valid number for the perPage parameter (you provided "'.$parameters['perPage'].'").');
      }

      if($parameters['perPage'] > 25) {
        throw new Exception('The maximum results per page is 25 (you requested "'.$parameters['perPage'].'").');
      }
    }

    if(isset($parameters['page'])) {
      if(!is_numeric($parameters['page']) || empty($parameters['page'])) {
        throw new Exception('You must provide a valid number for the page parameter (you provided "'.$parameters['page'].'".');
      }
    }

    return $this->request('GET', '/transactions', null, $parameters);
  }

  /**
   * Return all the information stored about a single customer.
   *
   * @param array $parameters
   *   Associative array of request parameters.
   *   
   *   email: Customer email to search for
   *
   * @return object
   */
  public function customer($parameters = []) {
    if(!isset($parameters['email']) || empty($parameters['email'])) {
      throw new Exception('You must provide an email address.');
    }

    if(function_exists('filter_var')) {
      if(!filter_var($parameters['email'], FILTER_VALIDATE_EMAIL)) {
        throw new Exception('You must provide a valid email address (you provided "'.$parameters['email'].'").');
      }
    }

    return $this->request('POST', '/customer', null, $parameters);
  }

  /**
   * Refund a single transaction or rebill.
   *
   * @param array $parameters
   *   Associative array of request parameters.
   *   
   *   order_id: The order ID
   *   reference: The item reference
   *   reason: Optional string explaining the reason for the refund; never shown to the customer
   *
   * @return object
   */
  public function refund($parameters = []) {
    if(!isset($parameters['order_id']) || !is_numeric($parameters['order_id']) || empty($parameters['order_id'])) {
      throw new Exception('You must provide a valid order ID to refund (you provided "'.$parameters['order_id'].'").');
    }

    if(!isset($parameters['reference']) || empty($parameters['reference'])) {
      throw new Exception('You must provide a valid item reference to refund (you provided "'.$parameters['reference'].'").');
    }

    if(isset($parameters['reason']) && strlen($parameters['reason']) > 200) {
      throw new Exception('Your reason for this refund must be shorter than 200 characters (yours was "'.strlen($parameters['reason']).'").');
    }

    return $this->request('POST', '/refund', null, $parameters);
  }

  /**
   * Cancel an active or paused subscription.
   *
   * @param array $parameters
   *   Associative array of request parameters.
   *   
   *   order_id: The order ID
   *   subscription_id: The subscription ID
   *
   * @return object
   */
  public function cancelSubscription($parameters = []) {
    if(!isset($parameters['order_id']) || !is_numeric($parameters['order_id']) || empty($parameters['order_id'])) {
      throw new Exception('You must provide a valid order ID to cancel (you provided "'.$parameters['order_id'].'").');
    }

    if(!isset($parameters['subscription_id']) || !is_numeric($parameters['subscription_id']) || empty($parameters['subscription_id'])) {
      throw new Exception('You must provide a valid subscription ID to cancel (you provided "'.$parameters['subscription_id'].'").');
    }

    return $this->request('POST', '/cancelSubscription', null, $parameters);
  }

  /**
   * Pause an active subscription.
   *
   * @param array $parameters
   *   Associative array of request parameters.
   *   
   *   order_id: The order ID
   *   subscription_id: The subscription ID
   *   auto_resume: (Optional) Unix timestamp of when to automatically resume the subscription; must be at least 24 hours in the future
   *
   * @return object
   */
  public function pauseSubscription($parameters = []) {
    if(!isset($parameters['order_id']) || !is_numeric($parameters['order_id']) || empty($parameters['order_id'])) {
      throw new Exception('You must provide a valid order ID to pause (you provided "'.$parameters['order_id'].'").');
    }

    if(!isset($parameters['subscription_id']) || !is_numeric($parameters['subscription_id']) || empty($parameters['subscription_id'])) {
      throw new Exception('You must provide a valid subscription ID to pause (you provided "'.$parameters['subscription_id'].'").');
    }

    if(isset($parameters['auto_resume'])) {
      if(!is_numeric($parameters['auto_resume'])) {
        throw new Exception('If automatically resume a subscription, you must provide it as a Unix timestamp (you provided "'.$parameters['auto_resume'].'").');
      }

      $now = time();
      if($parameters['auto_resume'] <= $now) {
        throw new Exception('You cannot auto-resume a subscription in the past. Check your timestamp.');
      }

      if(($parameters['auto_resume'] - $now) < 86399) {
        throw new Exception('You cannot auto-resume a subscription within a day from right now - please provide a time further in the future.');
      }
    }

    return $this->request('POST', '/pauseSubscription', null, $parameters);
  }

  /**
   * Resume a paused subscription.
   *
   * @param array $parameters
   *   Associative array of request parameters.
   *   
   *   order_id: The order ID
   *   subscription_id: The subscription ID
   *
   * @return object
   */
  public function resumeSubscription($parameters = []) {
    if(!isset($parameters['order_id']) || !is_numeric($parameters['order_id']) || empty($parameters['order_id'])) {
      throw new Exception('You must provide a valid order ID to resume (you provided "'.$parameters['order_id'].'").');
    }

    if(!isset($parameters['subscription_id']) || !is_numeric($parameters['subscription_id']) || empty($parameters['subscription_id'])) {
      throw new Exception('You must provide a valid subscription ID to resume (you provided "'.$parameters['subscription_id'].'").');
    }

    return $this->request('POST', '/resumeSubscription', null, $parameters);
  }

  /**
   * Paginate through affiliates.
   *
   * @param array $parameters
   *   Associative array of optional request parameters.
   *   
   *   product_id: Product ID
   *   query: Search query to run (affiliate name, email, affiliate ID)
   *   perPage: Results per page (maximum of 25)
   *   page: 1 through N
   *
   * @return array
   */
  public function affiliates($parameters = []) {
    if(isset($parameters['product_id']) && !empty($param['product_id'])) {
      if(!is_numeric($parameters['product_id']) || $parameters['product_id'] <= 0) {
        throw new Exception('You must provide a numeric product ID, or leave this field blank to exclude it.');
      }
    }

    if(isset($parameters['perPage'])) {
      if(!is_numeric($parameters['perPage']) || $parameters['perPage'] < 0) {
        throw new Exception('You must provide a valid number for the perPage parameter (you provided "'.$parameters['perPage'].'").');
      }

      if($parameters['perPage'] > 25) {
        throw new Exception('The maximum results per page is 25 (you requested "'.$parameters['perPage'].'").');
      }
    }

    return $this->request('GET', '/affiliates', null, $parameters);
  }

  /**
   * Return all the information stored about a single affiliate.
   *
   * @param array $parameters
   *   Associative array of request parameters.
   *   
   *   affiliate_id: Affiliate identifier to search for (this can be their numeric user ID, their affiliate_id included in affiliate links etc, or email address)
   *
   * @return object
   */
  public function affiliate($parameters = []) {
    if(!isset($parameters['affiliate_id']) || empty($parameters['affiliate_id'])) {
      throw new Exception('You must provide an affiliate identifier to look up a single affiliate.');
    }

    if(function_exists('filter_var')) {
      if(stripos($parameters['affiliate_id'], '@') !== false && !filter_var($parameters['affiliate_id'], FILTER_VALIDATE_EMAIL)) {
        throw new Exception('You must provide a valid email address, if searching via email address (you provided "'.$parameters['affiliate_id'].'").');
      }
    }

    return $this->request('POST', '/affiliate', null, $parameters);
  }

  /**
   * Create a new affiliate, or add an existing one to your account.
   *
   * @param array $parameters
   *   Associative array of request parameters.
   *    email: The email/username for the affiliate - they will use this to sign in to their account
   *    name: (Optional) Affiliate's name
   *    affiliate_id: Preferred affiliate ID - this may be modified by the system to ensure it is unique
   *    product_id: Numeric product ID to register the affiliate for
   *    auto_approve: Auto-approve this application, regardless of the product settings?
   *    product_ids: Array or JSON-encoded array of numeric product IDs, to add to multiple products at once
   *    parent_affiliate: Numeric user object ID to mark as the referring second-tier affiliate (must exist already, and be set as a second-tier affiliate)
   *    first_name: (Optional) Affiliate's first name - not used in conjunction with the 'name' parameter
   *    last_name: (Optional) Affiliate's last name - not used in conjunction with the 'name' parameter
   *    company: (Optional) Affiliate's company name
   *    country: (Optional) 2-digit country code for the affiliate's country
   *    city: (Optional) Affiliate's city
   *    state: (Optional) Affiliate's state/region
   *    zip: (Optional) Affiliate's ZIP/post code
   *    trigger_emails: Trigger emails to the affiliate and/or vendor?
   *
   * @return object
   */
  public function createAffiliate($parameters = []) {
    if(!isset($parameters['product_id']) || !is_numeric($parameters['product_id'])) {
      throw new Exception('You must provide a valid product ID to create an affiliate (you provided "'.$parameters['product_id'].'").');
    }

    if(isset($parameters['product_ids'])) {
      if(is_array($parameters['product_ids'])) {
        $parameters['product_ids'] = json_encode($parameters['product_ids']);
      }
    }

    return $this->request('POST', '/affiliates', null, $parameters);
  }

  /**
   * Mark an affiliate as a favorite/VIP affiliate.
   *
   * @param string $affiliate_id
   *   The numeric affiliate user ID, the affiliate email address, or the affiliate's affiliate ID.
   * @param array $parameters
   *   Associative array of request parameters.
   *
   * @return object
   */
  public function favoriteAffiliate($affiliate_id, $parameters = []) {
    $tokens = [
      'affiliate_id' => $affiliate_id,
    ];

    return $this->request('POST', '/affiliates/{affiliate_id}/favorite', $tokens, $parameters);
  }

  /**
   * Remove the favorite/VIP marker from an affiliate.
   *
   * @param string $affiliate_id
   *   The numeric affiliate user ID, the affiliate email address, or the affiliate's affiliate ID.
   * @param array $parameters
   *   Associative array of request parameters.
   *
   * @return object
   */
  public function unfavoriteAffiliate($affiliate_id, $parameters = []) {
    $tokens = [
      'affiliate_id' => $affiliate_id,
    ];

    return $this->request('POST', '/affiliates/{affiliate_id}/unfavorite', $tokens, $parameters);
  }

  /**
   * Register an existing affiliate for a product, or list of products.
   *
   * @param string $affiliate_id
   *   The numeric affiliate user ID, the affiliate email address, or the affiliate's affiliate ID.
   * @param array $parameters
   *   Associative array of request parameters.
   *    product_ids: JSON array of numeric product IDs to register for (which must have affiliates turned on in their settings)
   *    auto_approve: Auto-approve this application, regardless of the product settings?
   *    trigger_emails: Trigger emails to the affiliate and/or vendor?
   *    parent_affiliate: Numeric ID of the parent, referring affiliate (who must already be marked as a second tier affiliate for all products in the list)
   *
   * @return object
   */
  public function registerAffiliate($affiliate_id, $parameters = []) {
    $tokens = [
      'affiliate_id' => $affiliate_id,
    ];

    if(isset($parameters['product_ids'])) {
      if(is_array($parameters['product_ids'])) {
        $parameters['product_ids'] = json_encode($parameters['product_ids']);
      }
    }

    return $this->request('POST', '/affiliates/{affiliate_id}/register', $tokens, $parameters);
  }

  /**
   * Approve an affiliate for a product, or list of products. They must have a pending approval already for all provided products.
   *
   * @param string $affiliate_id
   *   The numeric affiliate user ID, the affiliate email address, or the affiliate's affiliate ID.
   * @param array $parameters
   *   Associative array of request parameters.
   *    product_ids: JSON array of numeric product IDs to approve for
   *    trigger_emails: Trigger emails to the affiliate and/or vendor?
   *
   * @return object
   */
  public function approveAffiliate($affiliate_id, $parameters = []) {
    $tokens = [
      'affiliate_id' => $affiliate_id,
    ];

    if(isset($parameters['product_ids'])) {
      if(is_array($parameters['product_ids'])) {
        $parameters['product_ids'] = json_encode($parameters['product_ids']);
      }
    }

    return $this->request('POST', '/affiliates/{affiliate_id}/approve', $tokens, $parameters);
  }

  /**
   * Reject an affiliate for a product, or list of products.
   *
   * @param string $affiliate_id
   *   The numeric affiliate user ID, the affiliate email address, or the affiliate's affiliate ID.
   * @param array $parameters
   *   Associative array of request parameters.
   *    product_ids: JSON array of numeric product IDs to reject their application(s) for
   *    trigger_emails: Trigger emails to the affiliate and/or vendor?
   *
   * @return object
   */
  public function rejectAffiliate($affiliate_id, $parameters = []) {
    $tokens = [
      'affiliate_id' => $affiliate_id,
    ];

    if(isset($parameters['product_ids'])) {
      if(is_array($parameters['product_ids'])) {
        $parameters['product_ids'] = json_encode($parameters['product_ids']);
      }
    }

    return $this->request('POST', '/affiliates/{affiliate_id}/reject', $tokens, $parameters);
  }

  /**
   * Specify custom commissions for an affiliate for a particular product.
   *
   * @param string $affiliate_id
   *   The numeric affiliate user ID, the affiliate email address, or the affiliate's affiliate ID.
   * @param array $parameters
   *   Associative array of request parameters.
   *    product_id: Numeric product ID to apply custom commissions to.
   *    commission_object: JSON object describing the custom commission settings to apply.
   *
   * @return object
   */
  public function setCustomCommissions($affiliate_id, $parameters = []) {
    $tokens = [
      'affiliate_id' => $affiliate_id,
    ];

    if(!isset($parameters['product_id']) || !is_numeric($parameters['product_id'])) {
      throw new Exception('You must provide a valid product ID to set custom commissions (you provided "'.$parameters['product_id'].'").');
    }

    if(isset($parameters['commission_object'])) {
      if(is_array($parameters['commission_object']) || is_object($parameters['commission_object'])) {
        $parameters['commission_object'] = json_encode($parameters['commission_object']);
      }
    }

    return $this->request('POST', '/affiliates/{affiliate_id}/custom_commissions', $tokens, $parameters);
  }

  /**
   * Delete an affiliate from your account.
   *
   * @param string $affiliate_id
   *   The numeric affiliate user ID, the affiliate email address, or the affiliate's affiliate ID.
   *
   * @return object
   */
  public function deleteAffiliate($affiliate_id, $parameters = []) {
    $tokens = [
      'affiliate_id' => $affiliate_id,
    ];

    return $this->request('POST', '/affiliates/{affiliate_id}/delete', $tokens, $parameters);
  }

  /**
   * Create a new event subscription.
   *
   * @param string $event
   *   Either `*` for all events, or a particular event name (see documentation for list of potential events)
   *
   * @param string $target_url
   *   Your URL to receive matching events (must be registered to your app if using OAuth)
   *
   * @param array $trigger_fields
   *   Associative array of trigger_fields (see documentation for trigger fields)
   *
   * @return object
   */
  public function createEventSubscription($event, $target_url, $trigger_fields = []) {
    if(empty($event)) {
      throw new Exception('You must provide a valid event name to create an event subscription.');
    }

    if(empty($target_url)) {
      throw new Exception('You must provide a target URL to create an event subscription.');
    }

    if(function_exists('filter_var')) {
      if(!filter_var($target_url, FILTER_VALIDATE_URL)) {
        throw new Exception('You must provide a valid target URL to create an event subscription.');
      }
    }

    $blob = array(
      'event' => $event,
      'target_url' => $target_url,
      'trigger_fields' => $trigger_fields,
    );

    return $this->request('POST', '/subscribe', null, $blob);
  }

  /**
   * Cancels an event subscription.
   *
   * @param string $target_url
   *   Your URL to receive matching events (must have previously been registered as an event subscription endpoint)
   *
   * @return object
   */
  public function cancelEventSubscription($target_url) {
    if(empty($target_url)) {
      throw new Exception('You must provide a target URL to cancel an event subscription.');
    }

    if(function_exists('filter_var')) {
      if(!filter_var($target_url, FILTER_VALIDATE_URL)) {
        throw new Exception('You must provide a valid target URL to cancel an event subscription.');
      }
    }

    $blob = array(
      'target_url' => $target_url,
    );

    return $this->request('POST', '/unsubscribe', null, $blob);
  }
}
