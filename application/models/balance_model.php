<?php // -*- coding: UTF-8 -*-

if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
* Object Model
*
* @package      SmartPPC6 Project
* @copyright    Copyright (c) 2008, OrbitScripts
* @link         http://www.orbitscripts.com
* @author       OrbitScripts Team
* @version      1.0
* @created 15-Sep-2008 10:02:44
* @created 24-Sep-2007 10:02:44
*/
class Balance_Model extends CI_Model {
   protected $_id_admin;

   /**
    * Constructor
    */
   public function __construct() {
      parent::__construct();
   }

   /**
    * Установить идентификатор администратора
    * 
    * Эта функция используется для установки идентификатора администратора SmartPPC6
    * 
    * @param $id_admin идентификатор администратора
    * @return None
    */
   public function set_id_admin($id_admin) {
   }

   /**
    * Получить идентификатор администратора
    * 
    * Эта функция используется для получения идентификатора администратора SmartPPC6
    * 
    * @param None
    * @return $id_admin идентификатор администратора
    */
   public function get_id_admin($id_admin) {
   }

   /**
    * Выбрать необработанные данные статистики по кликам
    * 
    * Эта функция используется для получения данных необработанной статистики по кликам, для формирования
    * платежных транзакций. На основе этих функций формируются платежные транзакции со счета адвертайзера
    * на счет админа.
    * 
    * Выполняет запрос к БД, следующего содержания:
    *    SELECT
    *       t1.*,
    *       t2.bid,
    *       t2.id_group,
    *       t5.id_advertiser
    *    FROM stat_ads AS t1
    *       RIGHT JOIN ads AS t2
    *          ON t1.id_ads = t2.id_ads
    *       RIGHT JOIN groups AS t3
    *          ON t2.id_group = t3.id_group
    *       RIGHT JOIN campaigns AS t4
    *          ON t3.id_campaign = t4.id_campaign
    *       RIGHT JOIN advertiser AS t5
    *          ON t4.id_advertiser = t5.id_advertiser 
    *    WHERE t1.is_processed = FALSE AND t1.id_flow IS NULL;
    *
    *
    * @param None
    * @return Возвращает ассоциативный массив, следующей структуры:
    *    array(
    *       [0] => array(
    *          'id_ads'       => ,
    *          'stat_date'    => ,
    *          'clicks'       => ,
    *          'impressions'  => ,
    *          'bid'          => ,
    *          'id_group'     => ,
    *          'id_advertiser'=>
    *       ),
    *       ...
    *    );
    */
   function select_stat_ads() {
   }

   /**
    *
    */
   function make_flows_by_ads() {
   }

   /**
    * Выбрать необработанные данные статистики по фидам
    * 
    * Эта функция используется для получения данных необработанной статистики по фидам, для формирования
    * платежных транзакций. На основе этих функций формируются платежные транзакции со счета адвертайзера
    * на счет админа.
    * 
    * Выполняет запрос к БД, следующего содержания:
    *    SELECT
    *       t1.*,
    *       t2.name
    *    FROM stat_feeds AS t1
    *       RIGHT JOIN feeds AS t2
    *          ON t1.id_feed = t2.id_feed
    *    WHERE t1.is_processed = FALSE AND t1.id_flow IS NULL;
    *
    *
    * @param None
    * @return Возвращает ассоциативный массив, следующей структуры:
    *    array(
    *       [0] => array(
    *          'id_feed'      => ,
    *          'stat_date'    => ,
    *          'clicks'       => ,
    *          'impressions'  => ,
    *          'revenue'      =>
    *       ),
    *       ...
    *    );
    */
   function select_stat_feeds() {
   }

   /**
    *
    */
   function make_flows_by_feeds() {
   }

   /**
    *
    * Выполняет запрос к БД, следующего содержания:
    *    SELECT
    *       t1.*
    *    FROM money_flows AS t1
    *    WHERE t1.is_processed = FALSE AND t1.flow_program NOT IS NULL;
    *
    * После выборки, для каждой записи вызывается функция invoke_program(...);
    *
    */
   function make_flows_by_programs() {
   }


   /**
    * Обработать поток денег
    * 
    * Эта функция вызывается для обработки потока денег в таблице money_flow. Обработчики
    * программ могут быть реализованы как подгружаемые модули или "зашиты" внутри этой
    * функции.
    * 
    * @param $program - идентификатор программы
    * @param $flow_date - данные денежной транзакции.
    */
   protected function invoke_prorgamm($program,$flow_data) {
   }

   /**
    *
    * array(
    *    'id_receipt'      => идентификатор получателя денег,
    *    'id_expense'      => идентификатор отправителя денег,
    *    'flow_date'       => дата,
    *    'amount'          => сумма,
    *    'flow_program'    => программа (необязательное),
    *    'id_flow_parent'  => идентификатор "родительской" транзакции ,
    *    'id_ads'          => ,
    *    'id_feeds'        =>,
    *    'is_processed'    =>
    * )
    */
   function make_flow($flow_data) {
   }
}