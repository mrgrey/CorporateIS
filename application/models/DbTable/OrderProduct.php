<?php
	class Application_Model_DbTable_OrderProduct extends Zend_Db_Table_Abstract
	{
		protected $_name = 'OrderProduct';
		protected $_referenceMap    = array(
	        'Product'	 => array(	
	            'columns'           => 'ProductID',	
	            'refTableClass'     => 'Application_Model_DbTable_Product',	
	            'refColumns'        => 'ID'	
	        ),
	        'Order'	    => array(
	        	'columns'			=> 'OrderID',
	        	'refTableClass'		=> 'Application_Model_DbTable_Order',
	        	'refColumns'		=> 'ID'
	        	)  
	    );
	    
	    /**
	     * 
	     * ��������� ����� ����� ������
	     * @param int $orderId
	     * @param mixed $products
	     */
	    public function newOrderProducts($orderId, $products, $status){
		    for ($i = 1; $i < 4; $i++){
	     		if ($products[$i] > 0){
	     			$data = array(
	     				'OrderID' 	=> $orderId,
			    		'ProductID' => $i,
	     				'Date'		=> $status, //-1 ��������, ��� ����� ��� �� ������ �� ����������; 0 - ��� ��� ������, �� ��� �� ����������
			    		'Count'		=> $products[$i],
			    		'Modifier'  => 0
	     			);
					
	     			$this->insert($data);
	     		}
	     	}
	     	return true;
	    }
	    
		/**
		 * 
		 * ������ ������ ������
		 * @param int $orderId
		 * @param int $orderType
		 */
		public function setOrderStatus($orderId, $orderType){
			$where['OrderID = ?'] = $orderId;
			$data = array(
				'Date' => $orderType
			); 
			
			return $this->update($data, $where);
		}
		
		/**
		 * 
		 * ��������� ����� �� ����������� �����
		 * @param unknown_type $orderId
		 */
		public function isOrderStarted($orderId){
			$select = $this->select()
				->where('OrderID = ?', $orderId)
				->where('Date > 0');
				
			$stmt = $this->fetchAll($select);

			return count($stmt) > 0;
		}
		
		/**
		 * 
		 * ��������� ���� ������� ������
		 * @param int $orderId
		 */
		public function getOrderProductByOrderId($orderId){
			$db = $this->getDefaultAdapter();
			$select = $db->select()
				->from($this->_name)
				->where('OrderID = ?', $orderId)
				->order('ProductID');

			return $db->query($select);
		}
	}