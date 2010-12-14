<?php
class Application_Model_DbTable_Warehouse extends Zend_Db_Table_Abstract
	{
		protected $_name = 'Warehouse';
		
		/**
		 * 
		 *  оличество товаров заказа на складе
		 * @param int $orderID
		 * @param int $productID
		 * @return int
		 */
		public function getCount($orderID, $productID){
			$db = Zend_Db_Table_Abstract::getDefaultAdapter();
			$select = $db->select()->from($this->_name)->where('OrderID = ?', $orderID)->where('ProductID = ?', $productID);
			$stmt = $db->query($select);
			$res = $stmt->fetchAll();
			$result = 0;
			foreach ($res as $r){
				$result += $r['Count'];
			}
			return $result;
		}
	}