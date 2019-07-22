<?php

namespace ThriveCart\http;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

/**
 * An HTTP client for use with the ThriveCart API using Guzzle.
 *
 * @package ThriveCart
 */
class ThriveCartGuzzleHttpClient implements ThriveCartHttpClientInterface {

  /**
   * The GuzzleHttp client.
   *
   * @var Client $client
   */
  private $guzzle;

  /**
   * ThriveCartGuzzleHttpClient constructor.
   *
   * @param array $config
   *   Guzzle HTTP configuration options.
   *   Currently supports only 'timeout'.
   */
  public function __construct($config = []) {
    $this->guzzle = new Client($config);
  }

  /**
   * @inheritdoc
   */
  public function handleRequest($method, $uri = '', $options = [], $parameters = [], $returnAssoc = FALSE) {
    if (!empty($parameters)) {
      if ($method == 'GET') {
        // Send parameters as query string parameters.
        $options['query'] = $parameters;
      }
      else {
        // Send parameters as JSON in request body.
        $options['json'] = (object) $parameters;
      }
    }

    try {
      $response = $this->guzzle->request($method, $uri, $options);
      $data = json_decode($response->getBody(), $returnAssoc);

      return $data;
    }
    catch (RequestException $e) {
      $response = $e->getResponse();
      if (!empty($response)) {
        $message = $e->getResponse()->getBody();
      }
      else {
        $message = $e->getMessage();
      }

      throw new \ThriveCart\Exception($message, $e->getCode(), $e);
    }
  }

}
