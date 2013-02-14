<?php

ctools_include('export');

class Drupal_Suggestion_View implements Drupal_Suggestion_Resolvable {

  protected $view;
  protected $name;
  protected $in_code;
  protected $in_database;

  public function __construct($view) {
    $this->view = $view;
    $this->name = $this->view->human_name? $this->view->human_name: $this->view->name;
  }

  public function assert() {
    // check the bitflags using a proper &.
    $this->in_code = !!(EXPORT_IN_CODE & $this->view->export_type);
    $this->in_database = !!(EXPORT_IN_DATABASE & $this->view->export_type);

    // If the view is present in the database we should warn the user.
    return $this->in_database;
  }

  public function message() {
    if (!$this->in_code) {
      $message = 'The view "%view" has been saved in the database. Consider moving it to code.';
    }
    else {
      $message = 'The view "%view" has been overriden in the database. Do you want to revert to the version in code?';
    }
    return t($message, array('%view' => $this->name));
  }

  public function isResolvable() {
    // We can only resolve warnings if the view is (also) saved in code.
    return $this->in_code;
  }

  public function resolve() {
    ctools_export_crud_delete($this->view->table, $this->view);
  }
}
