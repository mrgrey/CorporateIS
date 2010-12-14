<?php
	class Application_Model_DbTable_RawRequiment extends Zend_Db_Table_Abstract
	{
		protected $_name = 'RawRequiment';
		protected $_referenceMap    = array(
	        'Product' => array(	
	            'columns'           => 'ProductID',	
	            'refTableClass'     => 'Application_Model_DbTable_Product',	
	            'refColumns'        => 'ID'	
	        ),
	        'Raw' => array(
	        	'columns' 			=> 'RawID',
	        	'refTableClass'		=> 'Application_Model_DbTable_Raw',
	        	'refColumns' 		=> 'ID'
	        )       
	    );
			
    	public function getRequiments(){
    		$db = Zend_Db_Table_Abstract::getDefaultAdapter();
    		$select = $db->select()->from($this->_name);
    		$stmt = $db->query($select);
    		$result = $stmt->fetchAll();
    		return $result;
    	}
	    
	}