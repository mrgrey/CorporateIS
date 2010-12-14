<?php
	class Application_Model_DbTable_Order extends Zend_Db_Table_Abstract
	{
		protected $_name = 'Order';
		protected $_referenceMap    = array(
	        'OrderType' => array(	
	            'columns'           => 'OrderTypeID',	
	            'refTableClass'     => 'Application_Model_DbTable_OrderType',	
	            'refColumns'        => 'ID'	
	        ),
	        'Customer' => array(
	        	'columns' 			=> 'CustomerID',
	        	'refTableClass'		=> 'Application_Model_DbTable_Customer',
	        	'refColumns' 		=> 'ID'
	        )       
	    );
		
		/**
		 * 
		 * Get Last Accepted Order
		 */
		public function lastAcceptedOrderId(){
			
 			$db = Zend_Db_Table_Abstract::getDefaultAdapter();
 			$select = $db->select()
 				->from($this->_name)
 				->where('OrderTypeID > 1')
 				->order('ID DESC');
     		$stmt = $db->query($select);
     		$result = $stmt->fetch();     		
     		return $result['ID'];
		}	
    	
	/**
		 * 
		 * ¬озвращает ID последней добавленной строки
		 */
		public function lastOrderId(){
			$db = Zend_Db_Table_Abstract::getDefaultAdapter();
 			$select = $db->select()
 				->from($this->_name) 				
 				->order('ID DESC');
     		$stmt = $db->query($select);
     		$result = $stmt->fetch();     		
     		return $result['ID'];
		}
		
		/**
		 * 
		 * Get Order By Id
		 * @param int $orderId
		 */
		public function getOrder($orderId){
			$tableOrder = new Application_Model_DbTable_Order();
			$select = $tableOrder->select()->where('ID = ?',$orderId);
			$result = $this->fetchAll($select);
			//$result->current();
			//$bugsReportedByUser = $result->findParentRow('Application_Model_DbTable_OrderType');
			return $result;
			//$db = Zend_Db_Table_Abstract::getDefaultAdapter();
			//$select = $db->select()->from($this->_name)->where('ID = ?', $orderId);
			//$stmt = $db->query($select);
			//$result = $stmt->fetchAll();
			//return $result;
		}
		
		public function updateOrderType($orderId, $orderType){
			$where['ID = ?'] = $orderId;
			$data = array('OrderTypeID' => $orderType);
			$this->update($data, $where);
		}
		
	}