<?php
class Simulation{
	/**
	 * 
	 * ������ ���� ��� ������ �������������
	 * @param string $date
	 * @return bool
	 */
	public function niceStart($date){ 
		$db = Zend_Db_Table_Abstract::getDefaultAdapter();
		
		$tables = array(
			'OrderProduct',
			'Order',
			'Nomenclature',
			'Delivery',
			'Customer'
		);
		
		foreach($tables as $table) 
			$db->exec("TRUNCATE TABLE `{$table}`");
			
		$tableRaw = new Application_Model_DbTable_Raw();
		$tableRaw->setEmpty();		
		
		return true;		
	}	
	
	/**
	 * 
	 * ������������ ������ �� ������� InProgress
	 * @param string $date
	 * @return mixed
	 */
	public function getShoppingList($date){
		$date = strtotime($date);
		$tableOrderProduct = new Application_Model_DbTable_OrderProduct();
		//�������� ������ ������������� ������
		$ordProds = $tableOrderProduct->getWaitingList();
		$prevProductId = $tableOrderProduct->getLastBlockProductId();
		//�.�. �������� ���������� ��� � 7 ���� ������ 3 ��� ����� ������ ������, �� ���������� ����������� 10 ���� 
		for ($i = 1; $i < 11; $i++){
			//������� ����
			$list = $this->getPlan($ordProds);
			$time += 86400; //������� �������
			//��������� �� �����, ������ �������� �� 1 ���� � ���������� ������ ���� ���������� ���
			if (isset($nextDayFirstBlock)) unset($nextDayFirstBlock);
			$j = 0;
			unset($tempList);
			foreach ($list as $block){
				if ($time > 0){
					//������� ���������� ���������
					if ($i > 3) $products[$block['ProductID']] += $block['Count'];
					$time = $time - $block['Count']*$block['ExecutionTime'];
					if ($block['ProductID'] != $prevProductId) $time = $time - $block['RetunningTime'];
					$prevProductId = $block['ProductID'];
				}else{
					//���������� ������ ���� ���������� ���
					if ((isset($nextDayFirstBlock)) && ($block['DateExecution'] + $block['Time'] < $nextDayFirstBlock['DateExecution'] + $nextDayFirstBlock['Time'])){
						$tempList[] = $nextDayFirstBlock;
						$nextDayFirstBlock = $block;
					}else{
						if(!(isset($nextDayFirstBlock))){
							$nextDayFirstBlock = $block;
						}else{
							$tempList[] = $block;
						}						 
					}											
				}
				$j++;
			}
			$ordProds = isset($nextDayFirstBlock)
				? array_merge(array($nextDayFirstBlock), $tempList)
				: $tempList;
		}
		$tableRawRequiments = new Application_Model_DbTable_RawRequiment();
		$shoppingList = $tableRawRequiments->getShoppingList($products);
		$tableDelivery = new Application_Model_DbTable_Delivery();
		$tableDelivery->newDelivery($date + 300, $shoppingList);
		return $shoppingList;
	}
	
	/**
	 * 
	 * �������� ���� �� ���������� ������
	 * @param unknown_type $ordProds
	 */
	private function getPlan($ordProds){
		foreach ($ordProds as $block){ //foreach 1
			/*������ $block ����� ��������� ���������:
			 * array(
			 * 		'ID' 			=>	int - ID �����
			 * 		'OrderID'		=>	int	- ID ������
			 * 		'ProductID'		=>	int	- ID ��������
			 * 		'Date'			=>	int	- ���� ���������� �����, �� ��������� ��� ������������� ������ ����� 0
			 * 		'Count'			=>	int	- ���������� ���������
			 *		'Modifier'		=>	int	- � ������, ���� ����� ������ �������� ���� ��������� � ���������� ���� ����������� ����� ����� �������� ������ ������������ �������
			 *		'DateExecution'	=>	int - ����� ���������� ������
			 *		'Time'			=>	int - ��������� ������ (7 ��� 14 ����)
			 *		'ExecutionTime'	=>	int - ����� ���������� ������ ��������
			 *		'RetunningTime'	=> 	int - ����� ����������� ��� ��������� ������������ ����� ���, ��� ������ ����������� �������
			 *		)
			 */
			//����� ��������� ���� �� ������� ������������, ��� ����� ��������, 
			//��� �� ��� ������������ ������������ � ������� ���� � ��� ���� �������� ������ � ����� ����
			if ($block['Modifier'] != 0){
				$modifiedBlock = $block;
			}else{
				//�� ������� � �������� ������ ����� ��� ����� �����
				$flag1 = TRUE; //����, ������� ����� ���������� ����� �� �� ����� ��� ����� �����
				$flag2 = FALSE; //����, ������� ���������� ������� ������ � ����������� ����������
				$i = 0; //������� ������� � �����
				foreach ($list as $sortedBlock){ // foreach 2
					//���� ����� �� ������ ��������� ��� ���� � �������, �� ������� ���� ���� ��������� ������ �� ����,
					//��� ���� ������� ������, ��� ������� ���� �� ������ ������������� ����� ������ ���
					if (($sortedBlock['ProductID'] == $block['ProductID']) && ($block['Count']*$block['ExecutionTime'] < 86400)){						
						//��������� �� ��, ��� ��������������� ���� ������ ���� �������� ����� ��������
						if ($sortedBlock[DateExecution] + $sortedBlock['Time'] > $block[DateExecution] + $block['Time']){
							//���������� ������� ���� �� ����� ����������������
							$list = array_merge(
								array_slice($list, 0, $i-1),
								array($block),
								array_slice($list, $i)
								);
							$flag1 = FALSE;
							break;
						}
						$flag2 = TRUE;
					}else{
						if ($flag2){
							//���������� ������� ���� �� ����� ����������������
							$list = array_merge(
								array_slice($list, 0, $i-1),
								array($block),
								array_slice($list, $i)
								);
							$flag1 = FALSE;
							break;
						}
					}
					$i++;										
				}//end of foreach 2
				//�������� ������� ���� ������, ���������� � ����� �������
				if ($flag1) $list[] = $block;
			}			
		}//end of foreach 1
		if ($modifiedBlock) $list = array_merge(array($modifiedBlock), $list);
		return $list;
	}
	
