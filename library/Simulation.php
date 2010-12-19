<?php
class Simulation{
	/**
	 * 
	 * Чистка базы при старте моделирования
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
	 * Формирование заявки на закупку
	 * @param string $date
	 * @return mixed
	 */
	public function getShoppingList($date){
		$date = strtotime($date);
		
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
						if ($sortedBlock[DateExecution] + $sortedBlock['Time'] > $block[DateExecution] + $block['Time']){
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
		if ($modifiedBlock) $list = array_merge(array($modifiedBlock), $list);
		//Из получившегося списка составить план на 1 день
		//проверяем сколько продуктов можно изготовить из имеющихся в наличии материалов
		$tableRawRequiments = new Application_Model_DbTable_RawRequiment();
		$avaibleProducts = $tableRawRequiments->getAvaibleProductCount();
		//Получаем данные о продукте в последнем блоке предыдущего дня
		$prevProductId = $tableOrderProduct->getLastBlockProductId();
		$time = 86400; //счетчик времени
		$manufacturedProducts = array(  //счетчик произведенных товаров
			1 => 0,
			2 => 0,
			3 => 0
			);
		foreach ($list as $block){
			//Определяем необходимость перенастройки оборудования
			$time = ($block['ProductID'] == $prevProductId)
				? $time
				: $time - $block['RetunningTime'];
			//Определяем сколько товаров из блока возможно выполнить			
			$count = ($avaibleProducts[$block['ProductID']] > $block['Count'])
				? $block['Count']
				: $avaibleProducts[$block['ProductID']];
			//Определяем сколько товаров из возможных возможно выполнить в текущих сутках
			$modifier = 0;
			if ($count * $block['ExecutionTime'] > $time + $block['Modifier']){
				$count = floor(($time + $block['Modifier']) / $block['ExecutionTime']);
				$modifier = ($time + $block['Modifier']) % $block['ExecutionTime'];
			}
			$count = ($count * $block['ExecutionTime'] > $time)
				?  floor($time / $block['ExecutionTime'])
				: $count;			
			//Обновляем статус блока на выполненный путем присваивания даты
			$tableOrderProduct->setOrderProduct($block['ID'], $date + 86400 - $time, $count, 0);
			//Создаем новый блок, если текущий не модет быть выполнен полностью
			if ($count != $block['Count']) $tableOrderProduct->newOrderProduct($block['OrderID'], $block['ProductID'], 0, $block['Count'] - $count, $modifier);
			//Считаем остаток времени
			$time = $time - $count * $block['ExecutionTime'] + $block['Modifier'];
			//Рассчитываем результаты
			$manufacturedProducts['ProductId'] += $count;
			$plan[] = array('demandId' => $block['OrderID'],'productId' => $block['ProductID'], 'count' => $count);
		}	
		//Убираем потраченное сырье из БД
		$tableRaw = new Application_Model_DbTable_Raw();
		$tableRaw->spendRaw($manufacturedProducts);
		//Добавляем возможный простой оборудования
		if ($time != 0) $plan[] = array('demandId' => 0,'productId' => 4, 'count' => $time);
		return $plan;			
	}
				
}