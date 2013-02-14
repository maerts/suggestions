<?php

/**
 * Interface for Drupal site configuration suggestions.
 */
interface Drupal_Suggestion {

  /**
   * Asserts if the suggestion needs to be shown
   */
  public function assert();

  /**
   * Display a user friendly message suggesting a change
   */
  public function message();

}