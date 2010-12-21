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
			$db = $this->getDefaultAdapter();
			$select = $db->select()->from('RawRequiment')->where('RawCount > 0');
			$rawRequiments = $db->query($select)->fetchAll();
			foreach ($rawRequiments as $requiments) $count[$requiments['RawID']] += $requiments['RawCount'] * $products[$requiments['ProductID']];
			$select = $this->select();
			foreach ($this->fetchAll($select) as $row) {
				$data = array('Count' => $row['Count'] - $count[$row['ID']]);
				$where['ID = ?'] = $row['ID'];
				$this->update($data, $where);
			}		
			return TRUE;
		}

		public function addRaw($materials){
			$db = $this->getDefaultAdapter();
			$select = $db->select()->from($this->_name);
			$rowset = $db->query($select)->fetchAll();
			for ($i = 0; $i < 12; $i++) {
				$data = array(
					'Count' => $rowset[$i]['Count'] + $materials[$i]
					);
				$where['ID = ?'] = $rowset[$i]['ID'];
				$this->update($data, $where);
			} 
			return TRUE;
		}
	}