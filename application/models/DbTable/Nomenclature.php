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
	     * ѕолучаем все заказы нужного материала
	     * @param int $materialID
	     */
    	public function getNomenclatureForMaterial($materialID){
    		$db = Zend_Db_Table_Abstract::getDefaultAdapter();
    		$select = $db->select()->from($this->_name)->where('RawID = ?', $materialID);
    		$stmt = $db->query($select);
    		$result = $stmt->fetchAll();
    		return $result;
    	}
    	
    	public function insertIntoNomenclature($deliveryId, $rawId, $count){
    		$db = Zend_Db_Table_Abstract::getDefaultAdapter();
    		$select = $db->select()
    			->from($this->_name)
    			->where('DeliveryID = ?', $deliveryId)
    			->where('RawID = ?', $rawId);
				
    		$stmt = $db->query($select);
    		$res = $stmt->fetch();
			
    		$where['ID = ?'] = $res['ID'];
    		$data = array(
				'RealCount' => $count
			);
			
    		$db->update($this->_name, $data, $where);		
    	}
	}
	