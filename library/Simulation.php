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
				 * ЗюЫю If it's important to use old value of $retunningTime:
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
	 * Получение плана на день
	 * @param string $date
	 * @return mixed
	 */
	public function getPlan($date){
		$date = $this->convertDate($date);
		$plan = $this->getListOfProducts($date, 100);
		
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
		
		if ($flag){					
			for ($i = 1; $i < 13; $i++){
				$co = $tableRaw->getCount($i);
				$co = $co -	$materials[$i];          
				$where['ID = ?'] = $i;
				$rawData = array(
					'Count'	=> $co
				);
				$tableRaw->update($rawData, $where);
			}
			$plan1 = $this->getListOfProductsWithOrderId($date, 100);
			return $plan1;
		}else{
			$data = array(
				'OrderID' 	=> 1,
				'ProductID' => 4,
				'Count'		=> 100,
				'RealCount' => 0
			);
			
			$tableOrdProd = new Application_Model_DbTable_OrderProduct();
			$tableEP = new Application_Model_DbTable_ExecutionPlan();
			$orderProducts = $tableEP->getOrderProductByTime($date, 100);
			$tableOrdProd->insert($data);
			$insertedOrderProduct = $tableOrdProd->lastOrderProduct();
			
			$data = array(
				'OrderProductID' => $insertedOrderProduct['ID'],
				'AddOpportunity' => 0
			);
			
			$tableEP->insertIntoPlan($orderProducts[0], $data);
			$avaibleProd = $this->getAvaibleProd($date);
			
			$lastP =  $tableEP->getLastPlan();
			$exTime = $tableEP->getOrderProductExecutionTime($lastP['OrderProductID']);
			$planIDs = $tableEP->getOrderProductByTime($date, $exTime);			
			foreach ($planIDs as $planID){
				$ordProd = $tableOrdProd->getOrderProduct($planID);
				
				if($avaibleProd[$ordProd['ProductID']] <= 0)
					continue;
				
				if ($avaibleProd[$ordProd['ProductID']] >= $ordProd['Count']){
					$avaibleProd[$ordProd['ProductID']] = $avaibleProd[$ordProd['ProductID']] - $ordProd['Count'];
					$data = array(
						'Count' => 0
					);
					
					$where['ID = ?'] = $planID;
					$tableOrdProd->update($data, $where);
					
					$plan1[] = array(
						'demandId' => $ordProd['OrderID'],
						'productId' => $ordProd['ProductID'],
						'count' => $ordProd['Count']
					);
					
				}else{
					$avaibleProd[$ordProd['ProductID']] = 0;
					
					$count = $ordProd['Count'] - $avaibleProd[$ordProd['ProductID']];
					$data = array(
						'Count' => $count
					);
					
					$where['ID = ?'] = $planID;
					$tableOrdProd->update($data, $where);
					
					$plan1[] = array(
						'demandId' => $ordProd['OrderID'], 
						'productId' => $ordProd['ProductID'], 
						'count' => $avaibleProd[$ordProd['ProductID']]
					);
				}
			}
			
			$producedProd = array(
				1 => 0,
				2 => 0,
				3 => 0
			);
			
			foreach ($plan1 as $pl){
				$producedProd[$pl['productId']] += $pl['count'];
			}
			$materials = $this->necessaryAmountOfMaterials($producedProd);
			
			for ($i = 1; $i < 13; $i++){
				$co = $tableRaw->getCount($i);
				$co = $co -	$materials[$i];          
				$where['ID = ?'] = $i;
				$rawData = array(
					'Count'	=> $co
				);
				$tableRaw->update($rawData, $where);
			}
			$tableProduct = new Application_Model_DbTable_Product();
			$times = $tableProduct->getRetunningExecutionProductTime();
			$downtime = 0;
			for ($i = 1; $i < 4; $i++){
				if($avaibleProd[$i] > 0){
					$downtime += $avaibleProd[$i] * $times[$i]['ExecutionTime'];
				}
			}			
			$downtime += $avaibleProd[4];
			$plan1[] = array(
				'demandId' => 1,
				'productId' => 4,
				'count' => $downtime
			);
			
			return $plan1;
		}
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