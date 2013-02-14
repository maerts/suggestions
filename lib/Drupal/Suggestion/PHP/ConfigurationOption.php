<?php
/**
 * @file
 */

class Drupal_Suggestion_PHP_ConfigurationOption implements Drupal_Suggestion {

  protected $option;
  protected $expected_value;
  protected $current_value;

  public function __construct($option, $expected_value) {
    $this->option = $option;
    $this->expected_value = $expected_value;
  }

  public function assert() {
    $this->current_value = ini_get($this->option);
    return $this->expected_value != $this->current_value;
  }

  public function message() {
    $args = array(
    	'@option' => $this->option,
    	'@current_value' => $this->current_value,
    	'@expected_value' => $this->expected_value,
    );
    return t('The PHP configuration option "@option" is currently set to "@current_value". It is recommended to replace this with "@expected_value".', $args);
  }
}