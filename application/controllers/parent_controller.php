<?php if (!defined('BASEPATH') || !defined('APPPATH')) exit('No direct script access allowed');

require_once APPPATH . 'libraries/MY_Controller.php';

/**
 * родительский контроллер для всех контроллеров SmartPPC6
 * доступные методы
 * _set_title($title)      устанавливает title для web-страницы
 * _add_css($name)         добавляет в header web-страницы css файл
 * _add_java_script($name) добавление в header страницы java-скриптов
 * _set_content($text)     устанавливает основное содержимое web-страницы
 * _set_help_index($index) задает индекс системы помощт для данной web-страницы
 * _display()              формирует, переводит и выводит на экран web-страницу
 *
 * @author Владимир ЮдинUPANEL
 * @project SmartPPC6
 * @version 1.0.0
 */
class Parent_controller extends MY_Controller {

   /**
    * разделитель пунктов меню в заголовке окна браузера
    *
    */
   const TITLE_SEP = " - ";

   public $site_url; // базовый адрес сайта проекта


   public $index_page; // индекс контроллер


   protected $role; // роль пользователя, открывающего контроллер


   protected $user_name; // e-mail текущего пользователя (если не админ)


   protected $menu_item = "Dashboard"; // активный пункт меню у контроллера


   protected $mail_stat = 0; // статистика по количество почтовых сообщений (всего/непрочитанных)


   protected $date_format; // локальный формат даты


   protected $money_format; // локальный формат денег


   protected $number_format; // локальный формат чисел


   protected $week_start; // локальное начало недели (номер дня недели)


   protected $template = "common/parent/common.html"; // базовый шаблон отображения


   protected $noframe = FALSE; // не обрабатывать сессий, меню, upanel


   protected $date_picker = FALSE;

   public $temporary = array(); // массив для временных переменных пользователя

   protected $_javascriptInline = array();
   
   public $default_locale = 'en_US';
   
   protected $help_for = array( // показывать help для пользователей
         'admin',
         'advertiser',
         'publisher',
         'member');

   protected $content = array( // массив для формирования содержимого контроллера
         "FOOTER" => "",
         "HEADER" => "",
         "BODY" => "",
         "TITLE" => "",
         "CSS" => "",
         "JAVASCRIPT" => "",
         "UPANEL" => "",
         "HELP" => "",
         "MENU" => "",
         "ROLE" => "",
         'NOTIFICATION' => '',
         'HELPDISPLAY' => 'none',
         'SHOWROLE' => '',
         "SITENAME" => "SmartPPC 6",
         "SITEURL" => "",
   		 'BYTECITY_LINK_TOP' => '',
   		 'BYTECITY_LINK_FEED' => '',
          'COMMUNITY_TOP' => '');




   /**
    * конструктор контроллера
    *
    * @return ничего не возвращает
    */
   public function Parent_controller() {  
      parent::__construct();
      $this->load->library('session');
      $this->output->enable_profiler(FALSE);
      $this->site_url = $this->config->slash_item('base_url');
      $this->index_page = $this->config->item('index_page');
      
      if(!empty($this->index_page)) {
         $this->index_page .= '/';
      }

      $this->_load_session();
      if (in_array($this->role, $this->help_for)) {
         $this->content['HELPDISPLAY'] = '';
      }
      $this->_load_temporary();
      //$this->_init_translate();

      $this->load->library("parser");
      $this->parser->set_delimiters("<%", "%>");
      $this->load->model("menu", "", TRUE);
      $this->_add_css("style6"); // smartPPC6 CSS Aggregator
      $this->_add_css("design");
      $this->content["JAVASCRIPT"] .= "<script type='text/javascript'>var site_url = '{$this->site_url}'</script>\n";
      $this->_add_java_script('j');
      $this->_add_java_script('stuff');
      $this->_add_java_script('jquery');
      $this->_add_java_script('jquery-ui-full.min');
      $this->_add_java_script('jquery.tree');
      $this->_add_css('_commonlib');
      $this->_add_css('_elements');
      $this->_add_css('jquery');
      

      /*
      if ($this->config->item('debug_mode')) {
         $this->load->helper('firephp');
         $firephp =& get_firephp();
         $firephp->log($this->session->all_userdata(), 'Session');
      }
      */

      // Загружаем сообщение нотификации из сессии
      if (false !== $this->session->flashdata('notification_message')) {
         $message = $this->session->flashdata('notification_message');
         $message_type = 'notification'; 
         if (false !== $this->session->flashdata('notification_type')) {
            $message_type = $this->session->flashdata('notification_type');
         }
         $this->_set_notification($message, $message_type);
      }
   }

