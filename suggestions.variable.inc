<?php
/**
 * @file
 */

/**
 * Implements hook_variable_info().
 */
function suggestions_variable_info($options) {
  $variable['suggestions_profiles'] = array(
    'title' => t('Suggestion profiles the user has enabled.'),
    'type' => 'array',
    'group' => 'suggestions',
    'default' => array(),
  );

  $variable['suggestions_check_in_admin'] = array(
    'title' => t('Check for suggestions in the administration interface.'),
    'type' => 'boolean',
    'group' => 'suggestions',
    'default' => TRUE,
  );

  $variable['suggestions_[suggestions_profile]_ignore'] = array(
    'type' => 'multiple',
    'multiple' => 'suggestions_profile',
    'title' => t('A list of suggestions the user has ignored.', array(), $options),
    'repeat' => array(
     'type' => 'array',
     'default' => array(),
    ),
    'group' => 'suggestions',
  );

  return $variable;
}

/**
 * Implements hook_variable_type_info().
 */
function suggestions_variable_type_info() {
  $type['suggestions_profile'] = array(
    'title' => t('Suggestions Profiles'),
    'type' => 'select',
    'options callback' => 'suggestions_available_profiles',
    'cache' => TRUE,
  );
  return $type;
}
