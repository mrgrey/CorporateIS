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
    		$count = array_fill(1, 3, 9999);
    		foreach ($rowset as $row) $count[$row['ProductID']] = min($count[$row['ProductID']], floor($row['Count']/$row['RawCount']));    		
    		return $count;
    	}
    	
    	public function getRequiments(){
    		$select = $this->select()
    			->where('RawCount > 0')
    			;
    		return $this->fetchAll($select);
    	}
    	
    	/**
    	 * 
    	 * Определяет количество материалов, необходимов для производства переданных продуктов, с учетом отклонений
    	 * @param unknown_type $products
    	 * @param unknown_type $sigmaN
    	 * @param unknown_type $sigmaT
    	 */
    	public function getShoppingList($products, $sigmaN, $sigmaT){
    		$db = $this->getDefaultAdapter();
			$select = $db->select()->from('RawRequiment')->where('RawCount > 0');
			$requiments = $db->query($select)->fetchAll();   		
    		
    		foreach ($requiments as $row){
    			$k = 1 + $sigmaN[$row['RawID']] + $sigmaT;
    			$result[$row['RawID']] += $k * $products[$row['ProductID']] * $row['RawCount'];
    		}
    		for ($i = 1; $i < 13; $i++){
    			$result[$i] = round($result[$i]);
    		}
    		return $result;
    	}
    	
	}
