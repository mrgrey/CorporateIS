<?php
class FrontOffice{
	/**
	 * 
	 * New Order
	 * @param string $date
	 * @param string $customer
	 * @param int $product1count
	 * @param int $product2count
	 * @param int $product3count
	 * @return mixed
	 */
	public function newOrder($date, $customer, $product1count, $product2count, $product3count){
		$date = $this->convertDate($date);
		$tableCustomer = new Application_Model_DbTable_Customer();
		$customerId = $tableCustomer->getCustomerId($customer);
		
		if (!$customerId){		
			$customerData = array(
				'Name'	=> $customer,
			);
			$tableCustomer->insert($customerData);
			$tableCustomer = new Application_Model_DbTable_Customer();
			$customerId = $tableCustomer->getCustomerId($customer);	
		}
		
     	$tableOrder = new Application_Model_DbTable_Order();
     	$orderExTime = $this->getExecutionTime($date, $product1count, $product2count, $product3count);
		
     	$orderData = array(
     		'CustomerID' 		=> $customerId,
     		'OrderTypeID'		=> 1,
     		'TimeRegistration'	=> $date,
     		'TimeExecution'		=> $orderExTime,
     	);
		
     	$tableOrder->insert($orderData);
     	$orderId = $tableOrder->lastOrderId();
		
     	$tableOrderProduct = new Application_Model_DbTable_OrderProduct();
     	$tableOrderProduct->insertData($orderId, 1, $product1count);
     	$tableOrderProduct->insertData($orderId, 2, $product2count);
     	$tableOrderProduct->insertData($orderId, 3, $product3count);
		
     	$tblHelp = new Application_Model_DbTable_Helper();
		$exTime = $orderExTime*864;
		$startTime = $tblHelp->getStartTime();
		$exTime = $exTime + $startTime;
		$orderExTime = date('Y-m-d H:i:s', $exTime);
     	return array(
     		'OrderId' 			=> $orderId, 
     		'ExecutionTime'		=> $orderExTime
     		);
     		
	}
	
	/**
	 * 
	 * Get Execution Date
	 * @param string $date
	 * @param int $product1count
	 * @param int $product2count
	 * @param int $product3count
	 * @return int 
	 */
	private function getExecutionTime($date, $product1count, $product2count, $product3count){
		$tableOrder = new Application_Model_DbTable_Order();
		$previousOrderId = $tableOrder->lastAcceptedOrderId();
		$prevOrder = $tableOrder->getOrder($previousOrderId);
		$prevOrderX = $prevOrder->current();
		$prevOrderType = $prevOrderX->findParentRow('Application_Model_DbTable_OrderType');
		$tableExecutionPlan = new Application_Model_DbTable_ExecutionPlan();
		
		$prevOrderPlan = $tableExecutionPlan->getPrevOrderExecutionPlan($previousOrderId)->fetchAll();		
		$tableProduct = new Application_Model_DbTable_Product();
		$times = $tableProduct->getRetunningExecutionProductTime();
		$i = count($prevOrderPlan) - 1; 
		
		$prevOrderExTime = $tableExecutionPlan->getOrderProductExecutionTime($prevOrderPlan[$i]['ID']);
		$lastPlan = $tableExecutionPlan->getLastPlan();
		$curOrderExTime = $tableExecutionPlan->getOrderProductExecutionTime($lastPlan['ID']);
		if ($curOrderExTime < $date){
			
			$tableOrderProduct = new Application_Model_DbTable_OrderProduct();
			$data = array(
				'OrderID' => 1,
				'ProductID' => 4,
				'Count' 	=> $date - $curOrderExTime
				);
			$tableOrderProduct->insert($data);
			//TODO should be replaced with lastInsertId
			$insertedId = $tableOrderProduct->lastOrderProduct();
			
			$curOrderExTime = $date;
			$data = array(
				'OrderProductID' => 2,
				'AddOpportunity' => 1
				);
			$tableExecutionPlan->insert($data);
		}
		
		$productCounts = array(
			1 => $product1count,
			2 => $product2count,
			3 => $product3count
		);
		
		foreach($productCounts as $id => $productCount) {
			if ($productCount > 0){
				$executionTime = $times[$id]['ExecutionTime'];
				
				$added = false;
				if (($prevOrderExTime + $productCount * $executionTime) < ($prevOrder[0]['TimeExecution'] + $prevOrderType['Days'])){
					foreach ($prevOrderPlan as $plan){
						if ($plan['ProductID'] == $id){
							if (($tableExecutionPlan->getOrderProductExecutionTime($plan['ID']) > $date)&&($plan['AddOpportunity'] == 0)){
								$curOrderExTime += $productCount * $executionTime;
								$added = true;
							}
						}
					}				
				}
				if (!$added){
					$curOrderExTime += $productCount * $executionTime;
					
					if ($lastPlan['ProductID'] != $id)
						$curOrderExTime += $times[$id]['RetunningTime'];
				}
			}
		}
		
		$tableDelivery = new Application_Model_DbTable_Delivery();
		$lastDelivery = $tableDelivery->getLastDelivery();
		
		if (!$lastDelivery)
			return $curOrderExTime + 1000;
		
		$lastPlanExTime = $tableExecutionPlan->getOrderProductExecutionTime($lastPlan['ID']);
		if ((($lastDelivery['Date'] + 700) > $lastPlanExTime)&&($lastPlanExTime>$date)){
			$curOrderExTime += $lastDelivery['Date'] + 700 - $lastPlanExTime;
		}else{
			$flag = true;
			$deliveryDate = $lastDelivery['Date'];
			while ($flag){
				$deliveryDate += 700;
				if ($deliveryDate > $date){
					$curOrderExTime += $deliveryDate - $date;
					$flag = false;
				}
			}
		}
		
		return $curOrderExTime;
	}	
	
