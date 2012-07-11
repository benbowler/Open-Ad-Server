<?php
/**
 * @file
 * Contains the administrative functions of the orbitscriptsads module.
 *
 * This file is included by the core orbitscriptsads module, and includes the
 * settings form.
 */

include_once(drupal_get_path('module', 'orbitscriptsads') .'/orbitscriptsads_api.php');

/**
 * Menu callback for the orbitscriptsads module settings form.
 *
 * @ingroup forms
 */
function orbitscriptsads_main_settings() {
  include_once(drupal_get_path('module', 'orbitscriptsads') .'/help/orbitscriptsads.help.inc');

  $form['help'] = array(
    '#type'        => 'fieldset',
    '#collapsible' => TRUE,
    '#collapsed'   => TRUE,
    '#title'       => t('Help and instructions'),
  );

  $form['help']['help'] = array(
    '#type'  => 'markup',
    '#value' => orbitscriptsads_help_text(),
  );

  $form['basic'] = array(
    '#type' => 'fieldset',
    '#collapsible' => FALSE,
    '#collapsed' => FALSE,
    '#title' => t('Basic Settings'),
  );

  $form['basic']['orbitscriptsads_api_key'] = array(
    '#type'          => 'textfield',
    '#title'         => t('Your API key'),
    '#required'      => TRUE,
    '#default_value' => variable_get('orbitscriptsads_api_key', ORBITSCRIPTSADS_API_KEY_DEFAULT),
    '#description'   => t('To access the API you need a key. You can get it on %site.<br /> For example: %id', array('%site'=>ORBITSCRIPTSADS_BASE_URL_DEFAULT, '%id' => 'bae394fdabc5966323faaa2a3adf516c')),
  );
  
  $form['basic']['orbitscriptsads_base_url'] = array(
    '#type'          => 'textfield',
    '#title'         => t('API Host'),
    '#required'      => TRUE,
    '#default_value' => variable_get('orbitscriptsads_base_url', ORBITSCRIPTSADS_BASE_URL_DEFAULT),
    '#description'   => t('Base URL of %site_name. For example: %url', array('%url' => 'http://example.com','%site_name' => ORBITSCRIPTSADS_SITE_NAME)),
  );
  
  $form['basic']['orbitscriptsads_site_id'] = array(
    '#type'          => 'textfield',
    '#title'         => t('Site ID'),
    '#required'      => FALSE,
    '#default_value' => variable_get('orbitscriptsads_site_id', ORBITSCRIPTSADS_SITE_ID_DEFAULT),
    '#description'   => t('If you don\'t want to see all channels your specify site id. For example: %site_id', array('%site_id' => '14')),
  );
  
  /*
  	if (($errors['code'] === '0')) { ?>
	
				<p style="padding: .5em; background-color: #2d2; color: #fff; font-weight:bold;"><?php  _e('All is OK :)'); ?></p>				

		  	<?php } else { ?>
				
				<p style="padding: .5em; background-color: #d22; color: #fff; font-weight:bold;"><?php _e('Unable to load API data. The following error was get:'); echo '<br /><b>'.$errors['code'].' - '.$errors['msg'].'</b>';?></p>
		  	
		  	<?php } ?>
  
  */
  

  return system_settings_form($form);
}

/**
 * Search for the available Publisher ID modules
 *
 * @return
 *   array of selectable Publisher ID functions
 */
function _orbitscriptsads_id_settings_client_id_mods() {
  $ret['orbitscriptsads_basic'] = 'Use the site Publisher ID always';

  $funcs = get_defined_functions();
  foreach ($funcs['user'] as $func) {
    if (preg_match('!_orbitscriptsads$!', $func)) {
      $settings = $func('settings');
      $ret[$func] = $settings['name'];
      if (!empty($settings['desc'])) {
        $ret[$func] .= "<div style='margin-left: 2.5em;' class='description'>{$settings['desc']}</div>";
      }
    }
  }
  return $ret;
}