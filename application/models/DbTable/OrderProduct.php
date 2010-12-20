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
	     	return true;
	    }
	    
		/**
		 * 
		 * ћен€ет статус заказа
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
		 * ѕровер€ет начал ли выполн€тьс€ заказ
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
		 * ѕолучение всех товаров заказа
		 * @param int $orderId
		 */
		public function getOrderProductByOrderId($orderId){
			$db = $this->getDefaultAdapter();
			$select = $db->select()
				->from($this->_name)
				->where('OrderID = ?', $orderId)
				->order('ProductID');
			$result = $db->query($select);
			return $result->fetchAll();
		}
		
		/**
		 * 
		 * ѕолучение списка блоков, ожидающих выполнени€
		 */
		public function getWaitingList(){
			$db = $this->getDefaultAdapter();
			$select = $db->select()
				->from('OrderProduct')
				->join('Order','OrderProduct.OrderID = Order.ID', array('DateExecution', 'OrderTypeID'))
				->join('Product', 'OrderProduct.ProductID = Product.ID', array('ExecutionTime', 'RetunningTime'))
				->join('OrderType', 'Order.OrderTypeID = OrderType.ID', array('Time'))
				->where('Date = 0')
				;
			$stmt = $db->query($select);			
			return $stmt->fetchAll();
		}
		
		/**
		 * 
		 * ѕолучение Id продукта последнего выполненного блока
		 */
		public function  getLastBlockProductId(){
			$db = $this->getDefaultAdapter();
			$select = $db->select()
				->from($this->_name)
				->where('Date > 0')
				->order('Date DESC')
				->limit(1)				
				;
			$stmt = $db->query($select);
			$result = $stmt->fetch();
			return $result['ProductID'];
		}
		
		/**
		 * 
		 * »зменение блока
		 * @param int $id
		 * @param int $date
		 * @param int $count
		 * @param int $modifier
		 */
		public function setOrderProduct($id, $date, $count, $modifier){
			$where['ID = ?'] = $id;
			$data = array(
				'Date' 		=> $date,
				'Count'		=> $count,
				'Modifier'	=> $modifier
			); 			
			return $this->update($data, $where);
		}
		
		/**
		 * 
		 * —оздание нового блока
		 * @param int $orderId
		 * @param int $productId
		 * @param int $date
		 * @param int $count
		 * @param int $modifier
		 */
		public function newOrderProduct($orderId, $productId, $date, $count, $modifier){
			$data = array(
				'OrderID'	=> $orderId,
				'ProductID'	=> $productId,
				'Date' 		=> $date,
				'Count'		=> $count,
				'Modifier'	=> $modifier
				);			
			return $this->insert($data);
		}
		
	}