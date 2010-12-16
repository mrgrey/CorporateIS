<?php
	class Application_Model_DbTable_Raw extends Zend_Db_Table_Abstract
	{
		protected $_name = 'Raw';
		
		
		/**
		 * 
		 * Получаем список всех материалов
		 */
		public function getListOfMaterials(){
			$db = Zend_Db_Table_Abstract::getDefaultAdapter();
			$select = $db->select()->from($this->_name);
			$stmt = $db->query($select);
			$result = $stmt->fetchAll();
			return $result;
		}	
		
		public function getCount($rawId){
			$db = Zend_Db_Table_Abstract::getDefaultAdapter();
			$select = $db->select()->from($this->_name)->where('ID = ?', $rawId);
			$stmt = $db->query($select);
			$res = $stmt->fetch();
			return $res['Count']; 
		}
		
		public function setEmpty(){
			$data = array('Count' => 0);
			$where['ID > ?'] = 0;
			$this->update($data, $where);
		}
		
    
	}