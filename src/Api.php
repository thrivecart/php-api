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

  const VERSION = '1.0.5';

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
   * API version.
   *
   * @var string $version
   */
  public $version = self::VERSION;

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
   * @param array $parameters
   *   Associative array of optional request parameters.
   *
   * @return array
   */
  public function getAccount($parameters = []) {
    return $this->request('GET', '/', NULL, $parameters);
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
      'timeout' => 10,
    ];

    $client = new ThriveCartGuzzleHttpClient($http_options);

    return $client;
  }

  /**
   * Gets info about the connected account
   *
   * @return object
   */
  public function ping($parameters = []) {
    return $this->request('GET', '/ping', NULL, $parameters);
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
   * Paginate through transactions
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

    return $this->request('GET', '/transactions', null, $parameters);
  }

  /**
   * Return all the information stored about a single customer
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
   * Refund a single transaction or rebill
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
   * Cancel an active or paused subscription
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
   * Pause an active subscription
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
   * Resume a paused subscription
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
}
