<?php
if (!defined('BASEPATH'))
   exit('No direct script access allowed');

/**
 * модель для работы с сущностью пользователя
 * доступные методы:
 * login($email, $password)      по e-mail'у и паролю возвращает код и список ролей пользователя
 * get_roles($id)                по коду пользователя возвращает список ролей пользователя
 * get_guest()                   Получить гостя
 * password_recovery($email)     по e-mail'у пользователя возвращает код для восстанавлению пароля
 * password_code($code)          по коду восстанавления пароля возвращает код пользователя системы
 * set_password($id, $password)  установка нового пароля пользователя системы
 * sign_up($sign_up_data, $role) регистрация нового пользователя в системе
 * read($id_entity)              по коду пользователя считывает все его данные
 * save($id_entity, $fields)     обновляет данные заданного пользователя
 * password($id_entity)          под коду пользователя возвращает md5-хэш его пароля
 * 
 * @author Владимир Юдин
 * @project SmartPPC6
 * @version 1.0.0
 */
class Entity extends CI_Model {
   
   //для того чтобы не дублировать запросы делаем кэш ролей  
   protected $_rolesCache = array();
   
   //для того чтобы не дублировать запросы делаем кэш email  
   protected $_idByEmailCache = array();
   
   //для того чтобы не дублировать запросы делаем кэш всех данных пользователя  
   protected $_entityCache = array();
   
   //для того чтобы не дублировать запросы делаем кэш имен
   protected $_nameByIdCache = array();
   
   //для того чтобы не дублировать запросы делаем кэш имен и емейлов   
   protected $_nameAndEmailByIdCache = array();
   
   //для того чтобы не дублировать запросы делаем кэш id guest
   protected $_guestCache = null;
   
   public function __construct() {
      parent::__construct();
   }
   
   /**
    * по e-mail'у и md5 пароля возвращает код и список ролей пользователя
    *  
    * @param string $email электронный адрес пользователя
    * @param string $password md5 хэш пароля пользователя
    * @return array массив с данными пользователя или NULL если пользователь не найден
    *           id    код пользователя 
    *           roles массив ролей пользователя
    */
   public function login($email, $password, $role) {
      $res = $this->db
         ->select('entities.id_entity, roles.name AS roles, entity_roles.status')
         ->join('entity_roles', 'entities.id_entity=entity_roles.id_entity')
         ->join('roles', 'entity_roles.id_role=roles.id_role')
         ->where(array(
            'e_mail' => strtolower($email), 
            'password' => $password,
            'roles.name' => $role
            ))->get('entities');
      //$this->db->where('status', 'active');  
      

      $user = NULL;
      foreach ($res->result() as $row) {
         $user["id"] = $row->id_entity;
         $user["roles"][] = $row->roles;
         $user['status'] = $row->status;
      }
      return $user;
   } //end login
   

   /**
    * по коду пользователя возвращает список ролей пользователя
    *  
    * @param int $id код пользователя
    * @return array список ролей пользователя
    */
   public function get_roles($id, $status = NULL) {
      if (isset($this->_rolesCache[$id])) {
         return $this->_rolesCache[$id];
      }
      $roles = array();
      $this->db->select('name')
                      ->join('roles', 'entity_roles.id_role=roles.id_role')
                      ->where('id_entity', $id);
      if (!is_null($status)) {
         $this->db->where('status', $status);
      }
      $res = $this->db->get('entity_roles');
      foreach ($res->result() as $row) {
         $roles[] = $row->name;
      }
      $this->_rolesCache[$id] = $roles;
      return $roles;
   } //end get_roles
   

   /**
    * получаем пользователя с ролью гость
    *  
    * @return int пользователя с ролью гость (первый попавшийся)
    */
   public function get_guest() {
      if (null != $this->_guestCache) {
         return $this->_guestCache;
      }
      $roles = array();
      $res = $this->db->select()
                      ->from('entities e')
                      ->join('entity_roles er', 'er.id_entity = e.id_entity')
                      ->join('roles r', 'r.id_role = er.id_role')
                      ->where('r.name', 'guest')
                      ->limit(1)
                      ->get();
      if ($res->num_rows() > 0) {
         $this->_guestCache = $res->row();
         return $this->_guestCache;
      }
      return null;
   } //end    
   

   /**
    * Проверка на нахождение у пользователя определенной роли
    *
    * @param int $id
    * @param string $role
    * @return bool
    */
   public function has_role($id, $role, $status = NULL) {
      $roles = $this->get_roles($id, $status);
      if (in_array($role, $roles)) {
         return true;
      }
      return false;
   }
   
