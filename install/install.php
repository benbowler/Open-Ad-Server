<?php

include_once './InstallOpenAdServer.php';

class InstallOpenAdServerNonRewrite extends InstallOpenAdServer {
    
   /**
    * Добавление index_page
    * @param string $text Текст файла конфигурации
    */
   protected function change_index_page(&$text) {
     $this->change_config_item($text, "index_page", "index.php");
   } //end change_index_page()
}

new InstallOpenAdServerNonRewrite();
?>
