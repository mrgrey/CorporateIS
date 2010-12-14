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
	     * INSERT
	     * @param int $orderId
	     * @param int $productId
	     * @param int $count
	     */
	    public function insertData($orderId, $productId, $count){
	    	if ($count > 0){
		    	$data = array(
		    		'OrderID' => $orderId,
		    		'ProductID' => $productId,
		    		'Count' => $count,
		    		'RealCount' => $count
		    	);
		    	$this->insert($data);
	    	}
	    }
		
	    /**
	     * 
	     * Get Order Product By Id
	     * @param unknown_type $orderProductId
	     */
		public function getOrderProduct($orderProductId){
			$select = $this->select()->where('ID = ?', $orderProductId);
			$result = $this->fetchRow($select);
			return $result;
		}	
    
		/**
		 * 
		 * Получение всех товаров заказа
		 * @param int $orderId
		 */
		public function getOrderProductByOrderId($orderId){
			$db = Zend_Db_Table_Abstract::getDefaultAdapter();
			$select = $db->select()
				->from($this->_name)
				->where('OrderID = ?', $orderId)
				->order('ProductID')
				;
			$stmt = $db->query($select);
			return $stmt;
		}
		
		/**
		 * 
		 * Получение последненго добавленного OrderProduct
		 */
		public function lastOrderProduct(){
			$db = Zend_Db_Table_Abstract::getDefaultAdapter();
			$select = $db->select()
				->from($this->_name)
				->order('ID DESC');
			$stmt = $db->query($select);
			$result = $stmt->fetch();
			return $result;
		}
		
	}