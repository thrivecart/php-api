<?php

namespace ThriveCart;

use \Exception;

/**
 * Custom ThriveCart API exception.
 *
 * @package ThriveCart
 */
class ThriveCartAPIException extends Exception {

  /**
   * @inheritdoc
   */
  public function __construct($message = "", $code = 0, Exception $previous = NULL) {
    // Construct message from JSON if required.
    if (substr($message, 0, 1) == '{') {
      $message_obj = json_decode($message);
      $message = '['.$message_obj->error.'] '.$message_obj->reason;
    }

    parent::__construct($message, $code, $previous);
  }

}
