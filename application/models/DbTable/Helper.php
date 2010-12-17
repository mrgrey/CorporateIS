<?php
	class Application_Model_DbTable_Helper extends Zend_Db_Table_Abstract
	{
		protected $_name = 'Helper';
		
		public function getStartTime(){
			$db = $this->getDefaultAdapter();
			
			$select = $db->select()->from($this->_name)
								   ->where('ID = 1');
			
			$stmt = $db->query($select);			
			$res = $stmt->fetch();

			return $res['Value'];
		}
	}