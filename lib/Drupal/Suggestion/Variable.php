<?php

class Drupal_Suggestion_Variable implements Drupal_Suggestion_Resolvable {

  protected $name;
  protected $value;
  protected $default;
  protected $in_settings_file;
  protected $strict;

  public function __construct($name, $value, $default = NULL) {
    $this->name = $name;
    $this->value = $value;
    $this->default = $default;
    $this->strict = FALSE;

    $this->in_settings_file = FALSE;
  }

  public function strict($strict = TRUE) {
    $this->strict = $strict;
  }

  public function inSettingsFile() {
    $this->in_settings_file = TRUE;

    return $this;
  }

  public function assert() {
    $value = variable_get($this->name, $this->default);
    if ($this->strict) {
      return $value !== $this->value;
    }
    else {
      return $value != $this->value;
    }
  }

  /**
   * @todo take into account array variables when printing the message
   */
  public function message() {
    $current_value = variable_get($this->name, $this->default);

    $current_value_string = $this->value_string($current_value);
    $value_string = $this->value_string($this->value);

    $msg = t('The variable "@var" is currently set to @current_value. It is recommended to replace this with @value.', array(
      '@var' => $this->name,
    	'@current_value' => $current_value_string,
      '@value' => $value_string,
    ));

    if ($this->in_settings_file) {
      $msg .= ' ' . t('This needs to be configured in the settings.php file as this variable is required in an early phase of Drupal\'s bootstrap.');
    }

    return $msg;
  }

  protected function value_string($value) {
    if (is_bool($value)) {
      $value_string = $value ? 'TRUE' : 'FALSE';
    }
    else if (is_int($value)) {
      $value_string = $value;
    }
    else {
      $value_string = '"' . $value . '"';
    }

    return $value_string;
  }

  public function isResolvable() {
    return !$this->in_settings_file;
  }

  public function resolve() {
    variable_set($this->name, $this->value);
  }
}