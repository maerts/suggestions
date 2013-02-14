<?php

class Drupal_Suggestion_Image_Style implements Drupal_Suggestion_Resolvable {

  protected $style;
  protected $style_options;

  public function __construct($style, $style_options) {
    $this->style = $style;
    $this->style_options = $style_options;
  }

  public function assert() {
    return $this->style_options['storage'] == IMAGE_STORAGE_NORMAL
      || $this->style_options['storage'] == IMAGE_STORAGE_OVERRIDE;
  }

  public function message() {
    if ($this->style_options['storage'] == IMAGE_STORAGE_NORMAL) {
      return t('The image style "%style" has been saved in the database. Consider moving it to code.', array(
        '%style' => $this->style_options['name'],
      ));
    }
    elseif ($this->style_options['storage'] == IMAGE_STORAGE_OVERRIDE) {
      return t('The image style "%style" has been overridden. Revert changes?', array(
        '%style' => $this->style_options['name'],
      ));
    }
  }

  public function isResolvable() {
    return $this->style_options['storage'] == IMAGE_STORAGE_OVERRIDE;
  }

  public function resolve() {
    image_default_style_revert($this->style_options);
  }
}