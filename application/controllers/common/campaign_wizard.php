<?php if (!defined('BASEPATH') || !defined('APPPATH')) exit('No direct script access allowed');

require_once APPPATH . 'controllers/parent_controller.php';

/**
* Контроллер для пошагового создания кампании
*
* @author Немцев Андрей
* @project SmartPPC6
* @version 1.0.0
*/
class Campaign_Wizard extends Parent_controller {
   /**
    * Наследующий контроллер для выводе контента, соответствующего определенному шагу кампании должен:
    * 1. Указать идентификатор формы, которая будет отправляться при переходе к следующему шагу: form_id;
    * 2. Указать тип создаваемой кампании: set_campaign_type($campaign_type);
    * 3. Определить номер текущего шага: setCurrentStep(uri_string());
    */

   protected $create_save_button = 'Create';

   protected $progressbar = TRUE;

	/**
    * Описание шагов для создания кампаний различного типа
    * Формат описания шагов создания: array('код кампании' => array( 
    *                                                                array( //шаг 1
    *                                                                       'controller' => 'путь к контроллеру шага',
    *                                                                       'title' => 'заголовок формы при отображени формы в пошаговом режиме',
    *                                                                       'review_title' => (опционально)'заголовок формы при отображении в режиме review',
    *                                                                       'review_next_step' => (опционально)'путь к контроллеру следующего шага в режиме review',
    *                                                                       'review_prev_step' => (опционально)'путь к контроллеру предыдущего шага в режиме review')
    *                                                                      ),
    *                                                                ...
    *                                                                )
    * 
    * @var array
    */
	protected $campaign_types = array('cpm_flatrate' =>
                                    array( 
                                           array('controller' => 'advertiser/create_campaign_step_main/index/cpm_flatrate',
                                                'title' => 'Create CPM / Flatrate Campaign',
                                                'review_title' => 'Edit CPM / Flatrate Campaign'),
                                           array('controller' => 'advertiser/create_campaign_step_group_name/index/cpm_flatrate',
                                                'title' => 'Create Group',
                                                'review_title' => 'Edit Group'),
                                           array('controller' => 'advertiser/create_campaign_step_choose_sites_channels/index/cpm_flatrate',
                                                'title' => 'Choose Sites/Channels',
                                                'review_title' => 'Choose Sites/Channels',
                                                'review_next_step' => 'advertiser/create_campaign_step_set_pricing/index/cpm_flatrate'),
                                           array('controller' => 'advertiser/create_campaign_step_set_pricing/index/cpm_flatrate',
                                                'title' => 'Set Pricing',
                                                'review_title' => 'Set Pricing',
                                                'review_prev_step' => 'advertiser/create_campaign_step_choose_sites_channels/index/cpm_flatrate'),
                                           array('controller' => 'advertiser/create_campaign_step_create_ad/index/cpm_flatrate',
                                                'title' => 'Create Ad',
                                                'review_title' => ''),
                                           array('controller' => 'advertiser/create_campaign_step_preview_ads/index/cpm_flatrate',
                                                'title' => 'Preview Ads',
                                                'review_title' => 'Preview Ads'),
                                           array('controller' => 'advertiser/create_campaign_step_review_selections/index/cpm_flatrate',
                                                'title' => 'Review your selections')),
                                    'edit_channels' =>
                                    array( 
                                           array('controller' => 'advertiser/edit_channels', 
                                                 'title' => 'Choose Sites/Channels'),
                                           array('controller' => 'advertiser/edit_set_pricing', 
                                                 'title' => 'Set Pricing')),
                                    'edit_sites' =>
                                    array( 
                                           array('controller' => 'advertiser/edit_sites', 
                                                 'title' => 'Select Sites')),
                                    'edit_bids' =>
                                    array( 
                                           array('controller' => 'advertiser/edit_cpc_bids', 
                                                 'title' => 'Manage Bids')),
                                    'create_ad' =>
                                    array( 
                                          array('controller' => 'advertiser/create_campaign_step_create_ad/index/cpm_flatrate',
                                                'title' => 'Create Ad')),
                                    'edit_campaign' =>
                                    array( 
                                          array('controller' => 'advertiser/edit_campaign',
                                                'title' => 'Edit Campaign',
                                                'confirm_button_title' => 'Save')));
   /**
	 * Тип создаваемой кампании
	 *
	 * @var string
	 */
	private $campaign_type = '';