	/**
	 * 
	 * Подтверждение заказа и добавление его в очередь
	 * @param string $date
	 * @param int $orderId
	 * @param int $orderType
	 * @return bool
	 */
	public function confirmOrder($date ,$orderId, $orderType){		
		$tableOrder = new Application_Model_DbTable_Order();
		if ($orderType > 1){
			$date = $this->convertDate($date);
			$previousOrderId = $tableOrder->lastAcceptedOrderId();
			$prevOrder = $tableOrder->getOrder($previousOrderId);
			$prevOrderX = $prevOrder->current();
			$prevOrderType = $prevOrderX->findParentRow('Application_Model_DbTable_OrderType', 'OrderType');
			
			$tableExecutionPlan = new Application_Model_DbTable_ExecutionPlan();
			$prevOrderPlan = $tableExecutionPlan->getPrevOrderExecutionPlan($previousOrderId)->fetchAll();		
			
			$tableProduct = new Application_Model_DbTable_Product();
			$times = $tableProduct->getRetunningExecutionProductTime();
			
			$i = count($prevOrderPlan) - 1; 
			
			$prevOrderExTime = $tableExecutionPlan->getOrderProductExecutionTime($prevOrderPlan[$i]['ID']);
			$lastPlan = $tableExecutionPlan->getLastPlan();
			$tableOrderProduct = new Application_Model_DbTable_OrderProduct();
			$orderProduct = $tableOrderProduct->getOrderProductByOrderId($orderId)->fetchAll();
			
			foreach ($orderProduct as $product){
				$prevOrderPlan = $tableExecutionPlan->getPrevOrderExecutionPlan($previousOrderId)->fetchAll();
				
				$added = false;
				if (($prevOrderExTime + $product['Count']*$times[$product['ProductID']]['ExecutionTime']) < ($prevOrder[0]['TimeExecution'] + $prevOrderType['Days'])){
				
					foreach ($prevOrderPlan as $plan){
						if ($plan['ProductID'] != $product['ProductID'])
							continue;
							
						if (($tableExecutionPlan->getOrderProductExecutionTime($plan['ID']) > $date)&&($plan['AddOpportunity'] == 0)){
							$data = array(
								'OrderProductID' => $product['ID'],
								'AddOpportunity' => 1
								);
							$tableExecutionPlan->insertIntoPlan($plan['ID'] + 1, $data);
							$added = true;
						}
					}				
				}
				
				if (!$added) {				
					if ($lastPlan['ProductID'] == $product['ProductID']){
						$tableExecutionPlan->insert(array(
							'OrderProductID' => $product['ID'],
							'AddOpportunity' => 0
						));
					}else
						$notAdded[$product['ID']] = array('OrderProductID' => $product['ID']);
				}				
			}
			foreach ($notAdded as $add){
				$data = array(
					'OrderProductID' => $add['OrderProductID'],
					'AddOpportunity' => 0
					);
				$tableExecutionPlan->insert($data);			
			}
		}		
		$tableOrder->updateOrderType($orderId, $orderType);
		return true;
	}
	
