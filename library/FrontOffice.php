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
			
		//��������� ������� ������������ � �� � ��������� ��� �������������
		$tableCustomer = new Application_Model_DbTable_Customer();
		$customerId = $tableCustomer->getCustomerId($customer);
		if (!$customerId){		
			$customerId = $tableCustomer->newCustomer($customer);						
		}
		
		//����������� ����� ���������� ������
		$exTime = $this->getExecutionTime($date, $products);
		
		//��������� ����� � ��     	
     	//��������� ��� �����   
     	$tableOrder = new Application_Model_DbTable_Order();  
     	$orderId = $tableOrder->newOrder($customerId, $date, $exTime);	     	
		
     	//��������� ��� �����     	
     	$tableOrderProduct = new Application_Model_DbTable_OrderProduct();
     	$tableOrderProduct->newOrderProducts($orderId, $products, -1);     	
		
     	//�������� ���������
     	$orderExTime = date('Y-m-d H:i:s', $exTime);
     	return array(
     		'OrderId' 			=> $orderId, 
     		'ExecutionTime'		=> $orderExTime
		);     	
	}
	
	private function getExecutionTime($date, $products){	
     	$tableOrder = new Application_Model_DbTable_Order();
     	$tableProduct = new Application_Model_DbTable_Product(); 
		
     	//���������� ����� ������    	
     	$previousOrder = $tableOrder->getLastAcceptedOrder();
		
		$previousOrderExTime = $previousOrder
			? $previousOrder['ExecutionTime']
			: $date + 300 * 864; //!!!�������� �������� � ����������� �� ���� ������ �������� ����������
		
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
	 * ������������� ������ � ���������� ��� � �������
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
	 * ���������� ������ ������
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
	 * ������������� ���������� ������
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
		
		//�������� ����� ����������
		$exTime = $this->getExecutionTime($date, $product);
		
		//�������� ����� ���������� ������
		$tableOrder = new Application_Model_DbTable_Order();
		$tableOrder->setExecutionTime($orderId, $exTime);
		
		//��������� ����� ����� ������
		$tableOrderProduct = new Application_Model_DbTable_OrderProduct();
     	$tableOrderProduct->newOrderProducts($orderId, $product, 0);
		
		return true;
	}
	
	/**
	 * 
	 * ������ ������
	 * @param string $date
	 * @param int $orderId
	 * @return bool
	 */
	public function cancelOrder($date, $orderId){
		//��������� ����� �� ����� ����������
		$tableOrderProduct = new Application_Model_DbTable_OrderProduct();
		
		//���� ���������, �� ������ �� ��������
		if ($tableOrderProduct->isOrderStarted($orderId)){
			return false;
		}else{
		//�������� ������ ������
		$tableOrderProduct->setOrderStatus($orderId, -1);
		$tableOrder = new Application_Model_DbTable_Order();
		$tableOrder->setOrderStatus($orderId, 1);		
		return true;
		}
	}	
	
	/**
	 * 
	 * ��������� ��������� ������
	 * @param string $date
	 * @param int $orderId
	 * @return mixed
	 */
	public function getOrderStatus($date, $orderId){
		//�������� ��� ����� ������
		$tableOrderProduct = new Application_Model_DbTable_OrderProduct();
		
		$orderBlocks = $tableOrderProduct->getOrderProductByOrderId($orderId);
		
		//����������� �� ��� � ��������� 2 �������: 1 - ��� ���������; 2 - �����
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