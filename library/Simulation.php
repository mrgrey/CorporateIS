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
		//��������� ���� �� �������� ���������� � ������� ������
		$tableDelivery = new Application_Model_DbTable_Delivery();
		$wasLastWeekDelivery = $tableDelivery->wasLastWeekDelivery($date);
		//�.�. �������� ���������� ��� � 7 ���� ������ 3 ��� ����� ������ ������, �� ���������� ����������� 10 ���� 
		for ($i = 1; $i < 11; $i++){
			//������� ����
			$list = $this->getPlan($ordProds);
			$time += 86400; //������� �������
			//��������� �� �����, ������ �������� �� 1 ���� � ���������� ������ ���� ���������� ���
			if (isset($nextDayFirstBlock)) unset($nextDayFirstBlock);
			unset($tempList);
			foreach ($list as $block){
				if ($time > 0){
					$count = $block['Count'];
					$modifier = 0;
					if($block['ProductID'] != $prevProductId)
						$xtime = $time - $block['RetunningTime'];
					if ($count * $block['ExecutionTime'] > $xtime + $block['Modifier']){
						$count = floor(($xtime + $block['Modifier']) / $block['ExecutionTime']);
						$modifier = ($xtime + $block['Modifier']) % $block['ExecutionTime'];
					}
					if ($count > 0){
						//���������� ������������� ������������� ������������
						if($block['ProductID'] != $prevProductId)
							$time -= $block['RetunningTime'];
						//������� ���������� ���������
						if ($wasLastWeekDelivery){
							if ($i > 3)
								$products[$block['ProductID']] += $count;
						}else{
							if ($i < 8)
								$products[$block['ProductID']] += $count;
						}
						$time = $time - $count * $block['ExecutionTime'] + $block['Modifier'];					
						$prevProductId = $block['ProductID'];
						if ($count != $block['Count']){
							$block['Count'] -= $count;
							$block['Modifier'] = $modifier;
							$tempList[] = $block;
						}
						$plan[] = array(
							'demandId'  => $block['OrderID'],
							'productId' => $block['ProductID'], 
							'count'     => $count
						);
					}			
				}else{
					//���������� ������ ���� ���������� ���
					if (isset($nextDayFirstBlock) && ($block['DateExecution'] + $block['Time'] < $nextDayFirstBlock['DateExecution'] + $nextDayFirstBlock['Time'])){
						$tempList[] = $nextDayFirstBlock;
						$nextDayFirstBlock = $block;
					}else{
						if(isset($nextDayFirstBlock)){
							$tempList[] = $block;
						}else{
							$nextDayFirstBlock = $block;
						}						 
					}											
				}
			}
			$ordProds = isset($nextDayFirstBlock)
				? array_merge(array($nextDayFirstBlock), $tempList)
				: $tempList;
		}
		$tableRawRequiments = new Application_Model_DbTable_RawRequiment();
		$tableNomenclature = new Application_Model_DbTable_Nomenclature();
		$sigmaT = $tableDelivery->getSigmaT();
    	$sigmaN = $tableNomenclature->getSigmaN();
		$shoppingList = $tableRawRequiments->getShoppingList($products, $sigmaN, $sigmaT);
		
		
		$tableDelivery->newDelivery($date + 259200, $shoppingList);
		
		
		return $shoppingList;
	}
	
	/**
	 * 
	 * �������� ���� �� ���������� ������
	 * @param unknown_type $ordProds
	 */
	private function getPlan($ordProds){
		$list = array();
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
				$modifiedBlock[] = $block;
			}else{
				//�� ������� � �������� ������ ����� ��� ����� �����
				$inserted = false; //����, ������� ����� ���������� ����� �� �� ����� ��� ����� �����
				$flag2 = FALSE; //����, ������� ���������� ������� ������ � ����������� ����������
				$i = 0; //������� ������� � �����
				//$list = array();
				foreach ($list as $sortedBlock){ // foreach 2
					//���� ����� �� ������ ��������� ��� ���� � �������, �� ������� ���� ���� ��������� ������ �� ����,
					//��� ���� ������� ������, ��� ������� ���� �� ������ ������������� ����� ������ ���
					if (($sortedBlock['ProductID'] == $block['ProductID']) && ($block['Count'] * $block['ExecutionTime'] < 86400)){						
						//��������� �� ��, ��� ��������������� ���� ������ ���� �������� ����� ��������
						if ($sortedBlock['DateExecution'] + $sortedBlock['Time'] > $block['DateExecution'] + $block['Time']){
							//���������� ������� ���� �� ����� ����������������
							array_splice($list, $i, 0, array($block));
							$inserted = true;
							break;
						}
						$flag2 = TRUE;
					} else if ($flag2) {
						//���������� ������� ���� �� ����� ����������������
						array_splice($list, $i, 0, array($block));
						//$list = array_merge(array_slice($list, 0, $i-1),array($block),array_slice($list, $i)); 
						$inserted = true;
						break;
					}
					$i++;										
				}//end of foreach 2
				//������� �������� ���� � ������ �������, ���� ��� ���� ���������� ����� �������
				if (!$inserted){
					$i = 0;
					foreach ($list as $sortedBlock) {
						if ($list[0]['DateExecution'] + $list[0]['Time'] > $block['DateExecution'] + $block['Time']){
							//���������� ������� ���� �� ����� ����������������
							array_splice($list, $i, 0, array($block));
							$inserted = true;							
							break;
						}
						$i++;
					}
					if (!$inserted){
						//�������� ������� ���� ������, ���������� � ����� �������
						$list[] = $block;
					}
				}
					
			}			
		}//end of foreach 1
		if ($modifiedBlock){
			foreach ($modifiedBlock as $block) 
				$list = array_merge(array($block), $list);
		}
			
			
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
		$tableNomenclature->updateDeliveryNomenclature($deliveryId, $materials);
		$tableRaw = new Application_Model_DbTable_Raw();
		return $tableRaw->addRaw($materials);	
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
		$tableRawRequiments = new Application_Model_DbTable_RawRequiment();
		$tableRaw = new Application_Model_DbTable_Raw();
		
		//�������� ������ � �������� � ��������� ����� ����������� ���
		$prevProductId = $tableOrderProduct->getLastBlockProductId();
		$time = 86400; //������� �������
		$manufacturedProducts = array_fill(1, 3, 0);  //������� ������������� �������
		
		foreach ($list as $block){		
			//��������� ������� ��������� ����� ���������� �� ��������� � ������� ����������	
			$availableProducts = $tableRawRequiments->getAvaibleProductCount();			
			//���������� ������� ������� �� ����� �������� ���������			
			$count = min($availableProducts[$block['ProductID']], $block['Count']);
			//$count = $block['Count'];	
			//���������� ������� ������� �� ��������� �������� ��������� � ������� ������
			$modifier = 0;
			$xtime = $time;
			if($block['ProductID'] != $prevProductId)
					$xtime = $time - $block['RetunningTime'];
			if ($count * $block['ExecutionTime'] > $xtime + $block['Modifier']){
				$count = floor(($xtime + $block['Modifier']) / $block['ExecutionTime']);
				$modifier = ($xtime + $block['Modifier']) % $block['ExecutionTime'];
			}					
			if ($count > 0){
				//���������� ������������� ������������� ������������
				if($block['ProductID'] != $prevProductId)
					$time -= $block['RetunningTime'];
					
				//��������� ������ ����� �� ����������� ����� ������������ ����
				$tableOrderProduct->setOrderProduct($block['ID'], $date + 86400 - $time, $count, 0);
				
				//������� ����� ����, ���� ������� �� ����� ���� �������� ���������
				if ($count != $block['Count'])
					$tableOrderProduct->newOrderProduct($block['OrderID'], $block['ProductID'], 0, $block['Count'] - $count, $modifier);
					
				//������� ������� �������
				$time = $time - $count * $block['ExecutionTime'] + $block['Modifier'];
				$prevProductId = $block['ProductID'];
				
				//������������ ����������
				$manufacturedProducts[$block['ProductID']] += $count;
				//������� ����������� ����� �� ��		
				$tableRaw->spendRaw($manufacturedProducts);
				$manufacturedProducts = array_fill(1, 3, 0);
				
				$plan[] = array(
					'demandId'  => $block['OrderID'],
					'productId' => $block['ProductID'], 
					'count'     => $count
				);
			}
		}	
		
		
		
		//��������� ��������� ������� ������������
		if ($time > 0) {
			$plan[] = array(
				'demandId'  => 0,
				'productId' => 4, 
				'count'     => $time
			);
		}
		
		$id=1;
		foreach($plan as $item){
			$result[$id]=$item;
			$id++;
		}			
		
		return $result;
		//return $plan;				
	}
	
		
				
}