   /**
    * добавляет в HTML-код функцию создания Request объекта для Ajax
    *
    * @return ничего не возвращает
    */
   protected function _add_ajax() {
      $this->content["JAVASCRIPT"] .= "
<script type='text/javascript'>
//<!--
	function checkAjaxLogin(text) {
	   if (!text) return true;
		if (text.search('<!--Login'+'Page-->') == -1) {
			return false;
		}
		document.location = '".$this->site_url . $this->index_page . $this->role ."/login/timeout';
		return true;
	}

	function createRequestObject() {
		if (window.XMLHttpRequest) {
			try {
				return new XMLHttpRequest();
			} catch (e){ }
		} else if (window.ActiveXObject) {
			try {
				return new ActiveXObject('Msxml2.XMLHTTP');
			} catch (e){
				try {
					return new ActiveXObject('Microsoft.XMLHTTP');
				} catch (e){}
			}
		}
		return null;
	}

	var req = createRequestObject();
//-->
</script>\n";
   }

   /**
    * загружает временные переменные необходимые для контроллера
    *
    * @return ничего не загружает
    */
   protected function _load_temporary() {
      foreach ($this->temporary as $var => $value) {
         $new_value = $this->global_variables->get($var, $this->user_id);
         if (!is_null($new_value)) {
            $this->temporary[$var] = $new_value;
         }
      }
   }

   /**
    * обновляет состояние временных переменных используемых в контроллере
    *
    * @return ничего не возвращает
    */
   protected function _save_temporary() {
      foreach ($this->temporary as $var => $value) {
         $this->global_variables->set($var, $this->temporary[$var], $this->user_id, TRUE);
      }
   }

   /**
    * получает сессию для текущего пользователя
    *
    * @return ничего не возвращает
    */
   public function _load_session() {
      /*if ($this->role == 'guest' || $this->role == 'guest_admin') {
         return;
      }*/

      if ($this->role == 'guest_admin') {
         return;
      }

      $this->_save_session();
      $session_data = $this->session->userdata('auth_' . $this->role);
      if (!is_array($session_data) || !isset($session_data['userid']) || !$session_data['userid'] || 'guest' == $this->role) {
         // В сессии нет еще авторизационной переменной
         // Пытаемся найти хотябы одну рабочую сессию
         $succ = false;
         $this->load->model('roles');
         $all_roles = $this->roles->get_all_roles();
         foreach ($all_roles as $role) {
            if ('guest' == $role) {
               continue;
            }
            $session_data = $this->session->userdata('auth_' . $role);
            if (is_array($session_data) && isset($session_data['userid']) && $session_data['userid']) {
               $succ = true;
               break;
            }
         }
         if (!$succ) {
            if ($this->noframe || $this->role == 'guest') {
               $this->load->model('entity');
               $guest = $this->entity->get_guest();
               if (null !== $guest) {
                  $this->user_id = $this->entity->get_guest()->id_entity;
                  $this->user_name = $this->entity->get_guest()->e_mail;
               }
               return;
            }
            // Debug
            /*
            if (false !== ($fh = fopen(BASEPATH . 'logs/timeout-' . getenv('REMOTE_ADDR') . '.log', FOPEN_WRITE_CREATE))) {
               fwrite($fh, "Date: " . date('Y-m-d H:i:s') . "\n");
               fwrite($fh, "Session:\n");
               fwrite($fh, print_r($this->session->userdata, true));
               fwrite($fh, "Server:\n");
               fwrite($fh, print_r($_SERVER, true));
               fclose($fh);
            }
            */

            /* debug log for timeout redirect
            ob_start();
            echo $this->role . "\n";
            print_r($this->session->userdata);
            $var = ob_get_contents();
            ob_end_clean();
            $var = date('Y-m-d H:i:s') . '============================================================' . "\n" . $var;
            $fp = fopen(BASEPATH . 'timeout' . date('Y-m-d') . '.log','a+');
            fputs($fp,$var);
            fclose($fp);
            */
            $this->_save_session();

            /* debug log for timeout redirect
            ob_start();
            print_r($this->session->userdata);
            $var = ob_get_contents();
            ob_end_clean();
            $fp = fopen(BASEPATH . 'timeout' . date('Y-m-d') . '.log','a+');
            fputs($fp,$var);
            fclose($fp);
            */
            redirect($this->role . '/login/timeout');
            exit();
         }
      }
      /*
      if ($this->config->item('debug_mode')) {
         $this->load->helper('firephp');
         $firephp =& get_firephp();
         $firephp->log($session_data, 'Load Session Data');
      }
      */
      $this->user_id = $session_data['userid'];
      if ($this->noframe) {
         return;
      }
      $this->user_name = $session_data['username'];
      $this->_save_session();

      $session_role = $session_data['role'];

      // если у контроллера роли guest, то данный контроллер доступен всем ролям
      if ($this->role != 'guest' && $this->role != $session_role) {
         $this->load->model('entity');
         $roles = $this->entity->get_roles($this->user_id, 'active');
         if (empty($roles)) {
            redirect();
         } elseif (!in_array($this->role, $roles)) {
            $this->role = current($roles);
            if (!empty($this->role)) {
               redirect($this->role . '/dashboard');
            } else {
               redirect();
            }
         }
      }

   }

