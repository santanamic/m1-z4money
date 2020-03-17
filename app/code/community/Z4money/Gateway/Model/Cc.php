<?php
   
class Z4money_Gateway_Model_Cc extends Z4money_Gateway_Model_Payment {

    protected $_code                = 'z4money_gateway_cc';
    protected $_formBlockType       = 'z4money_gateway/cc_form';
    protected $_infoBlockType       = 'z4money_gateway/cc_info';

	protected $_isGateway           = true;
	protected $_canUseInternal      = true;
	protected $_canUseCheckout      = true;
    protected $_canOrder            = true;
	
    public function order(Varien_Object $payment, $amount)
    {		
        if($this->canOrder()){
			$order = $payment->getOrder();

			try {
				
				$sale = $this->api()->doCcPayment($order->getIncrementId());
				
				$this->api()->log( sprintf( 'The API response: %s', print_r( $sale, true ) ) );

				if( true === $sale->getSuccess() ) {
					
					$return = $sale->getPedido();
					$payment_status = $return['status_pedido_id'];
					
					$this->api()->log( sprintf( 'The API response: %s', print_r( $return, true ) ) );

					
					switch ( $payment_status ) {
						case '1': 
							$this->api()->addInformation($order, [
								'payment_id' => $return['id'],
							]);
	
						case '2': 
						case '7': 
							$this->api()->setOrderAsProcessingPayment($order);
							$this->api()->addInformation($order, [
								'payment_id' => $return['id'],
								'valor_bruto' => $return['valor_bruto'],
								'valor_liquido' => $return['valor_liquido'],
								'cc_bandeira' => $return['bandeira'],
								'cc_parcelas' => $return['parcelas'],
							]);

						break;

						default : 
							$this->api()->setOrderAsCancelPayment($order);
							$errorMsg = 'O pagamento não foi efetuado. Ocorreu um problema ao processar o seu cartão. Entre em contato com seu banco para obter mais detalhes.';

						break;
					
					}

				} else {

					$this->api()->log( sprintf( 'Unexpected API Connection Error BB1: %s', print_r($sale, true) ) );
					$errorMsg = $sale->getMessage();
				}

			} catch( Exception $e ) {				
				Mage::logException($e);
				$this->api()->log( sprintf( 'Unexpected API Connection Error VV3: %s', print_r($e, true) ) );
				Mage::throwException($e->getMessage());
			}
			
			if( isset($errorMsg) ) {
				Mage::throwException($errorMsg);
			}
			
			return $this;
			
        }
    }

	public function refund( Varien_Object $payment, $amount ) 
	{
		$order = $payment->getOrder();
		$order_id = $order->getId();
		$order_total = $order->getGrandTotal();
		
		$this->api()->log( sprintf('Order refund process: %s'), $order_id );
		$this->api()->log( sprintf('Order refund process $amount: %s'), $amount );
		
		try {
			$payment_id       = $order->getPayment()->getAdditionalInformation('payment_id');
			$order_status     = $this->api()->returnPaymentStatus( $payment_id );
			$order_status_id  = $order_status['venda']['status']['id'];
			$real_amount      = number_format( ($amount ?: $order_total), 2 );
			$cents_amount     = ( $real_amount ) * 100;

			switch ( $order_status_id ) {
				case '2': 
				case '5': 
				case '7': 
					$request = $this->api()->doPaymentRefund( $payment_id, $cents_amount );
					$this->api()->log( sprintf('Refund return request: %s', print_r( $request, true ) ) );

					if( $request['success'] == true ) {						
						$payment->setTransactionId(Mage_Sales_Model_Order_Payment_Transaction::TYPE_REFUND)
							->setParentTransactionId($payment->getRefundTransactionId())
							->setIsTransactionClosed(1)
							->setShouldCloseParentTransaction(1)
							->setTransactionAdditionalInfo(Mage_Sales_Model_Order_Payment_Transaction::RAW_DETAILS,array('z4money_status_id' => $order_status_id));
						
					} else {
						$this->api()->log( sprintf( 'Unexpected API Connection Error BT2: %s', print_r($order_status, true) ) );
						$errorMsg = $order_status->getMessage();					
					}
				break;
			}
		} catch (Exception $e) {
            Mage::log('Exception refund:');
			$this->api()->log( sprintf( 'Unexpected API Connection Error TT2: %s', print_r($e, true) ) );
            Mage::logException($e);
            Mage::throwException($e->getMessage());
        }
		
		return $this;
	}	
	
    public function getInstallmentsHtml(){
		$amount = $this->getInfoInstance()->getQuote()->getGrandTotal();
        return $this->helper()->getInstallments($amount);
    }   

}
