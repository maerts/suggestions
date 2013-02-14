<?php

class Drupal_Suggestion_Node_Options implements Drupal_Suggestion_Resolvable {

  protected $node_type;
  protected $node_type_name;
  protected $node_options;
  protected $changes_needed;

  public function __construct($node_type, $node_type_name, $node_options = array()) {
    $this->node_type = $node_type;
    $this->node_type_name = $node_type_name;
    $this->node_options = $node_options;
    $this->changes_needed = array();
  }

  protected function getOptionName($option) {
    $option_names = array(
      'status' => t('Published'),
      'promoted' => t('Promoted to front page'),
      'sticky' => t('Sticky at top of lists'),
      'revision' => t('Create new revision'),
    );
    if (isset($option_names[$option])) {
      return $option_names[$option];
    }
    else {
      return $option;
    }
  }

  public function assert() {
    $current_options = variable_get("node_options_$this->node_type", array());
    $this->changes_needed = array();

    foreach ($this->node_options as $option => $value) {
      if ($value && !in_array($option, $current_options)) {
          $this->changes_needed[] = t('Enable %option', array('%option' => $this->getOptionName($option)));
      }
      elseif (!$value && in_array($option, $current_options)) {
          $this->changes_needed[] = t('Disable %option', array('%option' => $this->getOptionName($option)));
      }
    }
    return (bool) $this->changes_needed;
  }

  public function message() {
    return theme('item_list', array(
      'items' => $this->changes_needed,
      'title' => t('Suggested changes to content type "%type":', array('%type' => $this->node_type_name)),
    ));
  }

  public function isResolvable() {
    return TRUE;
  }

  public function resolve() {
    $current_options = variable_get("node_options_$this->node_type", array());
    foreach ($this->node_options as $option => $value) {
      if ($value && !in_array($option, $current_options)) {
        array_push($current_options, $option);
      }
      elseif (!$value && in_array($option, $current_options)) {
        unset($current_options[array_search($option, $current_options)]);
      }
    }
    variable_set("node_options_$this->node_type", $current_options);
  }
}