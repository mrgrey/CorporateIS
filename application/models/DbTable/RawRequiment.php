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
			
    	public function getAvaibleProductCount(){
    		$db = $this->getDefaultAdapter();
    		$select = $db->select()
    			->from('RawRequiment')
    			->join('Raw', 'RawRequiment.RawID = Raw.ID', array('Count'))    			
    			->where('RawCount > 0')
    			;
    		$result = $db->query($select);
    		$rowset = $result->fetchAll();
    		//Я хз как по-другому написать=(
    		$count = array(
    			1 => 9999,
    			2 => 9999,
    			3 => 9999
     			);
    		foreach ($rowset as $row) $count[$row['ProductID']] = min($count[$row['ProductID']], floor($row['Count']/$row['RawCount']));    		
    		return $count;
    	}
    	
    	public function getRequiments(){
    		$select = $this->select()
    			->where('RawCount > 0')
    			;
    		return $this->fetchAll($select);
    	}
    	
	}