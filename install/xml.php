<?php

/**
 * Класс для работы с XML-файлов хранящем настройки инсталляции
 * @author Владимир Юдин
 */

class XMLFile {

   /**
    * Путь к XML-файлу
    * @var string
    */
   const XML_FILE = "./install.xml"; 
   
   /**
    * Копия XML-файла в памяти
    * @var SimpleXMLElement 
    */
   protected $xml = NULL; 
   
   /**
    * Конструктор класса (загрузка XML-файла в память)
    */
   public function __construct() {
      $this->load();
   } //end __construct()

   /**
    * Создание пустого XML-файла
    */
   public function create(){
      $xmlstr = "<?xml version='1.0' standalone='yes'?>
         <install>
         </install>";
      if (!$this->xml) {
         $this->xml = simplexml_load_string($xmlstr);
         $this->save();
      }
   } //end create()

   /**
    * Создание пустого XML-файла
    */
   public function clean(){
      $xmlstr = "<?xml version='1.0' standalone='yes'?>
         <install>
         </install>";
      $this->xml = simplexml_load_string($xmlstr);
      $this->save();
   } //end clean()


   /**
    * Загрузка существующего XML-файла
    */
   protected function load(){
      if (!file_exists(XMLFile::XML_FILE)) {
         return $this->create();
      } 
      $this->xml = @simplexml_load_file(XMLFile::XML_FILE);
      if ($this->xml === FALSE) {
         $this->create();
      }
   } //end load()

   /**
    * Сохрание текущего XML-файла
    */
   protected function save(){
      $xmlstr = $this->xml->asXML();
      $result = @file_put_contents(XMLFile::XML_FILE, $xmlstr);
      if ($result === FALSE) {
         die("Can't create XML file!");
      }
   } //end save()
   
   /**
    * Модификация ветви XML-файла
    * @param string $branch Имя модифицируемой ветви
    * @param array $params Список параметров ветви (имя => значение)
    */
   public function update($branch, $params){
      if (!isset($this->xml->$branch)) {
         $this->xml->addChild($branch);
      }
      foreach ($params as $name => $value) {
         if (!isset($this->xml->$branch->$name)) {
            $this->xml->$branch->addChild($name, $value);
         } else {
            $this->xml->$branch->$name = $value; 
         }
      }
      $this->save();   
   } //end update()
   
   /**
    * Удаление ветви XML-файла
    * @param string $branch Имя удаляемой ветви
    */
   public function delete($branch) {
      if (!isset($this->xml->$branch)) {
         unset($this->xml->$branch);
      }
      $this->save();
   } //end delete()
   
   /**
    * 
    * Получение нужного параметра, нужной ветви
    * @param string $branch Имя требуемой ветви
    * @param string $name Имя требуемого параметра
    * @param string $default Значение возвращаемое при отсутствии параметра в XML-файле
    */
   public function get($branch, $name, $default = "") {
      return isset($this->xml->$branch->$name)?(string)$this->xml->$branch->$name:$default;
   } //end get()
      
} //end Class XMLFile

//end xml.php file