	/**
	 * 
	 * ����� ����������
	 * @param string $date
	 * @param int $deliveryId
	 * @param mixed $materials
	 * @return bool
	 */
	public function receivingMaterials($date, $deliveryId, $materials){
		$date = strtotime($date);
		$tableDelivery = new Application_Model_DbTable_Delivery();
		$tableDelivery->setDelivery($date, $deliveryId);
		$tableNomenclature = new Application_Model_DbTable_Nomenclature();
		return $tableNomenclature->updateDeliveryNomenclature($deliveryId, $materials);			
	}
	
	/**
	 * 
	 * ��������� ����� �� ����
	 * @param string $date
	 * @return mixed
	 */
	public function getDayPlan($date){
		$date = strtotime($date);
		$tableOrderProduct = new Application_Model_DbTable_OrderProduct();
		//�������� ������ ������������� ������
		$ordProds = $tableOrderProduct->getWaitingList();
		//���������� ��������� (Insertion sort) � ��������� ���������: O(n2); ���������� ��� ������� ������� ������ ���������� � ������������� ������ � ��������� ��� ����
		//������� ������
		$list = $this->getPlan($ordProds);		
		//�� ������������� ������ ��������� ���� �� 1 ����
		//��������� ������� ��������� ����� ���������� �� ��������� � ������� ����������
		$tableRawRequiments = new Application_Model_DbTable_RawRequiment();
		$avaibleProducts = $tableRawRequiments->getAvaibleProductCount();
		//�������� ������ � �������� � ��������� ����� ����������� ���
		$prevProductId = $tableOrderProduct->getLastBlockProductId();
		$time = 86400; //������� �������
		$manufacturedProducts = array(  //������� ������������� �������
			1 => 0,
			2 => 0,
			3 => 0
			);
		foreach ($list as $block){
			//���������� ������������� ������������� ������������
			$time = ($block['ProductID'] == $prevProductId)
				? $time
				: $time - $block['RetunningTime'];
			//���������� ������� ������� �� ����� �������� ���������			
			$count = ($avaibleProducts[$block['ProductID']] > $block['Count'])
				? $block['Count']
				: $avaibleProducts[$block['ProductID']];
			//���������� ������� ������� �� ��������� �������� ��������� � ������� ������
			$modifier = 0;
			if ($count * $block['ExecutionTime'] > $time + $block['Modifier']){
				$count = floor(($time + $block['Modifier']) / $block['ExecutionTime']);
				$modifier = ($time + $block['Modifier']) % $block['ExecutionTime'];
			}
			$count = ($count * $block['ExecutionTime'] > $time)
				?  floor($time / $block['ExecutionTime'])
				: $count;			
			//��������� ������ ����� �� ����������� ����� ������������ ����
			$tableOrderProduct->setOrderProduct($block['ID'], $date + 86400 - $time, $count, 0);
			//������� ����� ����, ���� ������� �� ����� ���� �������� ���������
			if ($count != $block['Count']) $tableOrderProduct->newOrderProduct($block['OrderID'], $block['ProductID'], 0, $block['Count'] - $count, $modifier);
			//������� ������� �������
			$time = $time - $count * $block['ExecutionTime'] + $block['Modifier'];
			$prevProductId = $block['ProductID'];
			//������������ ����������
			$manufacturedProducts['ProductId'] += $count;
			$plan[] = array('demandId' => $block['OrderID'],'productId' => $block['ProductID'], 'count' => $count);
		}	
		//������� ����������� ����� �� ��
		$tableRaw = new Application_Model_DbTable_Raw();
		$tableRaw->spendRaw($manufacturedProducts);
		//��������� ��������� ������� ������������
		if ($time != 0) $plan[] = array('demandId' => 0,'productId' => 4, 'count' => $time);
		return $plan;			
	}
	
		
				
}