	/**
	 * 
	 * Увеличение объема заказа
	 * @param string $date
	 * @param int $orderId
	 * @param mixed $products
	 * @return string
	 */
	public function changeOrder($date, $orderId, $products){
	
		$exTime = $this->getExecutionTime(
			$this->convertDate($date), 
			$products[0], 
			$products[1], 
			$products[2]
		);
		
		$tblHelp = new Application_Model_DbTable_Helper();
		
		$startTime = $tblHelp->getStartTime();
		
		$exTime = $exTime * 864 + $startTime;
		
		$time = date('Y-m-d H:i:s', $exTime);
		
		return $time;
	}
	
	/**
	 * 
	 * Подтверждение увеличения объема
	 * @param string $date
	 * @param int $orderId
	 * @param mixed $products
	 * @return bool
	 */
	public function confirmChange($date, $orderId, $products){
		$date = $this->convertDate($date);
		$tableOrdProd = new Application_Model_DbTable_OrderProduct();
		$exTime = $this->getExecutionTime($date, $products[1], $products[2], $products[3]);
		$tableOrder = new Application_Model_DbTable_Order();
		
		$orderData = array(
     		'CustomerID' 		=> 1,
     		'OrderTypeID'		=> 1,
     		'TimeRegistration'	=> $date,
     		'TimeExecution'		=> $exTime,
     	);		
		
		$tableOrder->insert($orderData);
		
		$fakeOrderId = $tableOrder->lastOrderId(); 
		
		for ($i = 0; $i < 3; $i++){			
			if ($products[$i] <= 0)
				continue;
				
			$tableOrdProd->insert(array(
				'OrderID'	=> $fakeOrderId,
				'ProductID' => $i+1,
				'Count'		=> $products[$i],
				'RealCount'	=> 0
			));
		}
		
		$this->confirmOrder($date, $fakeOrderId, 2);
		
		$orderProducts = $tableOrdProd->getOrderProductByOrderId($fakeOrderId)->fetchAll();
		
		foreach ($orderProducts as $op){
			$data = array('OrderId' => $orderId);
			$where['ID = ?'] = $op['ID'];
			$tableOrdProd->update($data, $where);
		}
		$where['ID = ?'] = $fakeOrderId;
		$tableOrder->delete($where); 
		
		return true;
	}
	
	/**
	 * 
	 * Отмена заказа
	 * @param string $date
	 * @param int $orderId
	 * @return bool
	 */
	public function cancelOrder($date, $orderId){
		$date = $this->convertDate($date);
		
		$tableEP = new Application_Model_DbTable_ExecutionPlan();
		
		$ordPlans = $tableEP->getPrevOrderExecutionPlan($orderId);
		$plans = $ordPlans->fetchAll();
		$startTime = $tableEP->getPlanStartTime($plans[0]['ID']);
		
		if ($startTime <= $date)
			return false;
			
		$tableOrder = new Application_Model_DbTable_Order();
		$where['ID = ?'] = $orderId;
		$data = array('OrderTypeID' => 1);
		$tableOrder->update($data, $where);
		foreach ($plans as $plan){
			$where['ID = ?'] = $plan['ID'];
			$tableEP->delete($where);
		}
		
		return true;			
	}
	
	/**
	 * 
	 * приведение даты
	 * @param string $date
	 * @return int
	 */
	private function convertDate($date){
		$tstmp = strtotime($date);
		
		$tblHelp = new Application_Model_DbTable_Helper();
		$startTime = $tblHelp->getStartTime();
		
		$time = ($tstmp - $startTime)/864;
		return $time;		
	}
	
	/**
	 * Session test
	 * @param int $id
	 * @return bool 
	 */
	public function test($id){
		$date = $this->convertDate('2010-01-29 00:00:00');
		
		$tableEP = new Application_Model_DbTable_ExecutionPlan();
		
		$plans = $tableEP->getPrevOrderExecutionPlan($id)->fetchAll();
		
		$startTime = $tableEP->getPlanStartTime($plans[0]['ID']);
		
		if ($startTime <= $date)
			return 0;
			
		$tableOrder = new Application_Model_DbTable_Order();
		
		$where['ID = ?'] = $id;
		$data = array('OrderTypeID' => 1);
		$tableOrder->update($data, $where);
		
		foreach ($plans as $plan){
			$where['ID = ?'] = $plan['ID'];
			$tableEP->delete($where);
		}
		
		return true;
	}	
}