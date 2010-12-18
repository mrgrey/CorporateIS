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
		$date = strtotime($date);
		$products = array(
			1	=> $product1count,
			2	=> $product2count,
			3	=> $product3count
		);
			
		//Проверяем наличие пользователя в БД и добавляем при необходимости
		$tableCustomer = new Application_Model_DbTable_Customer();
		$customerId = $tableCustomer->getCustomerId($customer);
		if (!$customerId){		
			$customerId = $tableCustomer->newCustomer($customer);						
		}
		
		//Расчитываем время выполнения заказа
		$exTime = $this->getExecutionTime($date, $products);
		
		//Сохраняем заказ в БД     	
     	//Сохраняем сам заказ   
     	$tableOrder = new Application_Model_DbTable_Order();  
     	$orderId = $tableOrder->newOrder($customerId, $date, $exTime);	     	
		
     	//Сохраняем его блоки     	
     	$tableOrderProduct = new Application_Model_DbTable_OrderProduct();
     	$tableOrderProduct->newOrderProducts($orderId, $products, -1);     	
		
     	//Получаем результат
     	$orderExTime = date('Y-m-d H:i:s', $exTime);
     	return array(
     		'OrderId' 			=> $orderId, 
     		'ExecutionTime'		=> $orderExTime
		);     	
	}
	
	private function getExecutionTime($date, $products){	
     	$tableOrder = new Application_Model_DbTable_Order();
     	$tableProduct = new Application_Model_DbTable_Product(); 
		
     	//Определяем время старта    	
     	$previousOrder = $tableOrder->getLastAcceptedOrder();
		
		$previousOrderExTime = $previousOrder
			? $previousOrder['ExecutionTime']
			: $date + 300 * 864; //!!!Поменять значение в зависимости от даты первой поставки материалов
		
		$exTime = max($previousOrderExTime, $date);

     	$times = $tableProduct->getRetunningExecutionProductTime();     
     	for ($i = 1; $i < 4; $i++){
     		if ($products[$i] > 0){
     			$exTime += ($times[$i]['ExecutionTime'] * $products[$i] + $times[$i]['RetunningTime']) * 864;
     		}     		
     	}
     	return $exTime;
	}
	
	/**
	 * 
	 * Подтверждение заказа и добавление его в очередь
	 * @param string $date
	 * @param int $orderId
	 * @param int $orderType
	 * @return bool
	 */
	public function confirmOrder($date, $orderId, $orderType){
		$tableOrder = new Application_Model_DbTable_Order();
		$tableOrderProduct = new Application_Model_DbTable_OrderProduct();
		
		$updateOrder = $tableOrder->setOrderStatus($orderId, $orderType);
		$updateOrderProduct = $tableOrderProduct->setOrderStatus($orderId, 0);
		
		return $updateOrder && $updateOrderProduct;
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
		$product = array(
			1 => $products[0],
			2 => $products[1],
			3 => $products[2]
		);
		
		$exTime = $this->getExecutionTime(strtotime($date), $product);		
		
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
		$date = strtotime($date);
		$product = array(
			1 => $products[0],
			2 => $products[1],
			3 => $products[2]
		);
		
		//Получаем время выполнения
		$exTime = $this->getExecutionTime($date, $product);
		
		//Изменяем время выполнения заказа
		$tableOrder = new Application_Model_DbTable_Order();
		$tableOrder->setExecutionTime($orderId, $exTime);
		
		//Добавляем новые блоки заказа
		$tableOrderProduct = new Application_Model_DbTable_OrderProduct();
     	$tableOrderProduct->newOrderProducts($orderId, $product, 0);
		
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
		//Проверяем заказ на старт выполнения
		$tableOrderProduct = new Application_Model_DbTable_OrderProduct();
		
		//Если стартовал, то отмена не возможна
		if ($tableOrderProduct->isOrderStarted($orderId)){
			return false;
		}else{
		//Изменяем статус заказа
		$tableOrderProduct->setOrderStatus($orderId, -1);
		$tableOrder = new Application_Model_DbTable_Order();
		$tableOrder->setOrderStatus($orderId, 1);		
		return true;
		}
	}	
	
	/**
	 * 
	 * Получение состояния заказа
	 * @param string $date
	 * @param int $orderId
	 * @return mixed
	 */
	public function getOrderStatus($date, $orderId){
		//Получаем все блоки заказа
		$tableOrderProduct = new Application_Model_DbTable_OrderProduct();
		
		$orderBlocks = $tableOrderProduct->getOrderProductByOrderId($orderId);
		
		//Пробегаемся по ним и формируем 2 массива: 1 - уже выполнено; 2 - всего
		$done = array_fill(1, 3, 0);	
		$total = array_fill(1, 3, 0);
		
		foreach ($orderBlocks as $block){
			if ($block['Date'] > 0)
				$done[$block['ProductID']] += $block['Count'];
			
			$total[$block['ProductID']] += $block['Count'];
		}
		
		return array (
			'Done' => $done, 
			'Total' => $total
		); 		
	}
	
}