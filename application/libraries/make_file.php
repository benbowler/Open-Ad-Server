<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

require_once APPPATH . 'libraries/writeexcel/Writer.php';
/**
* Библиотека создания файла на основе двумерного массива
* @author Владимир Юдин
* @project SmartPPC6
* @version 1.0.0
*/
class Make_file {

   /**
   * конструктор класса
   *
   * @return ничего не возвращает
   */ 
   public function Make_file() {
   } //end Make_file
 
   /**
   * создает файл в формате CSV на основе переданных данных
   *
   * @param array $data содержимое ячеек таблицы
   * @return string содержимое файла
   */
   public  function csv(&$data) {
      $out = '';
      foreach ($data as $row) {
         foreach ($row as $column) {
            $out .= $column.";";
         }
         $out .= "\n";
      }
      return $out;
   } //end csv
   
   /**
   * создает файл в формате XLS (Microsoft Excel) на основе переданных данных
   *
   * @param array $data содержимое ячеек таблицы
   * @param string $name имя отчета
   * @return string содержимое файла
   */
   public  function xls(&$data, $name) {
      $fileName = date("Y-m-d_H-i-s");
      $workBook = new Spreadsheet_Excel_Writer();
      $workBook->setTempDir(BASEPATH . 'cache/');
      $workBook->setVersion(8);
      $workBook->send(__("report")."_$fileName.xls");
      $formatBold =& $workBook->addFormat();
      $formatBold->setBold();
      $formatTitle =& $workBook->addFormat();
      $formatTitle->setBold();
      $formatTitle->setColor('black');
      $formatTitle->setPattern(1);
      $formatTitle->setFgColor('gray');
      $formatTitle->setAlign('merge');
      $workSheet =& $workBook->addWorksheet('Report');
      $workSheet->setInputEncoding('utf-8');
      $row_count = 0;
      foreach ($data as $row) {
         $col_count = 0;
         foreach ($row as $column) {
            $workSheet->write($row_count, $col_count, $column, $row_count ? $formatBold : $formatTitle);
            $col_count++;
         }
         $row_count++;
      }
//      ширина столбцов
//      $workSheet->setColumn(0, 0, 30);
//      $workSheet->setColumn(2, 2, 30);
      $workBook->close();      
   } //end xls
      
   /**
   * возвращает содержимое файла полученного
   *
   * @param array $data содержимое ячеек таблицы в формате ((col1, col2, ..., colN), ...)
   *     первая строка содержит заголовки столбцов таблицы
   * @param string $type тип файла на выходе, по умолчанию - 'csv'
   * @param string $name наименование отчета, по умолчанию - 'Report'
   * @return string содержимое файла
   */
   public function create(&$data, $type = 'csv', $name = 'Report') {
      switch ($type) {
         case 'csv': return $this->csv($data);
         case 'xls': return $this->xls($data, $name);
         default: return '';
      }
   } //end create      
         
} //end Make_file

?>