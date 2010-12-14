<?php
class Simulation{
	
	/**
	 * 
	 * Enter description here ...
	 * @param string $date
	 * @return bool
	 */
	public function niceStart($date){
		$date = strtotime($date);
		
		$db = Zend_Db_Table_Abstract::getDefaultAdapter();
		$db->exec('DROP TABLE IF EXISTS ExecutionPlan');
		$db->exec('DROP TABLE IF EXISTS OrderProduct;');
		$db->exec("DROP TABLE IF EXISTS `Order`;");
		$db->exec('DROP TABLE IF EXISTS OrderType;');		
		$db->exec('DROP TABLE IF EXISTS Warehouse;');
		$db->exec('DROP TABLE IF EXISTS Nomenclature;');
		$db->exec('DROP TABLE IF EXISTS Delivery;');
		$db->exec('DROP TABLE IF EXISTS RawRequiment;');
		$db->exec('DROP TABLE IF EXISTS Helper;');
		$db->exec('DROP TABLE IF EXISTS Raw;');
		$db->exec('DROP TABLE IF EXISTS Product;');
		$db->exec('DROP TABLE IF EXISTS Customer;');
		
		$db->exec("CREATE TABLE IF NOT EXISTS Customer (
  ID int(11) NOT NULL AUTO_INCREMENT,
  Name varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (ID)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=2"); $db->exec("INSERT INTO Customer (ID, Name) VALUES
(1, 'Простой')");
		
	$db->exec("CREATE TABLE IF NOT EXISTS Delivery(
  ID int(11) NOT NULL AUTO_INCREMENT,
  Date int(11) NOT NULL,
  RealDate int(11) NOT NULL,
  PRIMARY KEY (ID)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;
");
	
	$db->exec("CREATE TABLE IF NOT EXISTS Helper (
  ID int(11) NOT NULL AUTO_INCREMENT,
  Value varchar(64) COLLATE utf8_unicode_ci NOT NULL,  
  PRIMARY KEY (ID)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=2"); $db->exec("INSERT INTO Helper (ID, Value) VALUES
(1, '1290593352')");
		$db->exec("CREATE TABLE IF NOT EXISTS ExecutionPlan (
  ID int(11) NOT NULL AUTO_INCREMENT,
  OrderProductID int(11) NOT NULL,
  AddOpportunity int(11) NOT NULL,
  PRIMARY KEY (ID),
  KEY OrderProductID (OrderProductID)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=2"); $db->exec("INSERT INTO ExecutionPlan (ID, OrderProductID, AddOpportunity) VALUES
(1, 1, 1)");
		$db->exec("CREATE TABLE IF NOT EXISTS Nomenclature (
   ID int(11) NOT NULL AUTO_INCREMENT,
  DeliveryID int(11) NOT NULL,
  RawID int(11) NOT NULL,
  Count int(11) NOT NULL,
  RealCount int(11) NOT NULL,
  PRIMARY KEY (ID),
  KEY DeliveryID (DeliveryID),
  KEY RawID (RawID)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;
		
		");
		$db->exec("CREATE TABLE IF NOT EXISTS `Order` (
  ID int(11) NOT NULL AUTO_INCREMENT,
  CustomerID int(11) NOT NULL,
  OrderTypeID int(11) NOT NULL,
  TimeRegistration int(11) NOT NULL,
  TimeExecution int(11) NOT NULL,
  PRIMARY KEY (ID),
  KEY CustomerID (CustomerID),
  KEY OrderTypeID (OrderTypeID)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=2"); $db->exec("INSERT INTO `Order` (ID, CustomerID, OrderTypeID, TimeRegistration, TimeExecution) VALUES
(1, 1, 2, 0, 0);
		
		");
		$db->exec("CREATE TABLE IF NOT EXISTS OrderProduct (
  ID int(11) NOT NULL AUTO_INCREMENT,
  OrderID int(11) NOT NULL,
  ProductID int(11) NOT NULL,
  Count int(11) NOT NULL,
  RealCount int(11) NOT NULL,
  PRIMARY KEY (ID),
  KEY OrderID (OrderID),
  KEY ProductID (ProductID)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=2"); $db->exec("INSERT INTO OrderProduct (ID, OrderID, ProductID, Count, RealCount) VALUES
(1, 1, 4, 0, 0);	
		");
		$db->exec("CREATE TABLE IF NOT EXISTS OrderType (
  ID int(11) NOT NULL AUTO_INCREMENT,
  Type varchar(16) COLLATE utf8_unicode_ci NOT NULL,
  Days int(11) NOT NULL,
  PRIMARY KEY (ID)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=4"); $db->exec("INSERT INTO OrderType (ID, Type, Days) VALUES
(1, 'Отказ', 0),
(2, 'Несрочный заказ', 1400),
(3, 'Срочный заказ', 700);		
		");
		$db->exec("CREATE TABLE IF NOT EXISTS Product (
  ID int(11) NOT NULL,
  Name varchar(11) COLLATE utf8_unicode_ci NOT NULL,
  ExecutionTime int(11) NOT NULL,
  RetunningTime int(11) NOT NULL,
  PRIMARY KEY (ID)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci"); $db->exec("INSERT INTO Product (ID, Name, ExecutionTime, RetunningTime) VALUES
(1, 'A', 2, 5),
(2, 'B', 5, 3),
(3, 'C', 3, 7),
(4, 'Простой', 1, 0);
		
		");
		$db->exec("CREATE TABLE IF NOT EXISTS Raw (
  ID int(11) NOT NULL AUTO_INCREMENT,
  Name varchar(11) COLLATE utf8_unicode_ci NOT NULL,
  Count int(11) NOT NULL,
  Volume int(11) NOT NULL,
  PRIMARY KEY (ID)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=13"); $db->exec("INSERT INTO Raw (ID, Name, Count, Volume) VALUES
(1, 'a', 0, 1),
(2, 'b', 0, 1),
(3, 'c', 0, 1),
(4, 'd', 0, 1),
(5, 'e', 0, 1),
(6, 'f', 0, 1),
(7, 'g', 0, 1),
(8, 'h', 0, 1),
(9, 'i', 0, 1),
(10, 'j', 0, 1),
(11, 'k', 0, 1),
(12, 'l', 0, 1);
		");
		$db->exec("CREATE TABLE IF NOT EXISTS RawRequiment (
  ProductID int(11) NOT NULL,
  RawID int(11) NOT NULL,
  Count int(11) NOT NULL,
  KEY RawID (RawID),
  KEY ProductID (ProductID)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci"); $db->exec("INSERT INTO RawRequiment (ProductID, RawID, Count) VALUES
(1, 1, 6),
(1, 2, 2),
(1, 3, 3),
(1, 4, 0),
(1, 5, 2),
(1, 6, 4),
(1, 7, 5),
(1, 8, 0),
(1, 9, 3),
(1, 10, 8),
(1, 11, 2),
(1, 12, 1),
(2, 1, 0),
(2, 2, 0),
(2, 3, 1),
(2, 4, 4),
(2, 5, 5),
(2, 6, 1),
(2, 7, 2),
(2, 8, 4),
(2, 9, 7),
(2, 10, 8),
(2, 11, 8),
(2, 12, 3),
(3, 1, 2),
(3, 2, 6),
(3, 3, 5),
(3, 4, 3),
(3, 5, 1),
(3, 6, 6),
(3, 7, 9),
(3, 8, 2),
(3, 9, 1),
(3, 10, 3),
(3, 11, 0),
(3, 12, 4);
		");
		$db->exec("CREATE TABLE IF NOT EXISTS Warehouse (
  ID int(11) NOT NULL AUTO_INCREMENT,
  OrderID int(11) NOT NULL,
  ProductID int(11) NOT NULL,
  Count int(11) NOT NULL,
  PRIMARY KEY (ID)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;
		
		");
		$db->exec("ALTER TABLE ExecutionPlan
  ADD CONSTRAINT executionplan_ibfk_1 FOREIGN KEY (OrderProductID) REFERENCES orderproduct (ID);
  		");

$db->exec("ALTER TABLE Nomenclature
  ADD CONSTRAINT nomenclature_ibfk_1 FOREIGN KEY (DeliveryID) REFERENCES delivery (ID),
  ADD CONSTRAINT nomenclature_ibfk_2 FOREIGN KEY (RawID) REFERENCES raw (ID);
	");
$db->exec("ALTER TABLE `Order`
  ADD CONSTRAINT order_ibfk_1 FOREIGN KEY (CustomerID) REFERENCES customer (ID),
  ADD CONSTRAINT order_ibfk_2 FOREIGN KEY (OrderTypeID) REFERENCES ordertype (ID);
	");
$db->exec("ALTER TABLE OrderProduct
  ADD CONSTRAINT orderproduct_ibfk_1 FOREIGN KEY (OrderID) REFERENCES `order` (ID),
  ADD CONSTRAINT orderproduct_ibfk_2 FOREIGN KEY (ProductID) REFERENCES product (ID);
	");
$db->exec("ALTER TABLE RawRequiment
  ADD CONSTRAINT rawrequiment_ibfk_1 FOREIGN KEY (RawID) REFERENCES raw (ID),
  ADD CONSTRAINT rawrequiment_ibfk_2 FOREIGN KEY (ProductID) REFERENCES product (ID);");	
		$tableHelp = new Application_Model_DbTable_Helper();
		$data = array(
			'ID'	=> 1,
			'Value' => $date
			);
		$tableHelp->update($data, 'ID = 1');
		return TRUE;	
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
		foreach ($rawRequiments as $requiments){
			$result[$requiments['RawID']] += $requiments['Count']*$products[$requiments['ProductID']];
		}
		return $result;
	}
	
	/**
	 * 
	 * Получение кол-ва товаров в плане начинаю с date до date+time
	 * @param int $date
	 * @param int $time
	 * @return mixed
	 */
private function getListOfProducts($date, $time){		
		$tableExecutionPlan = new Application_Model_DbTable_ExecutionPlan();
		$plan = $tableExecutionPlan->getExecutionPlan();
		$tableOrderProduct = new Application_Model_DbTable_OrderProduct();
		$tableProduct = new Application_Model_DbTable_Product();
		$times = $tableProduct->getRetunningExecutionProductTime(); 
		$prevExTime = 0;
		$result = array(
			1 => 0,
			2 => 0,
			3 => 0,
			4 => 0
			);
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
				if (!($rT))$rT = $times[$orderProduct['ProductID']]['RetunningTime'];
				if ($prevExTime + $rT < $date){
					$finPlanTime = $date - $prevExTime - $retunningTime; 
					$mod = ($exTime - $date - $finPlanTime) % $times[$orderProduct['ProductID']]['ExecutionTime'];
					$res = (($exTime - $date - $finPlanTime - $mod) / $times[$orderProduct['ProductID']]['ExecutionTime']);
					if ($mod <> 0) $res++;
				}else{
					$planTime = $exTime - $prevExTime - $retunningTime;
					$res = $planTime / $times[$orderProduct['ProductID']]['ExecutionTime'];
				}		
				$res = 1;
				if ($exTime > $date + $time){
					$overTime = $exTime - $date - $time;
					$mod = $overTime % $times[$orderProduct['ProductID']]['ExecutionTime'];
					$overProduct = ($overTime - $mod) / $times[$orderProduct['ProductID']]['ExecutionTime'];
					//if ($mod <> 0) $overProduct++;
					$res = $res - $overProduct; 
				}			
				$result[$orderProduct['ProductID']] = $result[$orderProduct['ProductID']] + $res;
				
			}
			if ($exTime + $rT> $date + $time) break;
			$prevExTime = $exTime;
			$prevPlan = $row;			
			$rT = $times[$orderProduct['ProductID']]['RetunningTime'];
		}
		return $result;
	}
	
	/**
	 * 
	 * Получение кол-ва товаров в плане начинаю с date до date+time
	 * @param int $date
	 * @param int $time
	 * @return mixed
	 */
	private function getListOfProductsWithOrderId($date, $time){		
		$tableExecutionPlan = new Application_Model_DbTable_ExecutionPlan();
		$plan = $tableExecutionPlan->getExecutionPlan();
		$tableOrderProduct = new Application_Model_DbTable_OrderProduct();
		$tableProduct = new Application_Model_DbTable_Product();
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
				if (!($rT))$rT = $times[$orderProduct['ProductID']]['RetunningTime'];
				if (($prevExTime + $rT) < $date){
					$finPlanTime = $date - $prevExTime - $retunningTime; 
					$mod = ($exTime - $date - $finPlanTime) % $times[$orderProduct['ProductID']]['ExecutionTime'];
					$res = (($exTime - $date - $finPlanTime - $mod) / $times[$orderProduct['ProductID']]['ExecutionTime']);
					if ($mod <> 0) $res++;
				}else{
					$planTime = $exTime - $prevExTime - $retunningTime;
					$res = $planTime / $times[$orderProduct['ProductID']]['ExecutionTime'];
				}		
				if ($exTime > $date + $time){
					$overTime = $exTime - $date - $time;
					$mod = $overTime % $times[$orderProduct['ProductID']]['ExecutionTime'];
					$overProduct = ($overTime - $mod) / $times[$orderProduct['ProductID']]['ExecutionTime'];
					//if ($mod <> 0) $overProduct++;
					$res = $res - $overProduct; 
				}			
				$result[] = array('demandId' => $orderProduct['OrderID'], 'productId' => $orderProduct['ProductID'], 'count' => $res);
				
			}
			if ($exTime  + $rT > $date + $time) break;
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
		$n = 0;
		foreach ($deliveries as $delivery){
			$sum += ($delivery['Date'] - $delivery)*($delivery['Date'] - $delivery);
			$n++;
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
			$n = 0;
			foreach ($nomenclature as $nom){
				$sum = ($nom['Count'] - $nom['RealCount'])*($nom['Count'] - $nom['RealCount']);
				$n++;
			}
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
		if(!($lastDeliveryId))	$lastDeliveryId = 0;			
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
		$materials=array(1,2,3,4,5,6,7,8,9,10,11,12);
		for ($i = 0; $i < 12; $i++)
		{
			$RawId=$i+1;
			$tableNomenclature->insertIntoNomenclature($deliveryId, $RawId, $materials[i]);
		}
		/*foreach ($materials as $material){
			$tableNomenclature->insertIntoNomenclature($deliveryId, $material['RawID'], $material['Count']);
		}*/
		return TRUE;
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
		$flag = TRUE;
		foreach ($warehouse as $material){
			if ($material['Count'] < $materials[$material['ID']]) $flag = FALSE;
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
			$tableOrdProd = new Application_Model_DbTable_OrderProduct();
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
				if ($avaibleProd[$ordProd['ProductID']] > 0){
					if ($avaibleProd[$ordProd['ProductID']] >= $ordProd['Count']){
						$avaibleProd[$ordProd['ProductID']] = $avaibleProd[$ordProd['ProductID']] - $ordProd['Count'];
						$data = array(
							'Count' => 0
							);
						$where['ID = ?'] = $planID;
						$tableOrdProd->update($data, $where);
						$plan1[] = array('demandId' => $ordProd['OrderID'], 'productId' => $ordProd['ProductID'], 'count' => $ordProd['Count']);
					}else{
						$avaibleProd[$ordProd['ProductID']] = 0;
						$count = $ordProd['Count'] - $avaibleProd[$ordProd['ProductID']];
						$data = array(
							'Count' => $count
							);
						$where['ID = ?'] = $planID;
						$tableOrdProd->update($data, $where);
						$plan1[] = array('demandId' => $ordProd['OrderID'], 'productId' => $ordProd['ProductID'], 'count' => $avaibleProd[$ordProd['ProductID']]);
					}
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
			$plan1[] = array('demandId' => 1, 'productId' => 4, 'count' => $downtime);
			//$result[] = array('demandId' => $orderProduct['OrderID'], 'productId' => $orderProduct['ProductID'], 'count' => $res);
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
		$orderProducts = $tableEP->getOrderProductByTime($date, 100);
		$time = 100;
		$tableProduct = new Application_Model_DbTable_Product();
		$times = $tableProduct->getRetunningExecutionProductTime();
		$tableRaw = new Application_Model_DbTable_Raw();
		$orderProduct = $tableOrdProd->getOrderProduct($orderProducts[0]);
		$amountOfMaterials = $tableRaw->getListOfMaterials();
		$flagTime = FALSE;
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
				$n = round(($amountOfMaterials[$j]['Count']/$req[$j+1]), 0);
				if ($min < $n) $min = $n;
			}
			if ($min == 9999) $min = 0;
			if ($i <> $orderProduct['ProductID']) $flagTime = TRUE;
			if ($flagTime){
				$time = $time - $times[$i]['RetunningTime'];
			} 
			if ($time < ($min * $times[$i]['ExecutionTime'])){
				$num = round(($time/$times[$i]['ExecutionTime']),0);
				$saveProd[$i] += $num;
				$time = $time - $num * $times[$i]['ExecutionTime'];
			}else{
				$saveProd[$i] += $min;
				$time = $time - $min * $times[$i]['ExecutionTime'];
			}
		}
		if ($time > 0){
			$saveProd[4] = $time;
		}	
		$result = $saveProd;
		return $result;
	}

	
	
	/**
	 * 
	 * Получение состояния заказа
	 * @param string $date
	 * @param int $orderId
	 * @return mixed
	 */
	public function getOrderStatus($date, $orderId){
		$date = $this->convertDate($date);
		$tableEP = new Application_Model_DbTable_ExecutionPlan();		
		$orderProducts = $tableEP->getPrevOrderExecutionPlan($orderId)->fetchAll();
		$result1 = array(
			1 => 0,
			2 => 0,
			3 => 0
			);
			
		$result2 = array(
			1 => 0,
			2 => 0,
			3 => 0
			);		
		var_dump($orderProducts);
		foreach ($orderProducts as $orderProduct){			
			$result2[$orderProduct['ProductID']] += $orderProduct['RealCount'];
			$exTime = $tableEP->getOrderProductExecutionTime($orderProduct['OrderProductID']);
			if ($exTime < date){
				$result1[$orderProduct['ProductID']] += $orderProduct['Count'];
				$result1[$orderProduct['ProductID']] += $orderProduct['RealCount'] - $orderProduct['Count'];
			}else{
				$startTime = $tableEP->getPlanStartTime($orderProduct['ID']);
				if ($startTime < $date){
					$count = $this->getListOfProducts($startTime, $date - $startTime);
					$result1[$orderProduct['ProductID']] += $count[$orderProduct['ProductID']];
					$result1[$orderProduct['ProductID']] += $orderProduct['RealCount'] - $orderProduct['Count'];
					$bagTrace = array('start' => $startTime, 'date' => $date);				}
			}
		}
		return array ('Done' => $result1, 'Total' => $result2); 
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
		$flag = TRUE;
		foreach ($warehouse as $material){
			if ($material['Count'] < $materials[$material['ID']]) $flag = FALSE;
		}
		return $materials[1];       	
	}
	
}