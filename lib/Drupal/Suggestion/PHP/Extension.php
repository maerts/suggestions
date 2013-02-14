<?php
/**
 * @file
 */

class Drupal_Suggestion_PHP_Extension implements Drupal_Suggestion {

  /**
   * Name of the extension
   * @var string
   */
  protected $name;

  public function __construct($name) {
    $this->name = $name;
  }

  public function assert() {
    return !extension_loaded($this->name);
  }

  public function message() {
    return t('You should enable the @name PHP extension.', array('@name' => $this->name));
  }
}
