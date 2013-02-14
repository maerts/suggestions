<?php

class Drupal_Suggestion_Permission implements Drupal_Suggestion_Resolvable {

  protected $permission;
  protected $role;

  public function __construct($role, $permission) {
    $this->role = user_role_load_by_name($role);;
    $this->permission = $permission;
  }

  public function assert() {
    $permissions = user_role_permissions(array($this->role->rid => $this->role->name));

    return !isset($permissions[$this->role->rid]) || !array_key_exists($this->permission, $permissions[$this->role->rid]);
  }

  /**
   * @todo use the human friendly name of the permission here
   */
  public function message() {
    $all_permissions = module_invoke_all('permission');
    $permission = $all_permissions[$this->permission];

    return t('The "@role" role currently does not have the permission "!permission".', array('@role' => $this->role->name, '!permission' => $permission['title']));
  }

  public function isResolvable() {
    return TRUE;
  }

  public function resolve() {
    user_role_grant_permissions($this->role->rid, array($this->permission));
  }
}