   /**
    * сохраняет сессию текущего пользователя
    *
    * @param $role
    * @return ничего не возвращает
    */
   public function _save_session() {
      /*      if ($this->noframe || $this->role == "guest" || $this->role == "guest_admin" || empty($this->role) || empty($this->user_id)) {
         return;
      }*/
      if ($this->noframe || $this->role == "guest_admin" || empty($this->role) || empty($this->user_id)) {
         return;
      }

      $this->load->model('entity');
      $all_roles = $this->entity->get_roles($this->user_id);
      $roles = $this->entity->get_roles($this->user_id, 'active');
      if (in_array($this->role, $all_roles)) {
         if (in_array($this->role, $roles)) {
            $session = array(
                  'userid' => $this->user_id,
                  'username' => $this->user_name,
                  'menuitem' => $this->menu_item,
                  'role' => $this->role);
            $this->session->set_userdata('auth_' . $this->role, $session);
         } else {
            $this->session->unset_userdata('auth_' . $this->role);
         }
      }
   }

   /**
    * задание заголовка web-страницы
    *
    * @param string $title заголовок web-странцы
    * @return ничего не возвращает
    */
   public function _set_title($title) {
      //Sitename учавствовал в формировании тайтла,
      //но теперь не учавствует.
      $this->content['SITENAME'] = '{@' . $this->global_variables->get('SiteName') . '@}';
      $this->content['TITLE'] = '{@' . $title . '@}';		
   }
   
   /**
    * добавление в header страницы css-файла
    *
    * @param string $name имя css-файла
    * @return ничего не возвращает
    */
   public function _add_css($name) {
      $this->content["CSS"] .= "<link type='text/css' rel='stylesheet' href='{$this->site_url}css/$name.css'/>\n";
   }

   /**
    * добавление в header страницы java-скриптов
    *
    * @param string $name имя локального js-файла, либо полный url стороннего java-скрипта
    * @return ничего не возвращает
    */
   public function _add_java_script($name) {
      if (strpos($name, "http://") === FALSE) {
         $this->content["JAVASCRIPT"] .= "\n<script type='text/javascript' src='{$this->site_url}js/$name.js'></script>";
      } else {
         $this->content["JAVASCRIPT"] .= "\n<script type='text/javascript' src='$name'></script>";
      }
   } //end _add_java_script


   public function _add_java_script_inline($code, $key=null) {
       if(is_null($key)) {
            $this->_javascriptInline[] = $code;
       }
       else {
           $this->_javascriptInline[$key] = $code;
       }
   }

   public function _unset_java_script_inline($key) {
       if(array_key_exists($key,$this->_javascriptInline)) {
           unset($this->_javascriptInline[$key]);
       }
   }

   /**
    * установка основного содержимого web-страницы
    *
    * @param string $text HTML-код содержимого страницы
    * @return ничего не возвращает
    */
   public function _set_content($text) {
      $this->content["BODY"] = $text;
   } //end _set_content


   /**
    * установка индекса в системе помощи проекта для данной web-страницы
    *
    * @param string $index индекс в системе помощи
    * @return ничего не возвращает
    */
   public function _set_help_index($index) {
      $this->content["HELP"] = "http://orbitopenadserver.com/help/$index";
      $this->content["HELPTOPIC"] = "help/$index";
   } //end _set_help_index


