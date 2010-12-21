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
 			$db = $this->getDefaultAdapter();
			
 			$select = $db->select()
 				->from($this->_name)
 				->where('OrderTypeID > 1')
 				->order('ID DESC')
				->limit(1)
				;
				
     		$stmt = $db->query($select);
     		return $stmt->fetch();
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
	     		'DateRegistration'	=> $date,
	     		'DateExecution'		=> $exTime
	     	);
	     	return $this->insert($orderData);
		}
		
		/**
		 * 
		 * Меняет статус заказа
		 * @param int $orderId
		 * @param int $orderType
		 */
		public function setOrderStatus($orderId, $orderType){
			$where['ID = ?'] = $orderId;
			$data = array(
				'OrderTypeID' => $orderType
			);
			
			return $this->update($data, $where);
		}
		
		/**
		 * 
		 * Меняет время выполнения заказа
		 * @param unknown_type $orderId
		 * @param unknown_type $exTime
		 */
		public function setExecutionTime($orderId, $exTime){
			$where['ID = ?'] = $orderId;
			$data = array(
				'TimeExecution' => $exTime
			);
			
			return $this->update($data, $where);
		}
	}