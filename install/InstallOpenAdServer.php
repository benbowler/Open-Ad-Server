<?php

/**
 * InstallOpenAdServer.php - Installation Class for Open Ad Server
 */

include_once './xml.php';
include_once './check.php';

/**
 * Класс для инсталляции Open Ad Server
 * @author Владимир юдин
 */
class InstallOpenAdServer {

   /**
    * Общий шаблон для всех страниц инсталлятора
    * @var string
    */
   protected $common_template = "";
   
   /**
    * Номер текущего шага инсталляции
    * @var integer
    */
   protected $step = 1;
   
   /**
    * Объект XML-файла с параметрами инсталляции
    * @var XMLFile
    */   
   protected $xml = NULL;
   
   /**
    * Журнал инсталляции (массив с сообщениями о ходе инсталляции)
    * @var array
    */
   protected $install_log = array(); 
   
   /**
    * Путь к файлу конфигурации базы данных CodeIgniter
    * @var string
    */
   const DATABASE_CONFIG_FILE = "../application/config/database.php";

   /**
    * Путь к файлу конфигурации CodeIgniter
    * @var string
    */
   const CODEIGNITER_CONFIG_FILE = "../application/config/config.php";

   /**
    * URL для регистрации пользователя на ADMarket
    * @var string
    */
   const ADMARKET_REGISTRATION_URL = "http://bytecity.com/reg";
   
   /**
    * Конструктор класса (создание и запуск на выполнение)
    */
   public function __construct() {
      $this->common_template = $this->get_template("common"); 
      $this->run();
   } //end __construct()

   /**
    * Загрузка шаблона из файла
    * @param string $name Имя файла
    * @param string $extension Расширение файла (по-умолчанию 'html')
    * @return string Шаблон
    */   
   protected function get_template($name, $extension = "html") {
      $content = @file_get_contents("./$name.$extension");
      if ($content === FALSE) {
         die("Template file '$name' not found!");
      }  
      return $content;
   } //end get_template()
   
   /**
    * Получение POST-переменной
    * @param string $name Имя переменной
    * @param string $default Значение возвращаемое при отсутствии такой переменной
    * @return string Значение переменной
    */
   protected function get_post_variable($name, $default) {
      if (isset($_POST[$name])) {
         $value = $_POST[$name];
         unset($_POST[$name]);
         return $value;
      }
      return $default;
   } //end get_post_variable()
   
   /**
    * Парсинг текста
    * @param string $string Текст для парсинга
    * @param array $data Список значений для парсинга (тег => значение)
    */
   protected function parse($string, $data) {
      foreach ($data as $key => $value) {
         $string = str_replace("<%$key%>", $value, $string);
      }
      return $string;
   } //end parse()
   
   /**
    * Получение заголовка для текущего шага
    * @return string Заголовок шага
    */
   protected function get_step_title() {
      switch ($this->step) {
         case 1:
            return "Welcome to Orbit Open Ad Server Installation Wizard";
         case 2:
            return "Check server configuration";
         case 3:
            return "Database settings";
         case 4:
            return "Admin account settings";
         case 5:
            return "AdMarket registration";
         case 6:
            return "Check settings";
         case 7:
            return "Installation";
         default:
            die("Title: Unknown step $this->step!");
      }
   } //end get_step_title()
   
   /**
    * Проверка строки на корректный E-Mail адрес
    * @param strign $string Строка
    * @return boolean TRUE - если строка E-Mail
    */
   protected function is_email($string) {
      return true;
   } //end is_email()
   
   /**
    * Обработка шага 1
    * @return string Текст страницы шага
    */
   protected function step1() {
      $button = $this->get_post_variable("button", false);
      if ($button == "Next") {
         $this->step = 2;
         return $this->step2();
      }
      $this->xml->create();
      return $this->get_template("step1");
   } //end step1()
   
   /**
    * Обработка шага 2
    * @return string Текст страницы шага
    */
   protected function step2() {
      $button = $this->get_post_variable("button", false);
      switch ($button) {
         case "Prev":
            $this->step = 1; 
            return $this->step1();
         case "Next": 
            $this->step = 3;
            $_POST = NULL;
            return $this->step3();
      }
      $content = $this->get_template("step2");
      $checker = new Checker_PHP();
      $checker->run();       
      $data = array(   
         "CHECK_RESULT" => $checker->toString(),
         "DISABLED" => $checker->is_ok()?"":"disabled='disabled'" 
      );
      return $this->parse($content, $data);
   } //end step2()

