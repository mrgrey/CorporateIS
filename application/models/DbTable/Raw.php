<?php
	class Application_Model_DbTable_Raw extends Zend_Db_Table_Abstract
	{
		protected $_name = 'Raw';
		
		/**
		 * 
		 * Очищение склада от сырья
		 */
		public function setEmpty(){
			$data = array(
				'Count' => 0
			);
			
			$where['ID > ?'] = 0;
			
			$this->update($data, $where);
		}
		
		/**
		 * 
		 * Трата материалов
		 * @param mixed $products
		 */
		public function spendRaw($products){
			$tableRawRequment = new Application_Model_DbTable_RawRequiment();
			$rawRequiments = $tableRawRequment->getRequiments();			
			foreach ($rawRequiments as $requiments) $count[$requiments['RawID']] += $requiments['RawCount'] * $products[$requiments['ProductID']];
			$select = $this->select();
			foreach ($this->fetchAll($select) as $row) {
				$data = array('Count' => $row['Count'] - $count[$row['ID']]);
				$where['ID = ?'] = $row['ID'];
				$this->update($data, $where);
			}			
			return TRUE;
		}
					
	}