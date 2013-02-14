<?php

/**
 * Interface for Drupal site configuration suggestions which can be solved automatically.
 */
interface Drupal_Suggestion_Resolvable extends Drupal_Suggestion {

  /**
   * Some suggestions can be automatically resolved only under certain conditions.
   *
   * @return bool
   */
  public function isResolvable();

  public function resolve();

}