<?php
/**
 * @file
 */

/**
 * Callback for the profiles configuration form.
 */
function suggestions_profiles_form($form, &$form_state) {
  $profiles = module_invoke_all('suggestions_profile_info');


  $profiles_options = array();
  foreach ($profiles as $identifier => $profile) {
    $profiles_options[$identifier] = array('title' => $profile['title'], 'description' => $profile['description']);
  }

  $form['introduction'] = array(
    '#type' => 'html_tag',
    '#value' => t('Please mark the suggestion profiles you want to enable.'),
    '#tag' => 'p',
  );

  $form['suggestions_profiles'] = array(
    '#title' => t('Profiles'),
    '#type' => 'tableselect',
    '#header' => array('title' => t('Title'), 'description' => t('Description')),
    '#empty' => t('There are no suggestion profiles available.'),
    '#description' => t('Mark the suggestion profiles you want to enable.'),
    '#options' => $profiles_options,
    '#default_value' => variable_get_value('suggestions_profiles'),
  );

  return system_settings_form($form);
}

/**
 * Callback for the suggestions form.
 */
function suggestions_suggestions_form($form, &$form_state) {
  $profiles = suggestions_enabled_profiles();

  $form['decisions'] = array(
    '#tree' => TRUE,
  );

  foreach ($profiles as $profile_id => $profile) {
    $suggestions = suggestions_profile_current_suggestions($profile);

    if (empty($suggestions)) {
      continue;
    }

    $form['decisions'][$profile_id] = array(
      '#title' => $profile['title'],
      '#description' => $profile['description'],
      '#type' => 'fieldset',
    );

    foreach ($suggestions as $suggestion_id => $suggestion) {
      $form['decisions'][$profile_id][$suggestion_id] = array();

      // Store the suggestion in the form so it does not need to be reloaded
      // and re-asserted on form submit.
      $form['decisions'][$profile_id][$suggestion_id]['suggestion'] = array(
        '#type' => 'value',
        '#value' => $suggestion,
      );

      $form['decisions'][$profile_id][$suggestion_id]['summary'] = array(
        '#type' => 'markup',
        '#markup' => $suggestion->message(),
      );

      $decision_options = array(
        1 => t("Skip"),
        2 => t("Skip and don't ask me again"),
      );

      if ($suggestion instanceof Drupal_Suggestion_Resolvable && $suggestion->isResolvable()) {
        $decision_options[3] = t('OK, fix it for me');
      }

      $form['decisions'][$profile_id][$suggestion_id]['decision'] = array(
        '#title' => t('Decision'),
        '#type' => 'radios',
        '#options' => $decision_options,
        '#default_value' => 1,
      );
    }
  }

  $form['submit'] = array(
    '#type' => 'submit',
    '#value' => t('Submit'),
  );

  return $form;
}

/**
 * Submit callback for the suggestions form.
 */
function suggestions_suggestions_form_submit($form, &$form_state) {
  $batch = array(
    'title' => t('Processing suggestions'),
    'operations' => array(),
    'finished' => 'suggestions_processed',
    'file' => drupal_get_path('module', 'suggestions') . '/suggestions.pages.inc',
  );

  $decision_callbacks = array(
    2 => 'suggestions_ignore',
    3 => 'suggestions_resolve',
  );

  foreach ($form_state['values']['decisions'] as $profile_id => $suggestion_decisions) {
    foreach ($suggestion_decisions as $suggestion_id => $suggestion_decision) {
      $suggestion = $suggestion_decision['suggestion'];
      $decision = (int) $suggestion_decision['decision'];

      // Decision: skip.
      if (!array_key_exists($decision, $decision_callbacks)) {
        continue;
      }

      $decision_callback = $decision_callbacks[$decision];

      $batch_params = array($profile_id, $suggestion_id, $suggestion);
      $batch['operations'][] = array($decision_callback, $batch_params);
    }
  }

  batch_set($batch);
}

/**
 * Batch operation callback to ignore a suggestion.
 */
function suggestions_ignore($profile_id, $suggestion_id, $suggestion, &$context) {
  $ignored_suggestions = variable_get_value("suggestions_${profile_id}_ignore");
  if (!in_array($suggestion_id, $ignored_suggestions)) {
    $ignored_suggestions[] = $suggestion_id;
    variable_set("suggestions_${profile_id}_ignore", $ignored_suggestions);
  }
}

/**
 * Batch operation callback to resolve an auto-resolvable suggestion.
 *
 * @todo consider passing $context to the resolve() implementation
 */
function suggestions_resolve($profile_id, $suggestion_id, $suggestion, &$context) {
  $suggestion->resolve();

  $context['message'] = '';
}


/**
 * Finish callback for the batch process that resolves suggestions.
 */
function suggestions_processed($success, $results, $operations) {

}