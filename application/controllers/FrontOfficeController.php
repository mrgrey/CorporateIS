<?php
require_once realpath(APPLICATION_PATH . '/../library/').'/FrontOffice.php';
class FrontOfficeController extends Zend_Controller_Action
{
	
	private $_WSDL_URI;

    public function init()
    {
		$domain = Zend_Registry::getInstance()->projSettings->settings->domain;
		$this->_WSDL_URI = "http://{$domain}/frontOffice?wsdl";
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
    	$autodiscover->setClass('FrontOffice');
    	$autodiscover->handle();
	}
    
	private function handleSOAP() {
		$soap = new Zend_Soap_Server($this->_WSDL_URI); 
    	$soap->setClass('FrontOffice');
    	$soap->handle();
	}


}