   /**
    * возвращает код пользователя по его E-Mail
    *
    * @param string $email E-Mail пользователя
    * @return integer код пользователя или NULL, если пользователь не найден
    */
   public function get_id_by_email($email) {
      if (isset($this->_idByEmailCache[$email])) {
         return $this->_idByEmailCache[$email];
      }
      
      $res = $this->db->select('id_entity')
                      ->where('e_mail', strtolower($email))
                      ->get('entities');
      if (!$res->num_rows()) {
         return NULL;
      }
      $row = $res->row();
      $this->_idByEmailCache[$email] = $row->id_entity;
      return $row->id_entity;
   } //end get_id_by_email   
   

   /**
    * по e-mail'у пользователя возвращает код для восстанавлению пароля
    *  
    * @param string $email электронный адрес пользователя
    * @return string код для восстановления пароля (высылается пользователю в ссылке),
    *     если NULL - пользователь в системе не найден
    */
   public function password_recovery($email) {
      $id = $this->get_id_by_email($email);
      if (is_null($id)) {
         return NULL;
      }
      $time = gettimeofday();
      $code = md5($id . serialize($time));
      $this->db->where('id_entity', $id)->update('entities', array(
            'password_recovery' => $code));
      return $code;
   } //end password_recovery
   

   /**
    * по коду восстанавления пароля возвращает код пользователя системы
    *
    * @param string $code код восстанавления пароля
    * @return int код пользователя системы или NULL, если код восстанавления не найден
    */
   public function password_code($code) {
      $res = $this->db->select('id_entity')->get_where('entities', array(
            'password_recovery' => $code));
      if ($res->num_rows()) {
         $row = $res->row();
         $id = $row->id_entity;
         return $id;
      } else {
         return NULL;
      }
   } //end password_code
   

   /**
    * установка нового пароля пользователя системы
    *
    * @param int $id код пользователя
    * @param string $password MD5 хэш нового пароля пользователя
    * @return bool возвращает истину если пароль успешно изменен
    */
   public function set_password($id, $password) {
      $this->db->where('id_entity', $id)->update('entities', array(
            'password' => $password));
      return TRUE;
   } //end set_password
   

   /**
    * регистрация нового пользователя в системе
    *
    * @param array $sign_up_data массив с данными пользователя для регистрации
    * @param string $role роль регистрируемого пользователя в системе
    * @param bool $need_activation флаг необходимости активации пользователя после регистрации
    * @param array $fields_from_role_table список полей которые хранятся в таблице 
    *                                      роли такой как advertiser, 
    *                                      member, paublisher и т.д.
    *                                      формат 'название поля' => 'тип для type_to_str()'
    * @return int код зарегистрированного пользователя, NULL при неудаче
    */
   public function sign_up($sign_up_data, $role, $need_activation, $fields_from_role_table = array()) {
      $password = md5($sign_up_data["password"]);
      $status = $need_activation ? "activation" : "active";
      $this->db->insert('entities', array(
            'name' => $sign_up_data["name"], 
            'e_mail' => strtolower($sign_up_data["mail"]), 
            'password' => $password, 
            //'status' => $status,
            'creation_date' => type_to_str(time(), 'databasedatetime')));
      $id_entity = $this->db->insert_id();
      if (!$id_entity) {
         return NULL;
      }
      $extra_fields = array(
            "country", 
            "timezone", 
            "city", 
            "address", 
            "zip_postal", 
            "phone", 
            "state");
      foreach ($extra_fields as $name) {
         if ($sign_up_data[$name]) {
            $this->db->select('id_contact_field');
            $res = $this->db->get_where('contact_fields', array(
                  'name' => $name));
            if ($res->num_rows()) {
               $row = $res->row();
               $this->db->insert('contacts', array(
                     'id_entity' => $id_entity, 
                     'id_contact_field' => $row->id_contact_field, 
                     'value' => $sign_up_data[$name]));
            }
         }
      }
      
      $values = array(
            "id_entity_$role" => $id_entity);
      if (sizeof($fields_from_role_table) > 0) {
         
         foreach ($fields_from_role_table as $field => $type) {
            if ($sign_up_data[$field]) {
               $values[$field] = type_to_str($sign_up_data[$field], $type);
            }
         }
      }
      
      $this->db->insert("{$role}s", $values);
      
      $this->db->select('id_role');
      $res = $this->db->get_where('roles', array(
            'name' => $role));
      if ($res->num_rows()) {
         $row = $res->row();
         $this->db->insert('entity_roles', array(
               'id_entity' => $id_entity, 
               'id_role' => $row->id_role,
               'status' => $status
         ));
      }
      return $id_entity;
   } //end sign_up
   