   /**
    * Обработка шага 3
    * @return string Текст страницы шага
    */
   protected function step3() {
      $button = $this->get_post_variable("button", false);
      if ($button == "Prev") {
         $this->step = 2; 
         return $this->step2();
      }
      $content = $this->get_template("step3");
      
      $host = $this->xml->get("database", "host", "localhost");
      $login = $this->xml->get("database", "login", "root");
      $password = $this->xml->get("database", "password");
      $database = $this->xml->get("database", "database", "database");
      $agree = $this->xml->get("database", "agree");
      $host = $this->get_post_variable("host", $host);
      $login = $this->get_post_variable("login", $login);
      $password = $this->get_post_variable("password", $password);
      $password2 = $this->get_post_variable("password2", $password);
      $database = $this->get_post_variable("database", $database);
      if (!is_null($_POST)){$agree = "";}
      $agree = $this->get_post_variable("agree", $agree);
      $checked = '';
      if($agree){
         $checked = 'checked';
      }
      $this->xml->update("database", array(
          "agree" => $agree
      ));
      if (!is_null($_POST) && $host != "" && $login != "" /*&& $password != ""*/ && $password == $password2  && $database != "" && $agree != "") {
         $this->xml->update("database", array(
            "host" => $host,
            "login" => $login,
            "password" => $password,
            "database" => $database
         ));
         $_POST = NULL;
         $this->step = 4;
         return $this->step4();
      }
      $license = file_get_contents( "../license.txt" );
      $data = array(
         "HOST" => $host,
         "LOGIN" => $login,
         "PASSWORD" => $password,
         "PASSWORD2" => $password2,
         "DATABASE" => $database,
         "CHECKED" => $checked,
         "ERROR" => is_null($_POST)?"none":"block",
         "LICENSE" => $license
      );

      return $this->parse($content, $data);
   } //end step3()
   
   /**
    * Обработка шага 4
    * @return string Текст страницы шага
    */
   protected function step4() {
      $button = $this->get_post_variable("button", false);
      if ($button == "Prev") {
         $_POST = NULL;
         $this->step = 3; 
         return $this->step3();
      }
      $content = $this->get_template("step4");
      $login = $this->xml->get("admin", "login");
      $pasword = $this->xml->get("admin", "password");
      $login = $this->get_post_variable("login", $login);
      $password = $this->get_post_variable("password", $password);
      $password2 = $this->get_post_variable("password2", $password);
      if (!is_null($_POST) && $login != "" && $password != "" && $password == $password2 && $this->is_email($login)) {
         $this->xml->update("admin", array(
            "login" => $login,
            "password" => $password
         ));
         $_POST = NULL;
         $this->step = 5;
         return $this->step5();
      }
      $data = array(
         "LOGIN" => $login,
         "PASSWORD" => $password,
         "PASSWORD2" => $password2,
         "ERROR" => is_null($_POST)?"none":"block"
      );
      return $this->parse($content, $data);
   } //end step4()
   
   /**
    * Обработка шага 5
    * @return string Текст страницы шага
    */
   protected function step5() {
      $button = $this->get_post_variable("button", false);
      if ($button == "Prev") {
         $_POST = NULL;
         $this->step = 4; 
         return $this->step4();
      }
      $content = $this->get_template("step5");
      $login = $this->xml->get("admarket", "login");
      $pasword = $this->xml->get("admarket", "password");
      $login = $this->get_post_variable("login", $login);
      $password = $this->get_post_variable("password", $password);
      $password2 = $this->get_post_variable("password2", $password);
      if (!is_null($_POST) && $login != "" && $password != "" && $password == $password2 && $this->is_email($login)) {
         $this->xml->update("admarket", array(
            "login" => $login,
            "password" => $password
         ));
         $_POST = NULL;
         $this->step = 6;
         return $this->step6();
      } elseif (!is_null($_POST) && $login == "" && $password == "" && $password2 == "") {
         $this->xml->delete("admarket");
         $_POST = NULL;
         $this->step = 6;
         return $this->step6();
      } 
      $data = array(
         "LOGIN" => $login,
         "PASSWORD" => $password,
         "PASSWORD2" => $password2,
         "ERROR" => is_null($_POST)?"none":"block"
      );
      return $this->parse($content, $data);
   } //end step5()
   
