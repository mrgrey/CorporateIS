<?php
	class Application_Model_DbTable_Product extends Zend_Db_Table_Abstract
	{
		protected $_name = 'Product';
		
		/**
		 * 
		 * Get Product Execution Time
		 * @return mixed
		 *
		 */
		public function getRetunningExecutionProductTime(){
			$db = Zend_Db_Table_Abstract::getDefaultAdapter();
			$select = $db->select()->from($this->_name);
			$stmt = $db->query($select);
			while ($row = $stmt->fetch()){
				$result[$row['ID']] = array('ExecutionTime' => $row['ExecutionTime'], 'RetunningTime' => $row['RetunningTime']);				
			}
			return $result;
		}
		
	}