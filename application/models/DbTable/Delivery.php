<?php
	class Application_Model_DbTable_Delivery extends Zend_Db_Table_Abstract
	{
		protected $_name = 'Delivery';
		
		/**
		 * 
		 * Получение всех поставок
		 */
		public function getAll(){
			$select = $this->select();
			$result = $this->fetchAll($select);
			return $result;
		}		
    	
		/**
		 * 
		 * Update заяки
		 * @param int $date
		 * @param int $deliveryId
		 */
		public function updateDelivery($date, $deliveryId){
			$db = Zend_Db_Table_Abstract::getDefaultAdapter();
			$data = array('RealDate' => $date);
			$where['ID = ?'] = $deliveryId;
			$db->update($this->_name, $data, $where);
		}
		
		
		public function getLastDelivery(){
			$db = Zend_Db_Table_Abstract::getDefaultAdapter();
			$select = $db->select()->from($this->_name)->order('ID DESC');
			$stmt = $db->query($select);
			$res = $stmt->fetch();
			return $res;
		}
		
	}