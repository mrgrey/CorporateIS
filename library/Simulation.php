<?php
class Simulation{
	/**
	 * 
	 * Чистка базы при старте моделирования
	 * @param string $date
	 * @return bool
	 */
	public function niceStart($date){ //$date не используется, но оставляю, чтобы не было проблем с интеграцией
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
	 * Получение кол-ва материалов, необходимых для  пр-ва товаров
	 * @param mixed $products
	 * @return mixed
	 */
	private function necessaryAmountOfMaterials($products){
		$tableRawRequment = new Application_Model_DbTable_RawRequiment();
		$rawRequiments = $tableRawRequment->getRequiments();
		
		foreach ($rawRequiments as $requiments)
			$result[$requiments['RawID']] += $requiments['Count'] * $products[$requiments['ProductID']];
		
		return $result;
	}
	
	/**
	 * 
	 * Получение кол-ва товаров в плане начиная с date до date+time
	 * @param int $date
	 * @param int $time
	 * @return mixed
	 */
	private function getListOfProducts($date, $time){		
		$rawResult = $this->getListOfProductsWithOrderId($date, $time);
		$result = array();
		
		foreach($rawResult as $item) {
			$productId = $item['productId'];
			
			//TODO review this line please.
			//TODO don't know if productId can duplicate
			if(!isset($result[$productId]))
				$result[$productId] = 0;
				
			$result[$productId] += $item['count'];
		}
		
		return $result;
	}
	
	/**
	 * 
	 * Получение кол-ва товаров в плане начиная с date до date+time
	 * @param int $date
	 * @param int $time
	 * @return mixed
	 */
	private function getListOfProductsWithOrderId($date, $time){		
		$tableExecutionPlan = new Application_Model_DbTable_ExecutionPlan();
		$tableOrderProduct = new Application_Model_DbTable_OrderProduct();
		$tableProduct = new Application_Model_DbTable_Product();
		
		$plan = $tableExecutionPlan->getExecutionPlan();
		$times = $tableProduct->getRetunningExecutionProductTime(); 
		
		$prevExTime = 0; 
		
		while ($row = $plan->fetch()){
			$exTime = $tableExecutionPlan->getOrderProductExecutionTime($row['OrderProductID']);
			
			if ($exTime > $date){
				$orderProduct = $tableOrderProduct->getOrderProduct($row['OrderProductID']);
				$prevOrderProduct = $tableOrderProduct->getOrderProduct($prevPlan['OrderProductID']);
				
				
				$retunningTime = $times[$orderProduct['ProductID']]['RetunningTime'];
				
				if ($prevOrderProduct['ProductID'] == 4){
					$retunningTime = 0;
				}
				if ($prevOrderProduct['ProductID'] == $orderProduct['ProductID']){
					$retunningTime = 0;
				}	
				if ($orderProduct['ProductID'] == 4){
					$retunningTime = 0;
				}			
				
				/* TODO 
				 * Why you use $times[$orderProduct['ProductID']]['RetunningTime'] instead of updated $retunningTime?
				 * Looks like a bug.
				 *
				 * P.S. If it's important to use old value of $retunningTime:
				 *   - this code should be moved to line 96
				 *   - $times[$orderProduct['ProductID']]['RetunningTime'] should be replaced with $retunningTime
				 */
				 
				if (!($rT))
					$rT = $times[$orderProduct['ProductID']]['RetunningTime'];
					
				if (($prevExTime + $rT) < $date){
					$finPlanTime = $date - $prevExTime - $retunningTime; 
					$mod = ($exTime - $date - $finPlanTime) % $times[$orderProduct['ProductID']]['ExecutionTime'];
					$res = (($exTime - $date - $finPlanTime - $mod) / $times[$orderProduct['ProductID']]['ExecutionTime']);
					
					if ($mod <> 0)
						$res++;
				}else{
					$planTime = $exTime - $prevExTime - $retunningTime;
					$res = $planTime / $times[$orderProduct['ProductID']]['ExecutionTime'];
				}		
				
				$res = 1; //TODO review this line please
				if ($exTime > $date + $time){
					$overTime = $exTime - $date - $time;
					$mod = $overTime % $times[$orderProduct['ProductID']]['ExecutionTime'];
					$overProduct = ($overTime - $mod) / $times[$orderProduct['ProductID']]['ExecutionTime'];
					
					$res -= $overProduct; 
				}			
				
				$result[] = array(
					'demandId' => $orderProduct['OrderID'], 
					'productId' => $orderProduct['ProductID'], 
					'count' => $res
				);
			}
			
			if ($exTime  + $rT > $date + $time)
				break;
				
			$prevExTime = $exTime;
			$prevPlan = $row;
			$rT = $times[$orderProduct['ProductID']]['RetunningTime'];			
		}
		return $result;
	}
	
	/**
	 * 
	 * Формирование заявки на закупку
	 * @param string $date
	 * @return mixed
	 */
	public function getShoppingList($date){
		$date = $this->convertDate($date);
		$listOfProducts = $this->getListOfProducts($date + 300, 700);
		
		$queryData = array(
			1 => $listOfProducts[1],
			2 => $listOfProducts[2],
			3 => $listOfProducts[3] 
		);
			
		$listOfMaterials = $this->necessaryAmountOfMaterials($queryData);
		$tableDelivery = new Application_Model_DbTable_Delivery();
		$deliveries = $tableDelivery->getAll();
		$sum = 0;
		
		$n = count($deliveries);
		
		foreach ($deliveries as $delivery){
			$sum += ($delivery['Date'] - $delivery) * ($delivery['Date'] - $delivery);
			$lastDeliveryId = $delivery['ID'];
		}
		$sigmaVKvadrate = $sum/$n;
		$sigmaT = sqrt($sigmaVKvadrate);	
		
		$tableNomenclature = new Application_Model_DbTable_Nomenclature();	
		$tableRaw = new Application_Model_DbTable_Raw();
		$rawList = $tableRaw->getListOfMaterials();
		foreach ($rawList as $material){
			$nomenclature = $tableNomenclature->getNomenclatureForMaterial($material['ID']);
			$sum = 0;
			
			$n = count($nomenclature);
			
			foreach ($nomenclature as $nom)
				$sum = ($nom['Count'] - $nom['RealCount']) * ($nom['Count'] - $nom['RealCount']);
			
			$sigmaVKvadrate = $sum/$n;
			$sigmaN = sqrt($sigmaVKvadrate);
			$shoppingList[$material['ID']] = round($listOfMaterials[$material['ID']] + $listOfMaterials[$material['ID']] * $sigmaT / 700 + $sigmaN); 
			$shoppingList2[$material['ID']] = array('ID' => $material['ID'], 'Count' => $shoppingList[$material['ID']]);
		}
		
		$date += 300;
		$deliveryData = array(
			'Date' => $date,
			'RealDate' => 0,
		);
		
		$tableDelivery->insert($deliveryData);
		if(!($lastDeliveryId))
			$lastDeliveryId = 0;			
			
		foreach ($shoppingList2 as $material){
			$nomData = array(
				'DeliveryID'	=> $lastDeliveryId + 1,
				'RawID'			=> $material['ID'],
				'Count'			=> $material['Count'],
				'RealCount'		=> 0
			);
			
			$tableNomenclature->insert($nomData);
			$count = $tableRaw->getCount($material['ID']);
			$count += $material['Count'];
			$where['ID = ?'] = $material['ID'];
			
			$rawData = array(
				'Count' => $count,
			); 
			$tableRaw->update($rawData, $where);	
		}
		return $shoppingList;
	}
	
	/**
	 * 
	 * Прием материалов
	 * @param string $date
	 * @param int $deliveryId
	 * @param mixed $materials
	 * @return bool
	 */
	public function receivingMaterials($date, $deliveryId, $materials){
		$date = $this->convertDate($date);
		$tableDelivery = new Application_Model_DbTable_Delivery();
		$tableDelivery->updateDelivery($date, $deliveryId);
		$tableNomenclature = new Application_Model_DbTable_Nomenclature();
		
		$materials = range(1, 12);
		
		for ($i = 0; $i < 12; $i++)
		{
			$RawId=$i+1;
			$tableNomenclature->insertIntoNomenclature($deliveryId, $RawId, $materials[i]);
		}
		/*foreach ($materials as $material){
			$tableNomenclature->insertIntoNomenclature($deliveryId, $material['RawID'], $material['Count']);
		}*/
		return true;
	}
	
	/**
	 * 
	 * Получение плана на день InProgress
	 * @param string $date
	 * @return mixed
	 */
	public function getPlan($date){
		$date = strtotime($date);
		$tableOrderProduct = new Application_Model_DbTable_OrderProduct();
		//Получаем массив невыполненных блоков
		$ordProds = $tableOrderProduct->getWaitingList();
		//Сортировка вставками (Insertion sort) — Сложность алгоритма: O(n2); определяем где текущий элемент должен находиться в упорядоченном списке и вставляем его туда
		//Ищу опорный элемент, им будет первый элемент массива блоков, который с большой долей вероятности имеет максимально возможный приоритет
		$list[] = $ordProds[0];		
		//Собираю список
		foreach ($ordProds as $block){ //foreach 1
			/*Каждый $block имеет следующую структуру:
			 * array(
			 * 		'ID' 			=>	int - ID блока
			 * 		'OrderID'		=>	int	- ID заказа
			 * 		'ProductID'		=>	int	- ID продукта
			 * 		'Date'			=>	int	- Дата выполнения блока, по умолчанию для невыполненных блоков равна 0
			 * 		'Count'			=>	int	- количество продуктов
			 *		'Modifier'		=>	int	- в случае, если часть одного продукта была выполнена в предыдущий день модификатор будет иметь значение равное потраченному времени
			 *		'DateExecution'	=>	int - время выполнения заказа
			 *		'Time'			=>	int - срочность заказа (7 или 14 дней)
			 *		'ExecutionTime'	=>	int - время выполнения одного продукта
			 *		'RetunningTime'	=> 	int - время необходимое для настройки оборудования перед тем, как начать производить продукт
			 *		)
			 */
			//сразу проверяем блок на наличие модификатора, что будет означать, 
			//что на нем остановилось производство в прошлый день и его надо запихать первым в новый план
			if ($block['Modifier'] != 0){
				$modifiedBlock = $block;
			}else{
				//не повезло и придется искать место для этого блока
				$flag1 = TRUE; //флаг, который будет определять нашли ли мы место для этого блока
				$flag2 = FALSE; //флаг, который определяет очередь блоков с одинаковыми продуктами
				$i = 0; //счетчик позиции в листе
				foreach ($list as $sortedBlock){ // foreach 2
					//Если блоки со схожим продуктом уже есть в очереди, то текущий блок надо поставить следом за ними,
					//при этом следует учесть, что текущий блок не должен производиться более одного дня
					if (($sortedBlock['ProductID'] == $block['ProductID']) && ($block['Count']*$block['ExecutionTime'] < 86400)){						
						//проверяем на то, что отсортированный блок должен быть выполнен позже текущего
						if ($sortedBlock[DateExecution] + $sortedBlock['Time'] > $sortedBlock[DateExecution] + $sortedBlock['Time']){
							//запихиваем текущий блок на место отсортированного
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
							//запихиваем текущий блок на место отсортированного
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
				//добавить элемент было некуда, запихиваем в конец очереди
				if ($flag1) $list[] = $block;
			}			
		}//end of foreach 1
		//Из получившегося списка взять 1 день
		
		return $list;		
	}
	
	/**
	 * Узнаем сколько можно произвести из оставшихся материалов
	 * @param string date
	 */
	private function getAvaibleProd($date) {
		$saveProd = array(
			1 => 0,
			2 => 0,
			3 => 0
		);
		
		$tableEP = new Application_Model_DbTable_ExecutionPlan();
		$tableOrdProd = new Application_Model_DbTable_OrderProduct();
		$tableProduct = new Application_Model_DbTable_Product();
		$tableRaw = new Application_Model_DbTable_Raw();
		
		$orderProducts = $tableEP->getOrderProductByTime($date, 100);
		$time = 100;
		
		$times = $tableProduct->getRetunningExecutionProductTime();
		
		$orderProduct = $tableOrdProd->getOrderProduct($orderProducts[0]);
		$amountOfMaterials = $tableRaw->getListOfMaterials();
		
		$flagTime = false;
		
		for ($i = 1; $i < 3; $i++){
			$matReq = array(
				1 => 0,
				2 => 0,
				3 => 0
			);
			
			$matReq[$i] = 1;
			$req = $this->necessaryAmountOfMaterials($matReq);
			
			$min = 9999;
			for ($j = 0; $j < 12; $j++){
				$n = round(($amountOfMaterials[$j]['Count'] / $req[$j+1]), 0);
				$min = min($min, $n);
			}
			
			if ($min == 9999)
				$min = 0;
				
			if ($i <> $orderProduct['ProductID'])
				$flagTime = true;
				
			if ($flagTime){
				$time -= $times[$i]['RetunningTime'];
			} 
			
			$num = $min;
			if ($time < ($min * $times[$i]['ExecutionTime']))
				$num = round(($time / $times[$i]['ExecutionTime']), 0);
			
			$saveProd[$i] += $num;
			$time -= $num * $times[$i]['ExecutionTime'];
		}
		
		if ($time > 0){
			$saveProd[4] = $time;
		}	
		
		return $saveProd;
	}
	
	/**
	 * 
	 * приведение даты
	 * @param int $date
	 */
	private function convertDate($date){
		$tstmp = strtotime($date);
		$tblHelp = new Application_Model_DbTable_Helper();
		$startTime = $tblHelp->getStartTime();
		$time = $tstmp - $startTime;
		$time = round($time/864);
		return $time;		
	}
		
	/**
	 * Session test
	 * @param string $id
	 * @return int
	 */
	public function test1($id){		
		$date = $this->convertDate($id);
		$plan = $this->getListOfProducts(4, 100);
		
		$data = array(
			1 => $plan[1],
			2 => $plan[2],
			3 => $plan[3]
		);
		
		$materials = $this->necessaryAmountOfMaterials($data);
		$tableRaw = new Application_Model_DbTable_Raw();
		$warehouse = $tableRaw->getListOfMaterials();
		$flag = true;
		
		foreach ($warehouse as $material){
			if ($material['Count'] < $materials[$material['ID']])
				$flag = false;
		}
		
		return $materials[1];       	
	}
	
}