   /**
    * по коду пользователя считывает все его данные
    *  
    * @param int $id_entity код пользователя
    * @return array массив с данными пользователя в формате имя_поля => значение
    */
   public function read($id_entity) {
      if (isset($this->_entityCache[$id_entity])) {
         return $this->_entityCache[$id_entity];
      }
      
      $this->db->select('name, e_mail AS mail, password');
      $res = $this->db->get_where('entities', array(
            'id_entity' => $id_entity));
      if (!$res->num_rows()) {
         return NULL;
      }
      
      $fields = $res->row_array();
      $res = $this->db->select('name, value')
                      ->join('contact_fields', 'contacts.id_contact_field=contact_fields.id_contact_field')
                      ->where('id_entity', $id_entity)
                      ->get('contacts');
      foreach ($res->result() as $row) {
         $fields[$row->name] = $row->value;
      }
      $this->_entityCache[$id_entity] = $fields;
      return $fields;
   } //end read   
   

   /**
    * по коду пользователя считывает все его данные из таблици его роли (advertisers или publishers или member и т.д.)
    *  
    * @param int $id_entity код пользователя
    * @param string $role роль
    * @return array массив с данными пользователя в формате имя_поля => значение
    */
   public function read_role_data($id_entity, $role) {
      $this->db->select('*', false);
      $res = $this->db->get_where($role . 's', array(
            'id_entity_' . $role => $id_entity));
      if (!$res->num_rows()) {
         return NULL;
      }
      
      $fields = $res->row_array();
      return $fields;
   } //end read_role_data  
   

   /**
    * обновляет данные пользователя
    *
    * @param int $id_entity код пользователя
    * @param array $fields список полей для обновления
    * @param $role роль не объязательно. Нужно для $fields_from_role_table
    * @param array $fields_from_role_table список полей которые хранятся в таблице 
    *                                      роли такой как advertiser, 
    *                                      member, paublisher и т.д.
    *                                      формат 'название поля' => 'тип для type_to_str()'
    * @return ничего не возвращает
    */
   public function save($id_entity, $fields, $role = null, $fields_from_role_table = array()) {
      $this->db->where('id_entity', $id_entity)->update('entities', array(
            'name' => $fields["name"], 
            'e_mail' => strtolower($fields["mail"])));
      $extra_fields = array(
            "country", 
            "timezone", 
            "city", 
            "address", 
            "zip_postal", 
            "phone", 
            "state");
      foreach ($extra_fields as $name) {
         $this->db->select('id_contact_field');
         $res = $this->db->get_where('contact_fields', array(
               'name' => $name));
         if ($res->num_rows()) {
            $row = $res->row();
            $id_contact_field = $row->id_contact_field;
            
            $this->db->delete('contacts', array(
                  'id_entity' => $id_entity, 
                  'id_contact_field' => $id_contact_field));
            if ($fields[$name]) {
               $this->db->insert('contacts', array(
                     'id_entity' => $id_entity, 
                     'id_contact_field' => $id_contact_field, 
                     'value' => $fields[$name]));
            }
         }
      }
      
      if (sizeof($fields_from_role_table) > 0) {
         $values = array();
         foreach ($fields_from_role_table as $field => $type) {
            if ($fields[$field]) {
               $values[$field] = type_to_str($fields[$field], $type);
            }
         }
         if (sizeof($values) > 0) {
            $this->db->where('id_entity_' . $role, $id_entity)->update($role . 's', $values);
         }
      }
   
   } //end save   
   

   /**
    * под коду пользователя возвращает md5-хэш его пароля
    *
    * @param int $id_entity код пользователя
    * @return string md5 хэш пароля пользователя, "" если не найден
    */
   public function password($id_entity) {
      $res = $this->db->select('password')->get_where('entities', array(
            'id_entity' => $id_entity));
      if ($res->num_rows()) {
         return $res->row()->password;
      } else {
         return "";
      }
   } //end password
   

