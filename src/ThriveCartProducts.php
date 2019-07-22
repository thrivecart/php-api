<?php

namespace ThriveCart;

/**
 * ThriveCart Products library.
 *
 * @package ThriveCart
 */
class ThriveCartLists extends ThriveCart {

  /**
   * Gets information about all products owned by the authenticated account.
   *
   * @param array $parameters
   *   Associative array of optional request parameters.
   *
   * @return array
   */
  public function getLists($parameters = []) {
    return $this->request('GET', '/products', NULL, $parameters);
  }

  /**
   * Gets a ThriveCart list.
   *
   * @param string $list_id
   *   The ID of the list.
   * @param array $parameters
   *   Associative array of optional request parameters.
   *
   * @return object
   */
  public function getList($list_id, $parameters = []) {
    $tokens = [
      'product_id' => $product_id,
    ];

    return $this->request('GET', '/products/{product_id}', $tokens, $parameters);
  }
}
