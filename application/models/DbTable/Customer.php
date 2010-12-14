<?php
	class Application_Model_DbTable_Customer extends Zend_Db_Table_Abstract
	{
		protected $_name = 'Customer';
		
		/**
		 * 
		 * Get Customer Name By Id
		 * @param string $name
		 */
		public function getCustomerId($name){
			$select = $this->select()
		   		->where('Name=?', $name);
			
			$result = $this->fetchRow($select);
			return $result['ID'];
		}
		
		/**
		 * 
		 * Get Customer Id by Name
		 * @param unknown_type $id
		 */
		public function getCustomerName($id){
			$select = $this->select()
		   		->where('id=?', $id);
			
			$result = $this->fetchRow($select);
			return $result['Name'];
		}
	
		/**
		 * 
		 * Get Last Customer Id
		 */
		public function lastId(){
			
 			$db = Zend_Db_Table_Abstract::getDefaultAdapter();
 			$select = $db->select()->from($this->_name)->order('ID DESC');
     		$stmt = $db->query($select);
     		$res = $stmt->fetch();
     		return $res['ID'];
		}
    
	}
