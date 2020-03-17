<?php

class Z4money_Gateway_Model_Observer extends Varien_Event_Observer
{
    public function checkWebhook()
    {
		$this->api()->log( 'Start Z4money Webhook Update' );
		
		$return_webhook = $this->api()->addWebhook();

		if( $return_webhook['success'] != true ) {
			$this->api()->log( 'Return Z4money Webhook data error:' . print_r( $return_webhook, true ) );
		} else {
			$this->api()->log( 'Return Z4money Webhook data success:' . print_r( $return_webhook, true ) );
		}
    }
	
    protected function helper(){
        return Mage::helper('z4money_gateway/data');
    }    
	
	protected function api(){
        return Mage::helper('z4money_gateway/order');
    }
}