   /**
    * JS код, выполняющийся при нажатии на кнопку "Далее"
    *
    * @var string
    */
	protected $on_submit = '';

   /**
    * Адрес контроллера, на который будет направлен пользователь при отмене создания кампании
    *
    * @var string
    */
   protected $cancel_creation_controller = '';

   /**
    * Отображение шага в режиме Review
    *
    * @var bool
    */
   protected $review_mode = false;

   /**
    * Массив шагов текущего типа кампании(('controller' => 'путь к контроллеру 1 шага', 'title' => 'название 1 шага', 'confirm_button_title'(опционально) => 'надпись кнопки сохранения изменений шага в режиме review'), ...)
    *
    * @var array
    */
   private $steps = array();

   /**
    * Номер текущего шага
    *
    * @var int
    */
   protected $current_step; //номер текущего шага
   
   /**
    * Текст подтверждения при отмене создания кампании
    *
    * @var string
    */
   protected $cancel_confirmation = 'Are you sure to cancel campaign creation?'; //текст подтверждения

   /**
    * Текст заголовка отображаемой формы
    *
    * @var string
    */
   protected $form_title = '';
   
   /**
    * Идентификатор временного XML файла для сохранения данных кампании 
    *
    * @var string|bool
    */
   protected $id_xml = false;
    /**
     * Current wizard
     * 
     * @var Sppc_Wizard
     */
	protected $_wizard = null;
	/**
	 * Current wizard steps
	 * 
	 * @var array
	 */
	protected $_steps = null;
	/**
	 * Current Step
	 * 
	 * @var Sppc_Wizard_Step
	 */
	protected $_currentStep = null;
	/**
	 * Model for working with wizard records
	 * 
	 * @var Sppc_WizardModel
	 */
	protected $_wizardModel = null;
   
	protected $_hooks = array();
   
  /**
   	 * Constructor
   *
   	 * @return void
   */
   public function __construct() {
   	parent::__construct();
   	$this->load->model('new_campaign');
   	$this->load->library('form');
      $this->id_xml = $this->session->userdata('id_xml');

      if (FALSE === $this->id_xml) {
         $this->id_xml = uniqid();
         $this->session->set_userdata('id_xml', $this->id_xml);
     } 
      	$this->_wizardModel = new Sppc_WizardModel();
      // Fill up hooks array
      $pluginsConfig = Zend_Registry::getInstance()->get('pluginsConfig');
      if (isset($pluginsConfig->common->campaign_wizard)) {
         foreach($pluginsConfig->common->campaign_wizard as $hookClass) {
            $hookObj = new $hookClass();
            if ($hookObj instanceof Sppc_Common_CampaignWizard_EventHandlerInterface) {
               $this->_hooks[] = $hookObj;
            }
         }
      }
   } //end __construct

   /**
    * Определение первого шага для активного типа кампании
    *
    * @param string $controller_path путь к первому шагу кампании
    * @return Sppc_Wizard_Step|null
    */
   protected function get_first_step($controller_path) {
   		if ((!is_null($this->_steps)) && (count($this->_steps) > 0)) {
   	  		return $this->_steps[0]->getController();
   	  	}
      	return '';
      }

   /**
    * Calculate current step based on path
    * 
    *
    * @param string $controller_path
    * @return void
    */
   	protected function setCurrentStep($controller_path) {
   	        $controller_path = preg_replace('~/+~', '/', $controller_path);
   		if (is_array($this->_steps)) {
	   		foreach ($this->_steps as $step) {
	   			//Zend_Debug::dump($step);
	   			if (trim($controller_path, '/') == trim($step->getController(), '/')) {
	   				$this->_currentStep = $step;
	   				break;
	   			}
   		}
   	}
   }

   /**
     * Return path to the next step (if exitst)
    *
     * @return string
    */
   protected function get_next_step_controller() {
   		if (!is_null($this->_currentStep)) {
   	if ('true' == $this->review_mode) {
   				if (!is_null($this->_currentStep->getReviewNextStep())) {
   					return $this->_currentStep->getReviewNextStep();
   		} else {
   					$lastStep = count($this->_steps) - 1;
   					return $this->_steps[$lastStep]->getController();
   				}
   			} else {
   				if (!$this->_currentStep->isLastStep()) {
   					return $this->_currentStep->getNextStep()->getController();
   				}
   		}
   	}
   		
		   		return '';
		   	}
   		
