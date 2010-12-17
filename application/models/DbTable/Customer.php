<?php
	class Application_Model_DbTable_Customer extends Zend_Db_Table_Abstract
	{
		protected $_name = 'Customer';
		
		/**
		 * 
		 * Get Customer Name By Id
		 * @param string $name
		 */
		public function getCustomerId($name){
			$select = $this->select()
						   ->where('Name=?', $name);			
						   
			$result = $this->fetchRow($select);
			
			return $result['ID'];
		}
		
		public function newCustomer($customer){
			$customerData = array(
				'Name'	=> $customer,
			);
			
			//клевая штука, не знал, что инсерт что-то возвращает
			return $tableCustomer->insert($customerData);
		}
	}
