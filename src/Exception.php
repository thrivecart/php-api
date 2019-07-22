<?php

namespace ThriveCart;

/**
 * Custom ThriveCart API exception.
 *
 * @package ThriveCart
 */
class Exception extends \Exception {

  /**
   * @inheritdoc
   */
  public function __construct($message = "", $code = 0, \Exception $previous = NULL) {
    // Construct message from JSON if required.
    if (substr($message, 0, 1) == '{') {
      $message_obj = json_decode($message);

      if(isset($message_obj->reason)) {
        $message = '['.$message_obj->error.'] '.$message_obj->reason;
      } else {
        $message = '['.$message_obj->error.']';
      }
    }

    parent::__construct($message, $code, $previous);
  }

}