   /**
     * Return path to the previous step (if exists)
    *
     * @return string
    */
   protected function get_previous_step_controller() {
   		if ((!is_null($this->_currentStep)) && (!$this->_currentStep->isFirstStep())) {
   			$previousStep = $this->_currentStep->getPreviousStep();
   			
   			if (!is_null($previousStep)) {
   				if (('true' == $this->review_mode) && (!is_null($previousStep->getReviewPreviousStep()))) {
   					return $previousStep->getReviewPreviousStep();
         } else {
   					return $previousStep->getController();
         }
      }
   		}
   		
		         return '';
		      }

   /**
    * Задание типа кампании
    *
    * @param string $type
    */
   protected function set_campaign_type($type) {
   	$this->campaign_type = $type;
   		//$this->steps = $this->campaign_types[$this->campaign_type];
   		
   		$wizardModel = new Sppc_WizardModel();

   		$this->_wizard = $wizardModel->findObjectById($this->campaign_type);
   		if (is_null($this->_wizard)) {
   			throw new Sppc_Exception('Specified wizard "'.$type.'" not found');
   		}
   		
   		$this->_steps = $this->_wizard->getSteps();
   }

   /**
    * Отображение первого шага либо страницы выбора типа кампании
    * //TODO - перенести функцию в наследующий контроллер?
    */
   public function index() {
      $this->load->model('campaign_types_model');
 		$campaign_types_list = $this->campaign_types_model->get_list();

   	if (!is_null($campaign_types_list)) {	   	
	   			$wizard = $this->_wizardModel->findObjectById($campaign_types_list[0]['campaign_type']);	   			
	   			if (!is_null($wizard)) {
	   				$wizardSteps = $wizard->getSteps();
	   				
	   				if (count($wizardSteps) > 0) {
	   					$firstStep = $wizardSteps[0];
	   					redirect($firstStep->getController());
	   					return ;
	   				}
	   			}	   			
	   			die('Campaign types are not found');	   	
      } else {
      	//TODO Страница с ошибкой создания кампании - нет доступных кампаний
      	die('Campaign types are not found');
      }
   } //end index()