   /**
    * Преобразование параметра инсталляции, пустая строка в виде черточки
    * @param string $string Значение параметра
    * @return string Обработанный параметр инсталляции
    */
   protected function empty_safe($string) {
      return ($string == "")?"-":$string;
   } //end safe()
   
   /**
    * Преобразование парметра-парроля, выводим в виде звездочек, пустая строка - черточка
    * @param string $string Значение парметра
    * @return string Обработанный параметр-пароль
    */
   protected function password($string) {
      return $this->empty_safe(str_repeat("*", strlen($string)));
   } //end password()
   
   /**
    * Обработка шага 6
    * @return string Текст страницы шага
    */
   protected function step6() {
      $button = $this->get_post_variable("button", false);
      switch ($button) {
         case "Prev":
            $this->step = 5; 
            $_POST = NULL;
            return $this->step5();
         case "Next": 
            $this->step = 7;
            $_POST = NULL;
            return $this->step7();
      }
      $content = $this->get_template("step6");
      $data = array(
         "DB_HOST" => $this->xml->get("database", "host"),
         "DB_LOGIN" => $this->xml->get("database", "login"),
         "DB_PASSWORD" => $this->password($this->xml->get("database", "password")),
         "DB_DATABASE" => $this->xml->get("database", "database"),
         "AC_LOGIN" => $this->xml->get("admin", "login"),
         "AC_PASSWORD" => $this->password($this->xml->get("admin", "password")),
         "AM_LOGIN" => $this->empty_safe($this->xml->get("admarket", "login")),
         "AM_PASSWORD" => $this->password($this->xml->get("admarket", "password"))      
      );
      return $this->parse($content, $data);
   } //end step6()
   
   /**
    * Обработка шага 7
    * @return string Текст страницы шага
    */
   protected function step7() {
      $button = $this->get_post_variable("button", false);
      if ($button == "Prev"){
         $this->step = 6; 
         $_POST = NULL;
         return $this->step6();
      }
      $success = $this->install();
      $content = $this->get_template("step7");
      $data = array(
         "INSTALL_LOG" => implode("\n", $this->install_log)
      );
      return $this->parse($content, $data);
   } //end step7()
   
   /**
    * Получение текста страницы для текущего шага
    * @return string Текст страницы шага
    */
   protected function get_step_content() {
      if ($this->step>=1 && $this->step<=7) {
         $function = "step".$this->step;
         return $this->$function();
      } else {
         die("Content: Unknown step $this->step!");
      }
   } //end get_step_content()

   /**
    * Подключение к базе данных MySQL
    * @throws Exception Текст ошибке при неудаче
    */   
   protected function database_connect() {
      $host = $this->xml->get("database", "host");
      $login = $this->xml->get("database", "login");
      $password = $this->xml->get("database", "password");
      $this->link = @mysql_connect($host, $login, $password);
      if (!$this->link) {
         throw new Exception("Could not connect to database: ".mysql_error());
      }
      $this->install_log[] = "Connected to database.";
   } //end database_connect()
   
   /**
    * Закрытие подключения к базе данных MySQL
    */
   protected function database_close() {
      mysql_close($this->link);
      $this->install_log[] = "Closed database connection.";
   } //end database_close()
   
   /**
    * Проверка базы данных на существание
    * @param string $name Имя базы данных
    * @throws Exception Текст ошибки при неудаче
    * @return bool TRUE - если база данных существует
    */
   protected function exist_database($name) {
      $result = mysql_query("SHOW DATABASES LIKE '$name'");
      if (!$result) {
         throw new Exception("Could not execute query: ".mysql_error()); 
      }
      return mysql_num_rows($result)>0;
   } //end exist_database()
   
