<?php
require_once realpath(APPLICATION_PATH . '/../library/').'/Simulation.php';
class SimulationController extends Zend_Controller_Action
{

	private $_WSDL_URI = "http://corporateSys/simulation?wsdl";
	
    public function init()
    {
        /* Initialize action controller here */
    }

 	public function indexAction()
    {
    	$this->_helper->viewRenderer->setNoRender();
    	if(isset($_GET['wsdl'])) {
    		//return the WSDL
    		$this->hadleWSDL();
		} else {
			//handle SOAP request
    		$this->handleSOAP();
		}
    }
    
	private function hadleWSDL() {
		$autodiscover = new Zend_Soap_AutoDiscover();
    	$autodiscover->setClass('Simulation');
    	$autodiscover->handle();
	}
    
	private function handleSOAP() {
		$soap = new Zend_Soap_Server($this->_WSDL_URI); 
    	$soap->setClass('Simulation');
    	$soap->handle();
	}


}