   /**
    * Задание контента страницы с добавлением полей прогресса и кнопок навигации по шагам
    * '<%PROGRESS_BAR%>' - строка, отображающая последовательность шагов
    * '<%NAVIGATION_BAR%>' - панель, отображающая кнопки для переключения между шагами
    *
    * @param string $content контент страницы
    */
   public function _set_content($content) {
   	$buttons_html = '';
   	$steps_count = count($this->_steps);
   	
   	if (!$this->review_mode) {
   		$steps_html = '';
   		$steps_spacer = $this->load->view('common/campaign_wizard/steps_spacer.html','',TRUE);
   		
   		for ($i = 0; $i < $steps_count;$i++) {
   			if ($i > 0) {
   				$steps_html.= $steps_spacer;
   			}
   			if ($i < $this->_currentStep->getStep()) {
   				$steps_html .= $this->parser->parse('common/campaign_wizard/completed_step.html', array(
   			   		'STEP_TITLE' => __($this->_steps[$i]->getTitle())
   					),TRUE
   				);
   			}
   		   	if ($i == $this->_currentStep->getStep()) {
            	$steps_html .= $this->parser->parse('common/campaign_wizard/current_step.html', array(
            		'STEP_TITLE' => __($this->_steps[$i]->getTitle())
            		),TRUE
            	);
            }
   		   	if ($i > $this->_currentStep->getStep()) {
            	$steps_html .= $this->parser->parse('common/campaign_wizard/not_completed_step.html', array(
            		'STEP_TITLE' => __($this->_steps[$i]->getTitle())
            		),TRUE
            	);
            }
   		}

   		//Back
   		if ((!is_null($this->_currentStep)) && (!$this->_currentStep->isFirstStep())) {
   		 	$buttons_html .= $this->parser->parse('common/campaign_wizard/button.html',array(
   		 		'BUTTON_ICON' => 'ico-back', 
   		 		'BUTTON_TITLE' => __('Back'),
   		 		'ON_CLICK' => 'onclick=\'top.document.location="<%SITEURL%>'.$this->_currentStep->getPreviousStep()->getController().'"\''),
   		 	TRUE);
   		}

   		//Next/Create
   		if ((!is_null($this->_currentStep)) && (!$this->_currentStep->isLastStep())) {
   			   $button_title = __('Next');
   			   $button_icon = 'ico-next';
   			} else {
   				$button_title = __($this->create_save_button);
   			   $button_icon = 'ico-confirm';
   			}

   		$buttons_html .= $this->parser->parse('common/campaign_wizard/button.html', array(
   			'BUTTON_ICON' => $button_icon, 
   			'BUTTON_TITLE' => $button_title,
   			'ON_CLICK' => 'onclick="'.$this->on_submit.'"')
   		,TRUE);

   		//Cancel
   		$cancel_func = 'go("<%SITEURL%><%INDEXPAGE%>'.$this->cancel_creation_controller.'")';
   		if ('' != $this->cancel_confirmation) {
   			$cancel_func = 'if(confirm("'.__($this->cancel_confirmation).'")) {'.$cancel_func.'}';
   		}
        $buttons_html.="&nbsp;&nbsp;&nbsp;".$this->parser->parse('common/campaign_wizard/button.html', array(
        	'BUTTON_ICON' => 'ico-cancel', 'BUTTON_TITLE' => __('Cancel'),
        	'ON_CLICK' => 'onclick=\''.$cancel_func.'\''),
        TRUE);

   		$progress_bar = '';
   		if ($this->progressbar) {
   			$progress_bar = $this->parser->parse('common/campaign_wizard/progress_bar.html',array(
   				'STEPS' => $steps_html),
 		 	TRUE);
   		}
         $content = str_replace('<%PROGRESS_BAR%>', $progress_bar, $content);

      } else {//открытие страницы в режиме review
   		if (!is_null($this->_currentStep->getReviewNextStep())) {
               $button_title = __('Next');
               $button_icon = 'ico-next';
      	} else {
      		if (!is_null($this->_currentStep->getConfirmationButtonTitle())) {
      			$button_title = __($this->_currentStep->getConfirmationButtonTitle());
	         } else {
	            $button_title = __('Apply');
	         }
	         $button_icon = 'ico-confirm';
      	}
      	
      	$buttons_html .= $this->parser->parse('common/campaign_wizard/button.html',array(
      		'BUTTON_ICON' => $button_icon, 
      		'BUTTON_TITLE' => $button_title,
      		'ON_CLICK' => 'onclick="'.$this->on_submit.'"'),
      	TRUE);
      		
      	if (!is_null($this->_currentStep->getReviewPreviousStep())) {
      		$back_cotnroller_path = $this->_currentStep->getReviewPreviousStep();
      		$buttons_html.=$this->parser->parse('common/campaign_wizard/back_button.html', array(
      			'PATH_TO_PREV_CONTROLLER' => '<%SITEURL%><%INDEXPAGE%>'.$back_cotnroller_path),
      		TRUE);
      	} else {
   			$lastStep = count($this->_steps) - 1;
      		$back_cotnroller_path = $this->_steps[$lastStep]->getController();
      		$buttons_html.=$this->parser->parse('common/campaign_wizard/button.html', array(
      			'BUTTON_ICON' => 'ico-back', 'BUTTON_TITLE' => __('Back'),
      			'ON_CLICK' => 'onclick=\'go("<%SITEURL%><%INDEXPAGE%>'.$back_cotnroller_path.'")\''),
      		TRUE);
      	}
      	
      	if (!is_null($this->_currentStep->getReviewTitle())) {
      		$content = str_replace('<%PROGRESS_BAR%>', '<h1>'.__($this->_currentStep->getReviewTitle()).'</h1>', $content);
         } else {
            $content = str_replace('<%PROGRESS_BAR%>', '', $content);
         }
   	}

   	
   	$content = str_replace('<%FORM_TITLE%>',$this->form_title,$content);

   	$navigation_bar = $this->parser->parse('common/campaign_wizard/navigation_bar.html',array('BUTTONS' => $buttons_html),TRUE);
   	$content = str_replace('<%NAVIGATION_BAR%>',$navigation_bar,$content);
   	parent::_set_content($content);
   }

}