   /**
    * Удаление существующей базы данных
    * @param string $name Имя базы данных
    * @throws Exception Текст ошибки при неудаче
    */
   protected function drop_database($name) {
      $result = @mysql_query("DROP DATABASE $name");
      if (!$result) {
         throw new Exception("Could not drop database: ".mysql_error()); 
      }
      $this->install_log[] = "Deleted existing database '$name'.";
   } //end drop_database()
   
   /**
    * Создание новой базы данных
    * @throws Exception Текст ошибки при неудаче
    */
   protected function create_database() {
      $database = $this->xml->get("database", "database");
      if ($this->exist_database($database)) {
         $this->drop_database($database);         
      }
      $result = mysql_query("CREATE DATABASE `$database` /*!40100 DEFAULT CHARACTER SET utf8 */"); 
      if (!$result) {
         throw new Exception("Could not create database: ".mysql_error()); 
      }
      $this->install_log[] = "Created database '$database'.";
      $result = mysql_query("USE `$database`");
      if (!$result) {
         throw new Exception("Could not use database: ".mysql_error()); 
      }
   } //end create_database()
   
   /**
    * Выполнение набора SQL-запросов из файла
    * @param string $filename Имя файла
    * @throws Exception Текст ошибки при неудаче
    */
   protected function execute_queries_from_file($filename) {
      $file = $this->get_template($filename, "sql");
      $file = str_replace("\r", '', $file);
      $queries = explode(";\n", $file);
      foreach ($queries as $query) {
         if ($query == "" || $query == "\n") continue;
         $result = mysql_query($query); 
         if (!$result) {
            throw new Exception("Could not execute query: ".mysql_error()); 
         }
      }            
   } //end execute_queries_from_file()
   
   /**
    * Создания набора таблиц в базе данных
    */
   protected function create_tables() {
      $this->execute_queries_from_file("structure"); 
      $this->install_log[] = "Created database tables.";
   } //end create_tables()
   
   /**
    * Инициализация данных в нужных таблицах базы данных
    */
   protected function populate_tables() {
      $this->execute_queries_from_file("data"); 
      $this->install_log[] = "Populated database tables.";
   } //end populate_tables()
   
   /**
    * Создание учетной записи администратора
    * @throws Exception Текст ошибки при неудаче
    */
   protected function create_admin_account() {
      $login = $this->xml->get("admin", "login");
      $password = $this->xml->get("admin", "password");
      $result = mysql_query("UPDATE entities SET e_mail='$login', password=MD5('$password') WHERE id_entity=1");
      $result &= mysql_query("UPDATE settings SET value='$login' WHERE name='SystemEMail'");
      if (!$result) {
         throw new Exception("Could not create admin account: ".mysql_error()); 
      }
      $this->install_log[] = "Created admin account.";
   } //end create_admin_account()
         
   /**
    * Изменение нужной записи в конфигурационном файле
    * @param string $text Текст файла конфигурации
    * @param string $name Имя параметра
    * @param string $value Новое значение параметра
    */
   protected function change_config_item(&$text, $name, $value) {
      $text = preg_replace('/(\[\''.$name.'\'\][\s]*=[\s]*")([^"]*)("[\s]*;)/', "\${1}$value\$3", $text); 
   } //end change_config_item()
   
   /**
    * Удаление перенаправления на инсталлер
    * @param string $text Текст файла конфигурации
    */
   protected function change_install_to_index(&$text) {
      $text = preg_replace("~\nheader\('Location: install/install.php'\);\nexit;\n~","", $text);
   } //end change_config_item()

   /**
    * Добавление index_page
    * @param string $text Текст файла конфигурации
    */
   protected function change_index_page(&$text) {
       //if mod_rewrite is active, it must be empty
       $this->change_config_item($text, "index_page", "");
   } //end change_config_item()

   /**
    * Модификация нужного файла конфигурации
    * @param string $file Имя изменяемого файла конфигурации
    * @param string $name Название файла конфигурации для вывода сообщений
    * @param array $data Набор изменяемых параметров файла конфигурации (имя => значение)
    * @throws Exception Текст ошибки при неудаче
    */
   protected function change_config_file($file, $name, $data) {      
      $text = @file_get_contents($file);
      if ($text === FALSE) {
         throw new Exception("Could not read $name config file.");
      }
      foreach ($data as $key => $value) {
         $this->change_config_item($text, $key, $value);
      }      
      $this->change_install_to_index($text);
      $this->change_index_page($text);
      $result = @file_put_contents($file, $text); 
      if ($result === FALSE) {
         throw new Exception("Could not write $name config file.");
      }
      $this->install_log[] = "Modified $name config file.";
   } //end change_config_file()
      
