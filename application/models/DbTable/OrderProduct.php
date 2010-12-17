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
	     * ƒобавл€ет новые блоки заказа
	     * @param int $orderId
	     * @param mixed $products
	     */
	    public function newOrderProducts($orderId, $products, $status){
		    for ($i = 1; $i < 4; $i++){
	     		if ($products[$i] > 0){
	     			$data = array(
	     				'OrderID' 	=> $orderId,
			    		'ProductID' => $i,
	     				'Date'		=> $status, //-1 означает, что заказ еще не прин€т на исполнение; 0 - что уже прин€т, но еще не выполн€лс€
			    		'Count'		=> $products[$i],
			    		'Modifier'  => 0
	     				);
	     			$this->insert($data);
	     		}
	     	}
	     	return TRUE;
	    }
	    
		/**
		 * 
		 * ћен€ет статус заказа
		 * @param int $orderId
		 * @param int $orderType
		 */
		public function setOrderStatus($orderId, $orderType){
			$where['OrderID = ?'] = $orderId;
			$data = array('Date' => $orderType); 
			$res = $this->update($data, $where);
			return $res;
		}
		
		/**
		 * 
		 * ѕровер€ет начал ли выполн€тьс€ заказ
		 * @param unknown_type $orderId
		 */
		public function isOrderStarted($orderId){
			$select = $this->select()
				->where('OrderID = ?', $orderId)
				->where('Date > 0')
				;
			$stmt = $this->fetchAll($select);
			$rowCount = count($stmt);
			if ($rowCount > 0){
				return TRUE;
			}else{
				return FALSE;
			}
		}
		
		/**
		 * 
		 * ѕолучение всех товаров заказа
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
		
	    /*
	    /**
	     * 
	     * INSERT
	     * @param int $orderId
	     * @param int $productId
	     * @param int $count
	     *
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
	     *
		public function getOrderProduct($orderProductId){
			$select = $this->select()->where('ID = ?', $orderProductId);
			$result = $this->fetchRow($select);
			return $result;
		}	
    
		
		
		/**
		 * 
		 * ѕолучение последненго добавленного OrderProduct
		 *
		public function lastOrderProduct(){
			$db = Zend_Db_Table_Abstract::getDefaultAdapter();
			$select = $db->select()
				->from($this->_name)
				->order('ID DESC');
			$stmt = $db->query($select);
			$result = $stmt->fetch();
			return $result;
		}
		*/
	}