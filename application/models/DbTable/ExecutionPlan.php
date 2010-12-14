<?php
class Application_Model_DbTable_ExecutionPlan extends Zend_Db_Table_Abstract
	{
		protected $_name = 'ExecutionPlan';				
		protected $_referenceMap    = array(
	        'OrderProduct' => array(	
	            'columns'           => 'OrderProductID',	
	            'refTableClass'     => 'Application_Model_DbTable_OrderProduct',	
	            'refColumns'        => 'ID'	
	        )       
	    );
		
		/**
		 * 
		 * Get Previous Order Execution Plan
		 * @param int $orderId
		 * @return mixed
		 */
		 
		public function getPrevOrderExecutionPlan($orderId){
			$db = Zend_Db_Table_Abstract::getDefaultAdapter();
			$select = $db->select()
				->from(array('ExecutionPlan'))
				->join('OrderProduct','OrderProduct.ID = ExecutionPlan.OrderProductID', array(OrderID,ProductID, Count))
				->where('OrderProduct.OrderID=?',$orderId)
				->order('ID')
				;
			$stmt = $db->query($select);			
			return $stmt;
		}	
		
		/**
		 * 
		 * Возвращает время окончания этапа плана
		 * @param int $orderProductId
		 * @return int
		 */
		public function getOrderProductExecutionTime($orderProductId){
			$db = Zend_Db_Table_Abstract::getDefaultAdapter();
			$select = $db->select()->from($this->_name)->order('ID');
			$stmt = $db->query($select);
			$tableProduct = new Application_Model_DbTable_Product();
			$times = $tableProduct->getRetunningExecutionProductTime();
			$exTime = 0;
			$prevProductId = 0;
			while ($row = $stmt->fetch()){			
				$tableOrderProduct = new Application_Model_DbTable_OrderProduct();
				$orderProduct = $tableOrderProduct->getOrderProduct($row['OrderProductID']);
							/*$exPlan = $row->current();
				$orderProduct = $exPlan->findParentRow('Application_Model_DbTable_OrderProduct', 'OrderProduct');*/
				if (!($orderProduct['ProductID'] == $prevProductId)){
					if (!($prevProductId == 4)){
						$exTime +=  $times[$orderProduct['ProductID']]['RetunningTime'];
					}					
				}
				$exTime += $orderProduct['Count'] * $times[$orderProduct['ProductID']]['ExecutionTime'];
				$prevProductId = $orderProduct['ProductID'];
				if ($row['OrderProductID'] == $orderProductId){
					break;	
				}
			}
			return $exTime;
		}
		
		/**
		 * 
		 * Возвращает последний элемент очереди
		 */
		public function getLastPlan(){
			$db = Zend_Db_Table_Abstract::getDefaultAdapter();
			$select = $db->select()
				->from('ExecutionPlan')
				->join('OrderProduct', 'OrderProduct.ID = ExecutionPlan.OrderProductID', array('ProductID'))
				->where('ProductID < 4')
				->order('ID DESC')
				;
			$stmt = $db->query($select);
			$lastPlan = $stmt->fetch();
			return $lastPlan;
		}
		
		/**
		 * 
		 * Добавление элемента в середину очереди
		 * @param int $id
		 * @param mixed $data
		 */
		public function insertIntoPlan($id, $data){
			$db = Zend_Db_Table_Abstract::getDefaultAdapter();
			$select = $db->select()
				->from($this->_name)
				->where('ID > ?', $id-1)
				->order('ID')
				;
			$stmt = $db->query($select);
			$selectResult = $stmt->fetchAll();
			foreach ($selectResult as $res){
				$where['ID = ?'] = $res['ID'];
				$db->update($this->_name, $data, $where);
				$data = array(
					'OrderProductID' => $res['OrderProductID'],
					'AddOpportunity' => $res['AddOpportunity']
					);
				 
			}
			$data1 = array(
				'OrderProductID' => $data['OrderProductID'],
				'AddOpportunity' => 0
				);
			$db->insert($this->_name, $data1);
			return $res;
		}
		
		/**
		 * 
		 * Получение всей очереди
		 */
		public function getExecutionPlan(){
			$db = Zend_Db_Table_Abstract::getDefaultAdapter();
			$select = $db->select()->from($this->_name)->order('ID');
			$stmt = $db->query($select);
			return $stmt;
		}
		
		public function testMapper($id = '1'){
			 
      			
		   	$tableEP = new Application_Model_DbTable_ExecutionPlan();
		   	$select = $tableEP->select()->where('id=?', $id); 		
			$EPRowset = $this->fetchAll($select);
      		$order = $EPRowset->current();  
      		$bugsReportedByUser = $order->findParentRow('Application_Model_DbTable_OrderType');
      		return $bugsReportedByUser;
		}
		
		/**
		 * 
		 * Поиск заказов выполняемых в данный промежуток
		 * @param int $date
		 * @param int $time
		 */
		public function getOrderProductByTime($date, $time){
			$db = Zend_Db_Table_Abstract::getDefaultAdapter();
			$select = $db->select()->from($this->_name)->order('ID');
			$stmt = $db->query($select);
			$tableProduct = new Application_Model_DbTable_Product();
			$times = $tableProduct->getRetunningExecutionProductTime();
			$exTime = 0;
			$prevProductId = 0;
			while ($row = $stmt->fetch()){			
				$tableOrderProduct = new Application_Model_DbTable_OrderProduct();
				$orderProduct = $tableOrderProduct->getOrderProduct($row['OrderProductID']);							
				if (!($orderProduct['ProductID'] == $prevProductId)){
					if (!($prevProductId == 4)){
						$exTime +=  $times[$orderProduct['ProductID']]['RetunningTime'];
					}					
				}
				$exTime += $orderProduct['Count'] * $times[$orderProduct['ProductID']]['ExecutionTime'];
				$prevProductId = $orderProduct['ProductID'];
				if ($exTime > $date){
					$result[] = $orderProduct['ID'];
				}
				if ($exTime > $date + $time){
					break;
				}				 
			}
			return $result;
		}
		
		/**
		 * 
		 * Возвращает последний ордерпродукт заказа
		 * @param int $orderId
		 */
		public function getLastOrderPlan($orderId){
			$db = Zend_Db_Table_Abstract::getDefaultAdapter();
			$select = $db->select()
				->from(array('ExecutionPlan'))
				->join('OrderProduct','OrderProduct.ID = ExecutionPlan.OrderProductID', array(OrderID,ProductID))
				->where('OrderProduct.OrderID=?',$orderId)
				->order('ID DESC')
				;
			$stmt = $db->query($select);
			$res = $stmt->fetch();			
			return $res;
		}
    	
		/**
		 * 
		 * Удаляет заказ из плана
		 * @param int $orderId
		 * @return int
		 */
		public function deleteOrder($orderId){
			$db = Zend_Db_Table_Abstract::getDefaultAdapter();
			$select = $db->select()->from('OrderProduct')->where('OrderID = ?', $orderId);
			$stmt = $db->query($select);
			while ($row = $stmt->fetch()){
				$where['OrderProductID = ?'] = $row['ID'];
				$db->delete($this->_name, $where);
			}
		}
		
		public function getPlanStartTime($id){
			$db = Zend_Db_Table_Abstract::getDefaultAdapter();
			$select = $db->select()->from($this->_name)->order('ID');
			$stmt = $db->query($select);
			$tableProduct = new Application_Model_DbTable_Product();
			$times = $tableProduct->getRetunningExecutionProductTime();
			$exTime = 0;
			$prevProductId = 0;
			while ($row = $stmt->fetch()){			
				$tableOrderProduct = new Application_Model_DbTable_OrderProduct();
				$orderProduct = $tableOrderProduct->getOrderProduct($row['OrderProductID']);
							/*$exPlan = $row->current();
				$orderProduct = $exPlan->findParentRow('Application_Model_DbTable_OrderProduct', 'OrderProduct');*/
				if (!($orderProduct['ProductID'] == $prevProductId)){
					if (!($prevProductId == 4)){
						$exTime +=  $times[$orderProduct['ProductID']]['RetunningTime'];
					}					
				}
				if ($row['ID'] == $id){
					break;	
				}
				$exTime += $orderProduct['Count'] * $times[$orderProduct['ProductID']]['ExecutionTime'];
				$prevProductId = $orderProduct['ProductID'];				
			}
			return $exTime;
		}
		
		public function getLatterPlans($id){
			$db = Zend_Db_Table_Abstract::getDefaultAdapter();
			$select = $db->select()->from($this->_name)->where('ID > ?', $id);
			$stmt = $db->query($select);
			$res = $db->fetchAll($stmt);
			return $res;
		}
		
	}