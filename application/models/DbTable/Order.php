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
		public function getLastAcceptedOrder(){
			
 			$db = Zend_Db_Table_Abstract::getDefaultAdapter();
 			$select = $db->select()
 				->from($this->_name)
 				->where('OrderTypeID > 1')
 				->order('ID DESC');
     		$stmt = $db->query($select);
     		$result = $stmt->fetch();     		
     		return $result;
		}	
		
		/**
		 * 
		 * Добавляет новый заказ
		 * @param int $customerId
		 * @param int $date
		 * @param int $exTime
		 */
		public function newOrder($customerId, $date, $exTime){
			$orderData = array(
	     		'CustomerID' 		=> $customerId,
	     		'OrderTypeID'		=> 1,
	     		'TimeRegistration'	=> $date,
	     		'TimeExecution'		=> $exTime
	     	);
	     	$result = $this->insert($orderData);
	     	return $result;
		}
		
		/**
		 * 
		 * Меняет статус заказа
		 * @param int $orderId
		 * @param int $orderType
		 */
		public function setOrderStatus($orderId, $orderType){
			$where['ID = ?'] = $orderId;
			$data = array('OrderTypeID' => $orderType);
			$res = $this->update($data, $where);
			return $res;
		}
		
		/**
		 * 
		 * Меняет время выполнения заказа
		 * @param unknown_type $orderId
		 * @param unknown_type $exTime
		 */
		public function setExecutionTime($orderId, $exTime){
			$where['ID = ?'] = $orderId;
			$data = array('TimeExecution' => $exTime);
			$res = $this->update($data, $where);
			return $res;
		}
		
    	
		/*
		/**
		 * 
		 * Возвращает ID последней добавленной строки
		 *
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
		 *
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
		
		}
		*/
		
	}