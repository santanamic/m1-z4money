<?php
   
class Z4money_Gateway_Model_Payment extends Mage_Payment_Model_Method_Abstract {

	protected $_code                = 'z4money_gateway';
	protected $_isGateway           = true;
	protected $_isInitializeNeeded  = false;
	protected $_canUseInternal      = false;
	protected $_canUseCheckout      = false;

    protected $_allowCurrencyCode   = ['BRL'];
	
    protected function helper(){
        return Mage::helper('z4money_gateway/data');
    }    
	
	protected function api(){
        return Mage::helper('z4money_gateway/order');
    }
}
