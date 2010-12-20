<?php
	class Application_Model_DbTable_Nomenclature extends Zend_Db_Table_Abstract
	{
		protected $_name = 'Nomenclature';
		protected $_referenceMap    = array(
	        'Delivery' => array(	
	            'columns'           => 'DeliveryID',	
	            'refTableClass'     => 'Application_Model_DbTable_Delivery',	
	            'refColumns'        => 'ID'	
	        ),
	        'Raw' => array(
	        	'columns' 			=> 'RawID',
	        	'refTableClass'		=> 'Application_Model_DbTable_Raw',
	        	'refColumns' 		=> 'ID'
	        )       
	    );
			
	    /**
	     * 
	     * Получение среднеквадратичного отклонения по номенклатуре поставок
	     */
	    public function getSigmaN(){
	    	$db = $this->getDefaultAdapter();
	    	$select = $db->select()->from($this->_name);
	    	$stmt = $db->query($select);
	    	$nomenclature = $stmt->fetchAll();
	    	$n = count($nomenclature)/12;
	    	foreach ($nomenclature as $nom){
	    		$realCount = ($nom['RealCount'] != 0)
	    			? $nom['RealCount']
	    			: $nom['Count'];
	    		$sum[$nom['RawID']] += ($nom['Count'] - $realCount)*($nom['Count'] - $realCount)/$n; 
	    		$count[$nom['RawID']] += $nom['Count'];
	    	}	    	
	    	for ($i = 1; $i <13; $i++){
	    		$result[$i] = sqrt($sum[$i]/$count[$i]);
	    	}
	    	return $result;
	    }
	    
	    public function newShopList($deliveryId, $shopList){
	    	for ($i = 1; $i < 13; $i++){
	    		$data = array(
	    			'DeliveryID' => $deliveryId,
	    			'RawID' 	 => $i,
	    			'Count'		 => $shopList[$i],
	    			'RealCount'	 => 0
	    			);
	    		$this->insert($data);
	    	}
	    	return TRUE;
	    }
	    
	    public function updateDeliveryNomenclature($deliveryId, $materials){
	    	$db = $this->getDefaultAdapter();
	    	$select = $db->select()->from($this->_name)->where('DeliveryID = ?', $deliveryId);
	    	$noms = $db->query($select)->fetchAll();
	    	foreach ($noms as $nom) {
	    		$data = array(
	    			'RealCount'	=> $materials[$nom['RawID'] + 1]
	    			);
	    		$where = $nom['ID'];
	    		$this-> update($data, $where);
	    	}
	    	$tableRaw = new Application_Model_DbTable_Raw();	    	
	    	/*for ($i = 0; $i < 12; $i++) {
	    		$data = array(
	    			'RealCount'	=> $material
	    			);
	    			$where['ID = ?'] = ???????????
	    		$this->update($data, $where);
	    	}*/
	    	return $tableRaw->addRaw($materials);
	    }
	}
	