   /**
    * показывает странице при вызове самого родительского контроллера,
    * используется для тестирования контроллера
    *
    * @return ничего не возвращает
    */
   public function index() {
      $this->_set_title("Parent Controller");
      $this->_set_help_index("parent_controller");
      $this->_set_content("<H1>{@Parent Controller Dummy Content@}</H1>");
      $this->_display();
   } //end index


   /**
    * подготавливает механизм локализации
    *
    * @return ничего не возвращает
    */
   protected function _init_translate() {
      $locale = $this->_get_locale();
      setlocale(LC_ALL, $locale);
      setlocale(LC_NUMERIC, 'POSIX');
      bindtextdomain("messages", "./system/locale");
      textdomain("messages");
   } //end _init_translate

   /**
    * переводит текст на язык, соответствующий локали пользователя,
    * строки, нуждающиеся в переводе выделены в тексте тегами "{@" и "@}",
    * если для данной локали отсутствует словарь строк -
    * возвращается текст находящийся между тегами
    *
    * @param string $text текст, который необходимо перевести
    * @return string переведенный текст
    */
   protected function _translate($text) {
      preg_match_all("/{@([\s\S]*?)@}/", $text, $matches);
      foreach ($matches[1] as $message) {
         
         $text = str_replace("{@" . $message . "@}", __($message), $text);
         
      }
      return $text;
   } //end _translate


   /**
    * возвращает HTML-код для выбора текущей роли,
    * или текущую роль, если у пользователя она одна
    *
    * @return string HTML-код
    */
   public function _get_roles() {
      if ($this->user_id == "") {
         return '<b>' . __('guest') . '</b>';
      }
      $this->load->model('entity', '', TRUE);
      $roles = $this->entity->get_roles($this->user_id, 'active');
      if (count($roles) == 1) {
         if ($roles[0] == 'admin') {
            return '<b>' . __('administrator') . '</b>';
         }
         return '<b>' . __($roles[0]) . '</b>';
      }

      $options = '';
      foreach ($roles as $role) {
         $select = ($role == $this->role) ? ' selected' : '';
         $nrole = ($role == 'admin') ? 'administrator' : $role;
         $options .= "<option value='$role'$select>" . __($nrole) . '</option>\n';
      }
      return str_replace('<%OPTIONS%>', $options, $this->load->view('common/parent/upanel_roles.html', '', TRUE));
   } //end _get_roles


   /**
    * генерация информационной панели для правого верхнего угла
    *
    * @return ничего не возвращает
    */
   public function _get_upanel() {

      if ($this->noframe) {
         return;
      }
      $upanel_vars = array(); 
      $this->load->model('entity', '', TRUE);
      if (in_array($this->role, array(
            'guest',
            'guest_admin',
            'member'))) {
         $upanel_vars['BALLANCE'] = '';
      } else {
      	 $this->load->library("Plugins", array(
   	        'path' => array('advertiser', 'dashboard'),
            'interface' => 'Sppc_Advertiser_Dashboard_Coupons_Interface'
   	     ), 'user_bonus');

   	     $ballance = type_to_str($this->entity->ballance($this->user_id), 'money');
      	 $bdata = array(
            'BALLANCE'  => type_to_str($this->entity->ballance($this->user_id), 'money'),
            'BONUS'     => '',
            'USE_BONUS' => array(),
      	 	'USE_HOLD'	=> array()
         );
         
      	 $this->user_bonus->run('getDashboardBonus', $bdata);

         $upanel_vars['BALLANCE'] = $this->parser->parse('common/parent/upanel_ballance.html', $bdata, true);
      }

      if ($this->user_name == "") {
         $upanel_vars["USERNAME"] = "";
      } else {
         $upanel_vars["USERNAME"] = str_replace('<%EMAIL%>', $this->user_name, $this->load->view('common/parent/upanel_email.html', '', TRUE));
      }
      $upanel_vars["ROLES"] = $this->_get_roles();
      $upanel_vars["SITEURL"] = $this->site_url;
      $upanel_vars["INDEXPAGE"] = $this->index_page;
      if ($this->role == "guest_admin") {
         $upanel_vars["ROLE"] = "guest";
      } else {
         $upanel_vars["ROLE"] = $this->role;
      }
      if ($this->role == "guest" || $this->role == "guest_admin") {
         $this->content['SHOWROLE'] = 'none';
         $upanel_vars["ACTION"] = "/login";
         $upanel_vars["ACTIONTEXT"] = "Login";
      } else {
         $upanel_vars["ACTION"] = "/logout";
         $upanel_vars["ACTIONTEXT"] = "Logout";
      }

      $upanel = $this->load->view('common/parent/upanel.html', '', TRUE);
      $upanel = str_replace('<%USERNAME%>', $upanel_vars["USERNAME"], $upanel);
      $this->content['ROLES'] = $upanel_vars["ROLES"];
      $upanel = str_replace('<%BALLANCE%>', $upanel_vars["BALLANCE"], $upanel);
      $upanel = str_replace('<%SITEURL%>', $upanel_vars["SITEURL"], $upanel);
      $upanel = str_replace('<%ROLE%>', $upanel_vars["ROLE"], $upanel);
      $upanel = str_replace('<%ACTION%>', $upanel_vars["ACTION"], $upanel);
      return str_replace('<%ACTIONTEXT%>', $upanel_vars["ACTIONTEXT"], $upanel);

   } //end _get_upanel


