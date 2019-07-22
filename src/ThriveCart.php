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
class ThriveCart {

  const VERSION = '1.0.0';

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
  // protected $endpoint = 'https://thrivecart.com/api/external';
  protected $endpoint = 'http://dev-thrivecart.com/api/external';

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
   * @param bool $batch
   *   TRUE if this request should be added to pending batch operations.
   * @param bool $returnAssoc
   *   TRUE to return ThriveCart API response as an associative array.
   *
   * @return mixed
   *   Object or Array if $returnAssoc is TRUE.
   *
   * @throws ThriveCartAPIException
   */
  public function request($method, $path, $tokens = NULL, $parameters = NULL, $returnAssoc = FALSE) {
    if (!empty($tokens)) {
      foreach ($tokens as $key => $value) {
        $path = str_replace('{' . $key . '}', $value, $path);
      }
    }

    // Set default request options with auth header.
    $options = [
      'headers' => [
        'Authorization' => $this->access_token
      ],
    ];

    return $this->client->handleRequest($method, $this->endpoint . $path, $options, $parameters, $returnAssoc);
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

}