   /**
    * Модификация файла конфигурации базы данных CodeIginiter
    */
   protected function database_config() {
      $this->change_config_file(
         self::DATABASE_CONFIG_FILE, 
         "database",
         array(
            "hostname" => $this->xml->get("database", "host"),
            "username" => $this->xml->get("database", "login"),
            "password" => $this->xml->get("database", "password"),
            "database" => $this->xml->get("database", "database")
         ) 
      );      
   } //end database_config()

   /**
    * Возвращает базовый URL системы в которой запущен инсталлятор
    */
   protected function get_base_url() {
      return str_replace("install/install.php", "", $_SERVER['HTTP_REFERER']);
   } //end get_base_url()
   
   /**
    * Возвращает имя домена с которого запущен инсталлятор
    */
   protected function get_domain() {
      return $_SERVER['SERVER_NAME'];
   } //end get_domain()
   
   /**
    * Модификация файла конфигурации CodeIginiter
    */
   protected function codeigniter_config() {
      $this->change_config_file(
         self::CODEIGNITER_CONFIG_FILE, 
         "CodeIgniter",
         array(
            "base_url" => $this->get_base_url()
         ) 
      );      
   } //end codeigniter_config()

   /**
    * Создание нового аккаунта для AdMarket
    */
   protected function create_admarket_account() {
      $login = $this->xml->get("admarket", "login");
      $pasword = $this->xml->get("admarket", "password");
      if ($login == "") {
         return;
      }
      $postdata = http_build_query(
         array(
            'email' => $login, 
            'password' => $pasword,
            'site_url' => $this->get_base_url(),
            'site_name' => $this->get_domain()
         ));      
      $opts = array(
         'http' => array(
            'method' => 'POST', 
            'header' => 'Content-type: application/x-www-form-urlencoded', 
            'content' => $postdata));      
      $context = stream_context_create($opts);      
      $result_json = @file_get_contents(self::ADMARKET_REGISTRATION_URL, false, $context);
      if ($result_json === FALSE) {
         $this->install_log[] = "Can't create AdMarket account: Can't read AdMarket URL.";
         return;
      }
      $result = json_decode($result_json);      
      if($result->status == "error"){
         $this->install_log[] = "Can't create AdMarket account: ".$result->data;
         return;         
      }else{
         $flag = mysql_query("UPDATE feeds SET affiliate_id_1='".$result->data."' WHERE name='bytecity'");
         if($flag === FALSE){
            $this->install_log[] = "Can't update feed settings: ";
         }
      }
      
      $this->install_log[] = "Created AdMarket account.";
   } //end create_admarket_account()
   
   /**
    * Процедура инсталляции Open AdServer
    */
   protected function install() {
      try {
         $this->database_connect(); 
         $this->create_database(); 
         $this->create_tables(); 
         $this->populate_tables(); 
         $this->create_admin_account();
         $this->database_config();  
         $this->codeigniter_config();
         $this->create_admarket_account();  
         $this->database_close(); 
      }  catch (Exception $message) {
         $this->install_log[] = $message;
         $this->install_log[] = "Installation aborted.";
         return false;
      } 
      $this->install_log[] = "Congratulations! Orbit Open AdServer successfully installed!";
      $this->xml->clean();
      return true;
   } //end install()
   
   /**
    * Запуск класса на выполнение
    */
   public function run(){ 
      $this->step = $this->get_post_variable("step", 1);
      $this->xml = new XMLFile();
      $common_data = array(
         'STEPCONTENT' => $this->get_step_content(),
         'STEPNUMBER' => $this->step,
         'STEPTITLE' => $this->get_step_title()
      );      
      echo $this->parse($this->common_template, $common_data);
   } //end run()

} //end Class InstallOpenAdServer

//end InstallOpenAdServer.php file