   /**
    * по коду пользователя возвращает его имя
    *  
    * @param int $id код пользователя
    * @return string|null имя пользователя
    */
   public function get_name($id_entity) {
      if (isset($this->_nameByIdCache[$id_entity])) {
         return $this->_nameByIdCache[$id_entity];
      }
      $res = $this->db->select('name')->get_where('entities', array(
            'id_entity' => $id_entity));
      if ($res->num_rows()) {
         $this->_nameByIdCache[$id_entity] = $res->row()->name;
         return $res->row()->name;
      } else {
         $this->_nameByIdCache[$id_entity] = null;
         return null;
      }
   } //end get_name
   

   /**
    * по коду пользователя возвращает его имя и e-mail
    *  
    * @param int $id_entity код пользователя
    * @return array|null имя пользователя
    */
   public function get_name_and_mail($id_entity) {
      if (isset($this->_nameAndEmailByIdCache[$id_entity])) {
         return $this->_nameAndEmailByIdCache[$id_entity];
      }
      $res = $this->db->select('name, e_mail')->get_where('entities', array(
            'id_entity' => $id_entity));
      if ($res->num_rows()) {
         $this->_nameAndEmailByIdCache[$id_entity] = $res->row();
         return $res->row();
      } else {
         $this->_nameAndEmailByIdCache[$id_entity] = null;
         return null;
      }
   } //end get_name
   

   /**
    * Получение списка пользователей для результата поиска по имени или адресу
    *
    * @param string $search строка поиска
    * @param string $role роль пользователей, '' - искать для всех ролей
    * @return array|null массив типа (id_entity, name, e_mail), либо null если результатов нет
    */
   public function get_list_by_name_or_mail($search = '', $role = '') {
      if ('' == $search) {
         return null;
      }
      
	  $second_role="admin";
	  
      $this->db->select('entities.id_entity, entities.name, entities.e_mail');
      $where = '';
      if ('' != $role) {
         $this->db->join('entity_roles', 'entity_roles.id_entity = entities.id_entity')->join('roles', 'roles.id_role = entity_roles.id_role');
		 if ('publisher' == $role) {
			$where = '(roles.name = ' . $this->db->escape($role) . ' OR roles.name = ' . $this->db->escape($second_role) . ') AND ';
		 } else {
			$where = 'roles.name = ' . $this->db->escape($role) . ' AND ';
		 }
      }
      $where .= "(entities.name LIKE " . $this->db->escape('%' . $search . '%') . " OR entities.e_mail LIKE " . $this->db->escape('%' . $search . '%') . ")";
      $query = $this->db->where($where, null, false)->order_by('entities.e_mail', 'asc')->get('entities');
      
      if ($query->num_rows()) {
         return $query->result_array();
      } else {
         return null;
      }
   }
   
   /**
    * возвращает текущий баланс пользовател-я
    *
    * @param int $id_entity код пользователя
    * @return float баланс пользователя, NULL - если пользователь не найден
    */
   public function ballance($id_entity) {
      $res = $this->db->select('ballance')->get_where('entities', array(
            'id_entity' => $id_entity));
      if (!$res->num_rows()) {
         return NULL;
      }
      $row = $res->row();
      return $row->ballance;
   } //end ballance
   
   /**
    * возвращает текущий бонус пользовател-я
    *
    * @param int $id_entity код пользователя
    * @return float бонус пользователя, NULL - если пользователь не найден
    */
   public function bonus($id_entity) {
      $res = $this->db->select('bonus')->get_where('entities', array(
            'id_entity' => $id_entity));
      if (!$res->num_rows()) {
         return NULL;
      }
      $row = $res->row();
      return $row->bonus;
   } //end ballance
   

   /**
    * устанавливает новое значение баланса для пользователя
    *
    * @param integer $id_entity код учетной записи пользователя
    * @param float $ballance новое значение баланса
    * @return ничего не возвращает
    */
   public function set_ballance($id_entity, $ballance) {
      $this->db->where(array(
            'id_entity' => $id_entity))->update('entities', array(
            'ballance' => $ballance));
   } //end set_ballance
   

   /**
    * Метод изменения баЛЛанса пользователя
    *
    * @param int $id_entity идентификатор пользователя
    * @param float $money на сколько нужно изменить баЛЛанс
    */
   public function change_ballance($id_entity, $money) {
      $sql = "
         UPDATE
            entities
         SET
            ballance = ballance + ?
         WHERE
            id_entity = ?
         LIMIT
            1
      ";
      $this->db->query($sql, array(
            $money, 
            $id_entity));
   }
   