   /**
    * формирует, переводит и выводит на экран web-страницу контроллера
    *
    * @return ничего не возвращает
    */
   public function _display($data = array()) {
      
      // Execute all display plugins for search controller
      Plugin_Manager::getInstance()->execute('parent_controller', 'display', $this, $data);
      
      if (!$this->noframe) {
         $this->content["MENU"] = $this->menu->generate($this->role, $this->menu_item);
      }
      // set inline JS code:
      if(count($this->_javascriptInline)>0) {
          $jsInlineCode = implode(PHP_EOL, $this->_javascriptInline);
          $this->content['JAVASCRIPT'] .= '<script type="text/javascript">'.PHP_EOL.$jsInlineCode.PHP_EOL.'</script>';
      }
      
      if ($this->date_picker) {
         $this->load->model('locale_settings', '', TRUE);
         $this->content['JAVASCRIPT'] .= $this->locale_settings->date_picker();
         datepicker_vars($this->content);
      }
      
      $this->_save_session();
      $this->_save_temporary();
      $this->benchmark->mark('display_start');
      if ($this->role == 'admin') $this->_get_community();
      if ($this->role == 'admin') $this->_get_bytecity_links();
      if ($this->user_id && $this->role != 'guest'){
         $this->content["UPANEL"] = $this->_get_upanel();
         $this->content["USERPANEL"] = array(array());
         $this->content["USERPANEL2"] = array(array());
      }else{
         $this->content["USERPANEL"] = array();
         $this->content["USERPANEL2"] = array();
      }
      $this->benchmark->mark('display_end');
      $this->content["SITEURL"] = $this->site_url;
      $this->content["INDEXPAGE"] = $this->index_page;
      
      // is_admin
      $this->content["IS_ADMIN"] = 'admin' == $this->role ? array(array()) : array();
      
      /*Add tag <%CONTROLLER%>*/
      global $RTR;
      $this->content["CONTROLLER"] = $RTR->fetch_class();
      
      $this->content["ROLE"] = $this->role;
      $this->content["LOCALE"] = $this->locale;
      $this->content["FOOTER"] = $this->load->view('common/parent/footer.html', '', true);
      $this->content["HEADER"] = $this->load->view('common/parent/header.html', '', true);
      
      $this->content['BODY'] = $this->_translate($this->content['BODY']);
      $page = $this->parser->parse($this->template, $this->content, TRUE);
      $page = $this->_translate($page);

      $this->load->helper("button");
      $page = make_buttons($page);      
      
      header('Content-Type: text/html; charset=utf-8');
      header("Expires: " . gmdate("D, d M Y H:i:s") . " GMT"); // always modified
      header("Cache-Control: no-cache, must-revalidate, no-store, max-age=0"); // HTTP/1.1
      $this->output->set_output($page);
   } //end _display


