<?php
	class Application_Model_DbTable_Delivery extends Zend_Db_Table_Abstract
	{
		protected $_name = 'Delivery';
		
		/**
		 * 
		 * Получение среднеквадратичного отклонения по времени
		 */
		public function getSigmaT(){
			$db = $this->getDefaultAdapter();
			$select = $db->select()->from($this->_name);
			$stmt = $db->query($select);
			$deliveries = $stmt->fetchAll();
			$n = count($deliveries);
			$sum = 0;
			foreach ($deliveries as $delivery){
				$realDate = ($delivery['RealDate'] != 0)
					? $delivery['RealDate']
					: $delivery['Date'] + 604800;
				$sum += ($delivery['Date'] - $realDate)*($delivery['Date'] - $realDate)/$n;
				$lastDeliveryId = $delivery['ID'];
			}			
			return (sqrt($sum)/604800);			
		}
		
		
		public function newDelivery($date, $shopList){
			$data = array(
				'Date'		=> $date,
				'RealDate'	=> 0
				);
			$deliveryId = $this->insert($data);
			$tableNomenclature = new Application_Model_DbTable_Nomenclature();
			return $tableNomenclature->newShopList($deliveryId, $shopList);
		}
		
		/**
		 * 
		 * Update заяки
		 * @param int $date
		 * @param int $deliveryId
		 */
		public function setDelivery($date, $deliveryId){						
			$data = array(
				'RealDate' => $date
			);			
			$where['ID = ?'] = $deliveryId;			
			return $this->update($data, $where);
		}
		
		/**
		 * 
		 * Проверяем была ли поставка на прошлой неделе
		 * @param int $date
		 */
		public function wasLastWeekDelivery($date){			
			$select = $this->select()
				->from($this->_name)
				->where('Date = ?', $date - 345600)
				->where('RealDate > 0')
				;
			$stmt = $this->fetchAll($select);			
			return count($stmt) > 0;			
		}
				
	}