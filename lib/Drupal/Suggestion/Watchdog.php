<?php

class Drupal_Suggestion_Watchdog implements Drupal_Suggestion {
  
  protected $type;
  protected $timespan;
  protected $url;
  protected $maximum_count;
  protected $results_found;
  
  public function __construct($type, $timespan, $maximum_count, $url = NULL) {
    $this->type = $type;
    $this->maximum_count = $maximum_count;
    $this->timespan = $timespan;
    $this->url = $url;
  }
  
  public function assert() {
    $results = db_query("SELECT count(uid) FROM {watchdog} WHERE type = :type
      		     AND timestamp <= :date", array(':type' => $this->type, ':date' => strtotime($this->timespan)));
    $count = $results->fetchField();
    if ($count > $this->maximum_count) {
      $this->results_found = $count;
      return true;
    }
    return false;
  }
  
  public function message() {
    $string = t('value "'. $this->type . '" has been found ' . $this->results_found . ' times in Watchdog. ');
      if ($this->url) {
        global $base_url;
        $string .= t('This can fixed at the following ' . l('url', $base_url . '/' . $this->url) . '.');
      }
    return $string; 
  }
}