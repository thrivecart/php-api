<?php

namespace ThriveCart\http;

/**
 * Interface for all HTTP clients used with the ThriveCart library.
 *
 * @package ThriveCart
 */
interface ThriveCartHttpClientInterface {

  /**
   * Makes a request to the ThriveCart API.
   *
   * @param string $method
   *   The REST method to use when making the request.
   * @param string $uri
   *   The API URI to request.
   * @param array $options
   *   Request options. @see ThriveCart::request().
   * @param array $parameters
   *   Associative array of parameters to send in the request body.
   * @param bool $returnAssoc
   *   TRUE to return ThriveCart API response as an associative array.
   *
   * @return object
   *
   * @throws \Exception
   */
  public function handleRequest($method, $uri = '', $options = [], $parameters = [], $returnAssoc = FALSE);

}
