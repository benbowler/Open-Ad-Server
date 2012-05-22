<?php
/**
 * @file
 * Contains the administrative functions of the orbitscriptsads_managed_managed module.
 *
 * This file is included by the orbitscriptsads_managed_managed module, and includes the
 * settings form.
 */
include_once(drupal_get_path('module', 'orbitscriptsads') .'/orbitscriptsads_api.php');

/**
 * Menu callback for the orbitscriptsads_managed_managed module settings form.
 *
 * @ingroup forms
 */
 
/**
 * Description form orbitscriptsads_managed_form_table
 */
function orbitscriptsads_managed_form_table()
{
	$form['submit'] = array(
        '#type' => 'submit',
        '#value' => 'Delete',
    );
    // описание заголовков
    $form['header'] = array(
        '#type' => 'value',
        '#value' => array(
            theme('table_select_header_cell'),
            array('data' => 'Title'),
            array('data' => 'Region'),
            array('data' => 'Status'),
        ),
    );
     $max = variable_get('orbitscriptsads_managed_last_block', 0);
	 $nodes = db_query("SELECT delta, region, title, status FROM {blocks} WHERE module = 'orbitscriptsads_managed'", 10);
     
     for ($count=0 ; $count <= $max ; $count++) {
        if ($ad = variable_get('orbitscriptsads_managed_ad_block_'. $count, ORBITSCRIPTSADS_MANAGED_AD_BLOCK_DEFAULT)) {
          if ($ad == NULL) {
          	break;
          }

       $title = $ad['channel_name'];
       $succ = false;
       		while ($node = db_fetch_object($nodes))    {
	     		 if ($node->delta == $count) {

    				$options[$node->delta] = '';
    				$form['title'][$node->delta] = array('#value' => $title); 
        			$form['region'][$node->delta] = array('#value' => (!empty($node->region)) ? $node->region : '<a href="../../build/block" title="Configure block">unconfigured</a>');          
					$form['status'][$node->delta] = array('#value' => ($node->status == 1) ? 'active' : '<a href="../../build/block" title="Configure block">inactive</a>');
					break;
       	  		}
       		}
       }
     }

    $form['bid'] = array(
        '#type' => 'checkboxes',
        '#options' => isset($options) ? $options : array(),
    );
 
    return $form;
}
 
 
/**
 * Themisation orbitscriptsads_managed_form_table
 * Creating table
 */
function theme_orbitscriptsads_managed_form_table($form)
{
    $output = '';
    $rows = array();
 
    foreach (element_children($form['title']) as $key)
    {
        $rows[] = array(
            drupal_render($form['bid'][$key]),  
            drupal_render($form['title'][$key]), 
            drupal_render($form['region'][$key]),
            drupal_render($form['status'][$key]),
            
        );
    }
 
    $output .= theme('table', $form['header']['#value'], $rows); // выводим таблицу
    $output .= drupal_render($form);                             // выводим остальную часть формы
 
    return $output;
}
 
/**
 * Submit form orbitscriptsads_managed_form_table
 * Explode bids
 */
function orbitscriptsads_managed_form_table_submit($form, &$form_state)
{
    $bids = array();
 
    foreach ($form_state['values']['bid'] as $bid)
    {
        if (isset($bid))
        {
            $bids[] = $bid;
        }
    }
    	orbitscriptsads_managed_form_table_delete($bids);
}

/*
 * Delete choosen blocks 
 */
function orbitscriptsads_managed_form_table_delete ($bids) {
		foreach ($bids as $delta){
			
		db_query('DELETE FROM blocks WHERE module="orbitscriptsads_managed" AND delta = '.$delta);
		
		$max = variable_get('orbitscriptsads_managed_number_blocks', ORBITSCRIPTSADS_MANAGED_NUMBER_BLOCKS_DEFAULT);
		variable_set('orbitscriptsads_managed_ad_block_'. $delta, NULL);
   		variable_set('orbitscriptsads_managed_number_blocks', $max-1);
		cache_clear_all();
		}
	    drupal_set_message('Block(s) were deleted: ' . implode(', ', $bids));
}

function orbitscriptsads_managed_add_block_table() {
	$api=new OrbitAdsApi();
	$channels = $api->execute('getChannelsHtml');
	$palettes = $api->execute('getPalettesHtml');

	$form = array();
	$errors = variable_get('orbitscriptsads_errors', array('code'=>'-2','msg'=>'Undefined respose'));
	if ($errors['code'] === '0') {
 		$form['new'] = array(
    		'#type' => 'fieldset',
    		'#collapsible' => FALSE,
    		'#collapsed' => FALSE,
    		'#title' => t('Add new'),
  		);
  
  		$form['new']['orbitscriptsads_channel'] = array(
    		'#type'          => 'select',
    		'#title'         => t('Select Channel'),
    		'#required'      => TRUE,
    		'#description'   => t('Please, choose channel from '.ORBITSCRIPTSADS_SITE_NAME, array()),
  			'#options' => $channels
  		);
  
  		$form['new']['orbitscriptsads_palette'] = array(
    		'#type'          => 'select',
    		'#title'         => t('Select Palette'),
    		'#required'      => TRUE,
    		'#description'   => t('Please, choose palette from '.ORBITSCRIPTSADS_SITE_NAME, array()),
  			'#options' => $palettes
 		);
  
  		$form['#submit'][] = 'orbitscriptsads_managed_add_block_new';
  
  		$form['buttons']['submit'] = array('#type' => 'submit', '#value' => t('Create') );
  		$form['buttons']['reset'] = array('#type' => 'submit', '#value' => t('Refresh lists') );
	} else {
		drupal_set_message(t('Unable to load API data. The following error was get: <br /><b>'.$errors['code'].' - '.$errors['msg'].'</b>'.'<br /> You cannot create new blocks, please check setting'),'error');
	}
  return $form;
}

function orbitscriptsads_managed_add_block_new ($form, &$form_state) {
  $max = variable_get('orbitscriptsads_managed_number_blocks', ORBITSCRIPTSADS_MANAGED_NUMBER_BLOCKS_DEFAULT);
  $last = variable_get('orbitscriptsads_managed_last_block', -1);
  $data = _orbitscriptsads_managed_get_ad($form_state['values']['orbitscriptsads_channel'], $form_state['values']['orbitscriptsads_palette']);
  $current = $last+1;

  variable_set('orbitscriptsads_managed_ad_block_'.$current, $data);
  variable_set('orbitscriptsads_managed_last_block', $last+1);
  variable_set('orbitscriptsads_managed_number_blocks', $max+1);
  
  drupal_set_message(t('The block has been created.'));
  cache_clear_all();

  $form_state['redirect'] = 'admin/build/block';
  return;
}