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

  const VERSION = '1.0.2';

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
   * The REST API endpoint.
   *
   * @var string $endpoint
   */
  protected $endpoint = 'https://thrivecart.com/api/external';

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
        'Authorization' => 'Bearer '.$this->access_token
      ],
    ];

    return $this->client->handleRequest($method, $this->endpoint . $path, $options, $parameters, true);
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
}