   /**
    * Метод изменения баЛЛанса пользователя
    *
    * @param int $id_entity идентификатор пользователя
    * @param float $money на сколько нужно изменить баЛЛанс
    */
   public function change_bonus($id_entity, $money) {
      $sql = " 
         UPDATE 
            entities 
         SET 
            bonus = bonus + ? 
         WHERE 
            id_entity = ? 
         LIMIT 
            1 
      ";
      $this->db->query($sql, array(
            $money,
            $id_entity));
   }
   
   
   /**
    * Убирание некой суммы дененг с баланса пользователя
    *
    * @param int $id_entity
    * @param float $money
    */
   public function subtract_money($id_entity, $money) {
      // Для начала проверяем есть ли деньги на бонусе
      $bonus = $this->bonus($id_entity);
      if (0 < $bonus) {
         $chargeBonus = min($money, $bonus);
         $this->change_bonus($id_entity, -1 * $chargeBonus);
         $money = $money - $chargeBonus;
      }
      if (0 < $money) {
         $this->change_ballance($id_entity, -1 * $money);
      }
   }
   
   /**
    * Добавление некой суммы дененг на баланс пользователя
    * Алиас для функции change_ballance
    *
    * @param int $id_entity
    * @param float $money
    */
   public function add_money($id_entity, $money) {
      $this->change_ballance($id_entity, $money);
   }
   
   /**
    * возвращает статистику заданного пользователя за указанный период
    *
    * @param integer $id_entity код пользователя
    * @param date $from дата начала периода
    * @param date $to дата конца периода
    * @return array массив со значениями статистики (spent, impressions, clicks)
    */
   public function get_stat($id_entity, $from, $to) {
      $stat = array(
          'spent' => 0, 
          'impressions' => 0, 
          'clicks' => 0
      );
      $res = $this->db->
      	 select_sum('spent')->
      	 select_sum('impressions')->
      	 select_sum('clicks')->
      	 where('id_entity_advertiser', $id_entity)->
      	 where('stat_date >=', type_to_str($from, 'databasedate'))->
      	 where('stat_date <=', type_to_str($to, 'databasedate'))->
      	 group_by('id_entity_advertiser')->
      	 get('stat_advertisers');
      if ($res->num_rows()) {
         $row = $res->row();
         
         $stat['spent'] = $row->spent;
         $stat['impressions'] = $row->impressions;
         $stat['clicks'] = $row->clicks;
      }
      return $stat;
   } //end get_stat
   

   /**
    * возвращает подробную статистику (на каждый день) заданного адвертайзера за указанный период
    *
    * @param integer $id_entity код пользователя
    * @param date $from дата начала периода
    * @param date $to дата конца периода
    * @return array массив с массивами статистики (spent, impressions, clicks, ctr)
    */
   public function get_line_stat($id_entity, $from, $to) {
      $stat = array(
          'spent' => array(), 
          'impressions' => array(), 
          'clicks' => array(), 
      	 'ctr' => array()
      );
      $this->db->select('
      	 spent,
      	 impressions,
      	 clicks,
      	 UNIX_TIMESTAMP(stat_date) AS stat_date,'
      )->where(array(
          'id_entity_advertiser' => $id_entity, 
          'stat_date >=' => type_to_str($from, 'databasedate'), 
      	 'stat_date <=' => type_to_str($to, 'databasedate')
      ));
      $res = $this->db->get('stat_advertisers');
      $cur_date = $from;
      foreach ($res->result() as $row) {
         $stat_date = $row->stat_date;
         while ($cur_date < $stat_date) {
            $stat['spent'][] = 0.0;
            $stat['impressions'][] = 0;
            $stat['clicks'][] = 0;
            $stat['ctr'][] = 0.0;
            $cur_date += 24 * 60 * 60;
         }
         $stat['spent'][] = $row->spent;
         $stat['impressions'][] = $row->impressions;
         $stat['clicks'][] = $row->clicks;
         $stat['ctr'][] = ($row->impressions) ? $row->clicks * 100 / $row->impressions : 0;
         $cur_date += 24 * 60 * 60;
      }
      while ($cur_date <= $to) {
         $stat['spent'][] = 0.0;
         $stat['impressions'][] = 0;
         $stat['clicks'][] = 0;
         $stat['ctr'][] = 0.0;
         $cur_date += 24 * 60 * 60;
      }
      return $stat;
   } //end get_line_stat
   

