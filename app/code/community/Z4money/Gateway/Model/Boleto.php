<?php
   
class Z4money_Gateway_Model_Boleto extends Z4money_Gateway_Model_Payment {

    protected $_code                = 'z4money_gateway_boleto';
    protected $_formBlockType       = 'z4money_gateway/boleto_form';
    protected $_infoBlockType       = 'z4money_gateway/boleto_info';
	
	protected $_isGateway           = true;
	protected $_canUseInternal      = true;
	protected $_canUseCheckout      = true;
    protected $_canOrder            = true;
  
    public function order(Varien_Object $payment, $amount)
    {		
        if($this->canOrder()){
			$order = $payment->getOrder();

			try {
				
				$sale = $this->api()->doBoletoPayment($order->getIncrementId());
				$return = $sale->getPedido();
				$payment_status = $return['status_pedido_id'];
				
				if( true === $sale->getSuccess() ) {

					switch ( $payment_status ) {
						case '1': 

							//$this->api()->setOrderAsPendingPayment($order);

							$this->api()->addInformation($order, [
								'boleto_url' => $return['urlBoleto'],
								'payment_id' => $return['id'],
							]);

						break;
					
					}
				
				} else {

					$this->api()->log( sprintf( 'Unexpected API Connection Error: %s', print_r($sale, true) ) );
					$errorMsg = $sale->getMessage();
				}
			
			} catch( Exception $e ) {				
				Mage::logException($e);
				$this->api()->log( sprintf( 'Unexpected API Connection Error: %s', $e->getMessage() ) );
				Mage::throwException($e->getMessage());
			}
			
			if( isset($errorMsg) ) {
				Mage::throwException($errorMsg);
			}
			
			return $this;
			
        }
    }


}
