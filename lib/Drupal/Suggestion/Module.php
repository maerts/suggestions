<?php

class Drupal_Suggestion_Module implements Drupal_Suggestion_Resolvable {

  protected $name;
  protected $identifier;
  protected $url;

  protected $available;
  protected $version;
  protected $version_incompatible;

  protected $enable = TRUE;

  public function __construct($identifier, $name, $url = NULL) {
    $this->identifier = $identifier;
    $this->name = $name;
    $this->url = $url;
    $this->enable = TRUE;
    $this->version = NULL;
  }

  public function disable() {
    $this->enable = FALSE;

    return $this;
  }

  public function version($version) {
    $this->version = $version;

    return $this;
  }

  public function assert() {
    $files = system_rebuild_module_data();
    $this->available = array_key_exists($this->identifier, $files);
    if (!$this->available) {
      return $this->enable;
    }
    else {
      if ($this->enable && $this->version) {
        $file = $files[$this->identifier];
        $current_version = str_replace(DRUPAL_CORE_COMPATIBILITY . '-', '', $file->info['version']);

        $dependency_info = drupal_parse_dependency("{$this->identifier} ({$this->version})");

        $this->version_incompatible = drupal_check_incompatibility($dependency_info, $current_version);
        if ($this->version_incompatible) {
          return TRUE;
        }
      }

      return $files[$this->identifier]->status != $this->enable;
    }
  }

  public function message() {
    if ($this->url) {
      $params = array('!module_name' => l($this->name, $this->url));
    }
    else {
      $params = array('!module_name' => check_plain($this->name));
    }

    if ($this->enable) {
      if (!$this->available) {
        if ($this->version) {
          $params['@version'] = $this->version;
          return t('Download and enable the !module_name module (version @version).', $params);
        }
        else {
          return t('Download and enable the !module_name module.', $params);
        }
      }
      else if ($this->version_incompatible) {
        $params['@version'] = $this->version;
        return t('Download version @version of the !module_name module.', $params);
      }
      else {
        return t('Enable the !module_name module.', $params);
      }
    }
    else {
      return t('Disable the !module_name module', $params);
    }
  }

  public function isResolvable() {
    return (bool)$this->available;
  }

  public function resolve() {
    $module_list = array($this->identifier);
    if ($this->enable) {
      module_enable($module_list, TRUE);
    }
    else {
      module_disable($module_list, TRUE);
    }
  }
}