   /**
    * возвращает подробную статистику (на каждый день) заданного паблишера за указанный период
    *
    * @param date $from дата начала периода
    * @param date $to дата конца периода
    * @return array массив с массивами статистики (revenue, impressions, clicks, ctr)
    */
   public function publisher_get_line_stat($from, $to) {
      $stat = array(
            'revenue' => array(), 
            'impressions' => array(), 
            'clicks' => array(), 
            'ctr' => array()
      );
      
      $this->db->select('
      	SUM(impressions) AS impressions, 
      	SUM(clicks) AS clicks, 
      	UNIX_TIMESTAMP(stat_date) AS stat_date, 
      	SUM(earned_admin) AS earned_admin
      ');
      $where = array(
            'stat_date >=' => type_to_str($from, 'databasedate'), 
            'stat_date <=' => type_to_str($to, 'databasedate'));

      $this->db->where($where)->group_by('stat_date');
      $res = $this->db->get('stat_sites');

      $cur_date = $from;
      foreach ($res->result() as $row) {
         $stat_date = $row->stat_date;
         while ($cur_date < $stat_date) {
            $stat['revenue'][] = 0.0;
            $stat['impressions'][] = 0;
            $stat['clicks'][] = 0;
            $stat['ctr'][] = 0.0;
            $cur_date += 24 * 60 * 60;
         }
         $stat['revenue'][] = $row->earned_admin;
         $stat['impressions'][] = $row->impressions;
         $stat['clicks'][] = $row->clicks;
         $stat['ctr'][] = ($row->impressions) ? $row->clicks * 100 / $row->impressions : 0;
         $cur_date += 24 * 60 * 60;
      }
      while ($cur_date <= $to) {
         $stat['revenue'][] = 0.0;
         $stat['impressions'][] = 0;
         $stat['clicks'][] = 0;
         $stat['ctr'][] = 0.0;
         $cur_date += 24 * 60 * 60;
      }
      
      $cur_date = $from;
      return $stat;
   } //end publisher_get_line_stat()   

   /**
    * возвращает дату начала работы заданного пользователя (дата его создания)
    *
    * @param integer $id_entity код учетной записи пользователя
    * @return integer дата начала работы пользователя в формате unix timestamp
    */
   public function get_start_date($id_entity) {
      $this->db->select('unix_timestamp(creation_date) AS start_date');
      $res = $this->db->get_where('entities', array(
            'id_entity' => $id_entity));
      if ($res->num_rows()) {
         $row = $res->row();
         return $row->start_date;
      }
      return mktime(0, 0, 0, date("m"), date("d"), date("Y"));
   } //end get_start_date
   

   /**
    * наполняет базу тестовыми данными по статистике адвертайзера 1
    *
    * @return ничего не возвращает
    */
   public function test_data() {
      $date = mktime(0, 0, 0, 1, 1, 2007);
      $end = mktime();
      $count = 0;
      while ($date < $end) {
         $impressions = (int) (rand(800, 1000) + 200 * sin($count * pi() / 100));
         $clicks = (int) ($impressions * rand(1, 3) / 100);
         $spent = $clicks * 0.12;
         $this->db->insert('stat_advertisers', array(
               'id_entity_advertiser' => 1, 
               'stat_date' => type_to_str($date, 'databasedate'), 
               'clicks' => $clicks, 
               'spent' => number_format($spent, 2, '.', ''), 
               'impressions' => $impressions));
         $count++;
         $date += 24 * 60 * 60;
      }
   } //end test_data
   

   /**
    * возвращает статистику паблишера или админа-паблишера за указанных период
    *
    * @param integer $id_entity учетная запись пользователя
    * @param string $role роль пользователя
    * @param array $range период времени за который возвращается доход (по умолчанию за сегодня)
    * @return array массив со статистикой (revenue, impressions, clicks, ctr)
    */
   public function publisher_range_stat($range = NULL) {
      $CI =& get_instance();
      if (is_null($range)) {
         $range = array(
               'from' => time(), 
               'to' => time());
      }
      $select = $this->db
         ->select_sum('earned_admin')
         ->select_sum('clicks')
         ->select_sum('impressions')
         ->from('stat_sites');
      $res = $select->where('stat_date >=', type_to_str($range['from'], 'databasedate'))->where('stat_date <=', type_to_str($range['to'], 'databasedate'))->get();
      
      $stat = array(
            'revenue' => 0.0, 
            'impressions' => 0, 
            'clicks' => 0, 
            'ctr' => 0.0
      );
      
      if ($res->num_rows()) {
         $row = $res->row();
         $stat['impressions'] = $row->impressions;
         $stat['clicks'] = $row->clicks;
         $stat['revenue'] = $row->earned_admin;
      }      
      
      if ($stat['impressions']) {
         $stat['ctr'] = $stat['clicks'] * 100 / $stat['impressions'];
      }
      
      return $stat;
   } //end publisher_range_stat
   

   /**
    * вовзвращает список лучших рекламодателей системы
    *
    * @param string $sort_field поле, по которому осуществляется сортировка
    * @param string $sort_direction направление сортировки
    * @param array $range массив задающий отчетный период (from, to), если не задан - за все время
    * @return array массив со списком рекламодателей
    */
   public function top_advertisers($sort_field, $sort_direction, $range) {
      $limit = $this->global_variables->get("TopAdvertisersOnPage");
      $this->db->select('
      	 entities.id_entity AS id, 
      	 name, 
      	 e_mail, 
      	 SUM(spent) AS revenue,
      	 SUM(impressions) AS impressions, 
      	 SUM(clicks) AS clicks,
      	 SUM(clicks)*100/SUM(impressions) AS ctr'
      );
      $this->db->from('entities')->join('advertisers', 'entities.id_entity=advertisers.id_entity_advertiser');
      if (!is_null($range)) {
         $this->db->join('stat_advertisers', 'entities.id_entity = stat_advertisers.id_entity_advertiser' . ' AND stat_date>="' . type_to_str($range['from'], 'databasedate') . '" AND stat_date<="' . type_to_str($range['to'], 'databasedate') . '"', 'LEFT');
      } else {
         $this->db->join('stat_advertisers', 'stat_advertisers.id_entity_advertiser = entities.id_entity', 'LEFT');
      }
      $this->db->group_by('id');
      $this->db->order_by($sort_field, $sort_direction);
      $this->db->limit($limit);
      $res = $this->db->get();
      
      $top = array();
      foreach ($res->result() as $row) {
         $top[$row->id]['name'] = $row->name;
         $top[$row->id]['email'] = $row->e_mail;
         $top[$row->id]['revenue'] = $row->revenue;
         $top[$row->id]['impressions'] = $row->impressions;
         $top[$row->id]['clicks'] = $row->clicks;  
         $top[$row->id]['ctr'] = $row->ctr;
      }
      return $top;
   } //end top_advertisers
   

   /**
    * вовзвращает список лучших пользователей системы
    *
    * @param string $sort_field поле, по которому осуществляется сортировка
    * @param string $sort_direction направление сортировки
    * @param array $range массив задающий отчетный период (from, to), если не задан - за все время
    * @return array массив со списком пользователей
    */
   public function top_members($sort_field, $sort_direction, $range) {
      $limit = $this->global_variables->get("TopMembersOnPage");
      
      $this->db->select('entities.id_entity AS id, name, e_mail')->select_sum('num_ratings')->select_sum('num_comments');
      $this->db->from('entities')->join('members', 'entities.id_entity=members.id_entity_member');
      if (!is_null($range)) {
         $this->db->join('stat_members', 'stat_members.id_member = entities.id_entity' . ' AND stat_date>="' . type_to_str($range['from'], 'databasedate') . '" AND stat_date<="' . type_to_str($range['to'], 'databasedate') . '"', 'LEFT');
      } else {
         $this->db->join('stat_members', 'stat_members.id_member = entities.id_entity', 'LEFT');
      }
      
      $this->db->group_by('id');
      $this->db->order_by($sort_field, $sort_direction);
      $this->db->limit($limit);
      $res = $this->db->get();
      $top = array();
      
      foreach ($res->result() as $row) {
         $top[$row->id]['name'] = $row->name;
         $top[$row->id]['email'] = $row->e_mail;
         $top[$row->id]['num_ratings'] = $row->num_ratings;
         $top[$row->id]['num_comments'] = $row->num_comments;
      }
      return $top;
   } //end top_advertisers
   

   /**
    * добавляет новую роль для пользователя
    *
    * @param integer $id_entity код учетной записи в которую добавляется роль
    * @param string $role наименование добавляемой роли
    * @return bool TRUE - при успехе
    */
   function add_role($id_entity, $role, $need_activation = false, $sign_up_data = array (), $fields_from_role_table = array ()) {
      $res = $this->db->select('id_role')->get_where('roles', array(
            'name' => $role));
      if (!$res->num_rows()) {
         return FALSE;
      }
      $id_role = $res->row()->id_role;
      $res = $this->db->get_where('entity_roles', array(
                           'id_entity' => $id_entity,
                           'id_role' => $id_role
                        ));
      if (0 < $res->num_rows()) {
         return FALSE; //Такая запись уже есть
      }
      
      if (0 == count($sign_up_data)) { 
         if ($need_activation) {
            $this->db->set('status', 'activation');
         }
         $this->db->insert('entity_roles', array(
               'id_entity' => $id_entity, 
               'id_role' => $id_role));
         $this->db->insert($role . 's', array(
               'id_entity_' . $role => $id_entity));
      } else {
         $extra_fields = array(
               "country", 
               "timezone", 
               "city", 
               "address", 
               "zip_postal", 
               "phone", 
               "state");
         foreach ($extra_fields as $name) {
            if ($sign_up_data[$name]) {
               $this->db->select('id_contact_field');
               $res = $this->db->get_where('contact_fields', array(
                     'name' => $name));
               if ($res->num_rows()) {
                  $row = $res->row();
                  $this->db->insert('contacts', array(
                        'id_entity' => $id_entity, 
                        'id_contact_field' => $row->id_contact_field, 
                        'value' => $sign_up_data[$name]));
               }
            }
         }
         
         $values = array("id_entity_$role" => $id_entity);
         if (sizeof($fields_from_role_table) > 0) {
            
            foreach ($fields_from_role_table as $field => $type) {
               if ($sign_up_data[$field]) {
                  $values[$field] = type_to_str($sign_up_data[$field], $type);
               }
            }
         }
         
         $this->db->insert("{$role}s", $values);
         
         $this->db->select('id_role');
         $res = $this->db->get_where('roles', array(
               'name' => $role));
         if ($res->num_rows()) {
            $row = $res->row();
            if ($need_activation) {
               $this->db->set('status', 'activation');
            }
            $this->db->insert('entity_roles', array(
                  'id_entity' => $id_entity, 
                  'id_role' => $row->id_role
            ));
         }
      }
      return TRUE;
   } //end add_role
   

   /**
    * Проверяет заполнены ли все поля в форме аккаунта
    *
    * @param integer $id_entity код учетной записи
    * @return bool TRUE - если заполнены все поля
    */
   function have_all_contacts($id_entity) {
      $res = $this->db->distinct()->select('id_contact_field')->get_where('contacts', array(
            'id_entity' => $id_entity));
      return $res->num_rows() == 6;
   } //end have_all_contacts   

   /**
    * Меняет E-Mail пользователя
    * 
    * @param integer $id_entity код пользователя
    * @param string $e_mail новый e-mail пользователя
    */
   public function set_mail($id_entity, $e_mail) {
   	$this->db
   	   ->where('id_entity', $id_entity)
   	   ->update('entities', array('e_mail' => $e_mail));
   } //end set_mail

   /**
    * Returns users list by role
    * 
    * @param $role - user role
    * @param $page
    * @param $per_page 
    * @param $sort_field
    * @param $sort_direction
    * @return array
    */
   public function get_by_role($role, $page = null, $per_page = null, $sort_field = null, $sort_direction = null) {
      if (! is_null($page) && !is_null($per_page)) {
         $this->db->limit($per_page, ($page - 1) * $per_page);
      }
      if (! is_null($sort_field)) {
         $sort_direction = (is_null($sort_direction) ? 'ASC' : $sort_direction);
         $this->db->order_by($sort_field, $sort_direction);
      }
      $this->db->join('entity_roles er', 'er.id_entity = e.id_entity');
      $this->db->join('roles r', 'r.id_role = er.id_role');
      $this->db->where('r.name', $role);
      $this->db->where('er.status', 'active');
      $this->db->select('e.*');
      //return $this->db->get('advertisers')->result();
      $results = $this->db->get('entities e');
      return $results->result_array();
   }
   
   public function get_role_status($id_entity, $role) {
      $results = $this->db->select('er.status')
                          ->where('er.id_entity', $id_entity)
                          ->where('r.name', $role)
                          ->join('roles r', 'r.id_role=er.id_role')
                          ->get('entity_roles er');
      if (0 < $results->num_rows()) {
         $row = $results->row();
         return $row->status;
      }
      return '';
   }
   
   public function get_hold($id_entity) {
   	  $res = $this->db->select('hold')->get_where('entities', array(
            'id_entity' => $id_entity));
      if (!$res->num_rows()) {
         return NULL;
      }
      $row = $res->row();
      return $row->hold;
   }
   
} //end class Entity


?>