   /**
    * завершает сессию работы пользователя с системой
    *
    * @param string $target страница на которую просиходит выход
    * @return ничего не возвращает
    */
   public function _logout($target = 'guest') {
      // Получаем текущего пользователя
      $id_user = 0;
      $auth_data = $this->session->userdata('auth_' . $this->role);
      if (is_array($auth_data) && isset($auth_data['userid']) && $auth_data['userid']) {
         $id_user = $auth_data['userid'];
      }
      $this->session->unset_userdata('auth_' . $this->role);

      // Убиваем все сессии текущего пользователя
      $this->load->model('roles');
      $all_roles = $this->roles->get_all_roles();
      foreach ($all_roles as $role) {
         /*
         if ('admin' == $role && $this->session->userdata('stored_session')) {
            // Пропускаем админовскую роль, с помошью которой был свершен "Login as"
            continue;
         }
         */
         $auth_data = $this->session->userdata('auth_' . $role);
         if (is_array($auth_data) && isset($auth_data['userid']) && $auth_data['userid'] == $id_user) {
            $this->session->unset_userdata('auth_' . $role);
         }
      }

      // Смотрим куда теперь податься
      if ($this->session->userdata('stored_session')) {
         // Возвращаемся в сохраненную сессию
         $session = $this->session->userdata('stored_session');
         $role = $session['role'];
         $this->session->set_userdata(array(
               'auth_' . $role => $session));
         $this->session->unset_userdata('stored_session');
         $controller = $session['controller'];
         //$this->session->unset_userdata('admincontroller');
         redirect('admin/' . $controller);
      } else {
         redirect($target);
      }
   } //end _logout


   /**
    * возвращает E-Mail текущего пользователя
    *
    * @return string E-Mail
    */
   public function get_email() {
      return $this->user_name;
   } //end get_email


   /**
    * возвращает базовый адрес поекта
    *
    * @return string адрес проекта
    */
   public function get_siteurl() {
      return $this->site_url;
   } //end get_siteurl


   /**
    * устанавливает закрывающееся уведомление о выполнении какого-либо действия
    *
    * @param string $message текст сообщения уведомления
    * @param string $message_type тип сообщения уведомления
    * @param string $stored нужно ли сохранять сообщение в сессии?
    * @return ничего не возвращает
    */
   public function _set_notification($message, $message_type = 'notification', $stored = false) {
      switch ($message_type) {
         case 'error':
            $template_name = 'error.html';
            break;
         default:
            $template_name = 'notification.html';
            break;
      }
      if ($stored) {
         $this->session->set_flashdata('notification_message', $message);
         $this->session->set_flashdata('notification_type', $message_type);
      }
      $this->content['NOTIFICATION'] = $this->parser->parse('common/' . $template_name, array(
            'MESSAGE' => __($message)));
   } //_set_notification

    
   /**
    * сохраняет сообщение чтобы на след. страницы устанавливать закрывающееся уведомление о выполнении какого-либо действия
    *
    * @param string $message текст сообщения уведомления
    * @param string $message_type тип сообщения уведомления
    * @return ничего не возвращает
    */
   public function _set_stored_notification($message, $message_type = 'notification') {
        $this->session->set_flashdata('notification_message', $message);
        $this->session->set_flashdata('notification_type', $message_type);
   }
   
   /**
    * Получение роли пользователя, открывающего контроллер
    *
    * @return string
    */
   public function get_role() {
      return $this->role;
   }

   /**
    * Получение идентификатора пользователя, открывающего контроллер
    *
    * @return int
    */
   public function get_user_id() {
      return $this->user_id;
   }
   
   /**
    * Добавить в content cвои тэги или переопределить текущие
    *
    * @param Array $values(name => value)
    */
   public function add_content($values){
      foreach($values as $key => $val){
         $this->content[$key] = $val;
      }  
   }
   
   /**
    * Generation blocks with links to bytecyte registration
    *
    * @return nothing
    */
   public function _get_bytecity_links() {
   		$this->load->model('feeds');
   		$bytecity_site_feed = $this->feeds->get_settings(1);
   		$bytecity_site = $bytecity_site_feed['affiliate_id'];
   		if (($this->role != 'admin') || (!empty($bytecity_site))) {
   			return;
   		}
   		
		$this->content['BYTECITY_LINK_TOP'] = $this->load->view('admin/bytecity/ajax.html', '', TRUE);
		$this->content['BYTECITY_LINK_FEED'] = $this->load->view('admin/bytecity/feed.html', '', TRUE);
	
   }
   
   public function _get_community() {
   		if (($this->role != 'admin')) {
   			return;
   		}
		$this->content['COMMUNITY_TOP'] = $this->load->view('admin/community/community.html', '', TRUE);
	
   }
}

?>