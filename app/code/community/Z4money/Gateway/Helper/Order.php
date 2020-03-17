<?php

class Z4money_Gateway_Helper_Order extends Mage_Core_Helper_Abstract {

    public function __construct()
    {
		require_once( dirname( __FILE__ ) . '/SDK/vendor/autoload.php' );
    }
	
	public function doPayment( $payment ) 
	{	
		$config = new Z4Money\Configuration();
		$config->setBearerAuth( $this->helper()->getApiToken() );
		
		$this->log( 'return Payment data request: ' . print_r($payment, true) );
		
		$requestPagamento = new Z4Money\SDK\Requests\Pagamento\PagamentoRequest( $payment, $config, true );

		try {
			$request = $requestPagamento->run();
			return $request;
		} catch (Exception $e) {
			$this->log( 'Exception when calling Order request->run: ' . $e->getMessage() );
		}
	}

	public function doRefund( $id ) 
	{	
		$config = new Z4Money\Configuration();
		$config->setBearerAuth( $this->helper()->getApiToken() );
		$requestRefund = new Z4Money\SDK\Requests\Pagamento\RefundRequest( $id, $config, true );

		try {
			$request = $requestRefund->run();
			return $request;
		} catch (Exception $e) {
			$this->log( 'Exception when calling Refund request->run: ' . $e->getMessage() );
		}
		
	}

	public function doStatus( $id ) 
	{	
		$config = new Z4Money\Configuration();
		$config->setBearerAuth( $this->helper()->getApiToken() );
		
		$requestStatus = new Z4Money\SDK\Requests\Pagamento\StatusRequest( $id, $config, true );

		try {
			$request = $requestStatus->run();
			return $request;
		} catch (Exception $e) {
			$this->log( 'Exception when calling Status request->run: ' . $e->getMessage() );
		}
		
	}
	
	public function checkWebhook() 
	{
		$webhook = $this->getWebhookData();
		
		$config = new Z4Money\Configuration();
		$config->setBearerAuth( $this->helper()->getApiToken() );
		
		$requestGetWebhook = new Z4Money\SDK\Requests\Pagamento\GetWebhookRequest( $webhook, $config, true );
		
		try {
			$request = $requestGetWebhook->run();
			return $request;
		} catch (Exception $e) {
			$this->log( 'Exception when calling Check Webhook request->run: ' . $e->getMessage() );

		}

	}
	
	public function addWebhook() 
	{
		$config = new Z4Money\Configuration();
		$config->setBearerAuth( $this->helper()->getApiToken() );

		$webhook           = $this->getWebhookData();
		$requestAddWebhook = new Z4Money\SDK\Requests\Pagamento\AddWebhookRequest( $webhook, $config, true );

		try {
			$request = $requestAddWebhook->run();				
			return $request;
		} catch (Exception $e) {
			$this->log( 'Exception when calling Add Webhook request->run: ' . $e->getMessage() );
		}
	}
	
	public function doBoletoPayment($order_id) 
	{
		$order = Mage::getModel('sales/order')->loadByIncrementId($order_id);

		if($order && $order->getId()){
			
			$address = $order->getBillingAddress();
			$taxvat = $order->getCustomerTaxvat();
			$birthdate = $order->getCustomerDob() ? explode( ' ', $order->getCustomerDob() )[0] : '';

			$customer = new Z4Money\SDK\Model\Cliente();
			$customer->setNome( $order->getCustomerName() );
			$customer->setCPF( $taxvat );
			$customer->setDataNascimento( $birthdate );
			$customer->setEmail( $order->getCustomerEmail() );
			$customer->setCelular( $address->getTelephone() );

			$customer_address = new Z4Money\SDK\Model\Endereco();
			$customer_address->setNumero( (isset($address->getStreet()[1])) ? $address->getStreet()[1] : 'NONE' );
			$customer_address->setLogradouro( (isset($address->getStreet()[0])) ? $address->getStreet()[0] : 'NONE' );
			$customer_address->setCEP( $address->getPostcode() );
			$customer_address->setCidade( $address->getCity() );
			$customer_address->setEstado( $this->helper()->getRegionCodeById($address->getRegionId()) );
			$customer_address->setComplemento( (isset($address->getStreet()[2])) ? $address->getStreet()[2] : 'NONE' );
			
			$payment = new Z4Money\SDK\Model\Pagamento();
			$payment->setTipoPagamentoId( Z4Money\SDK\Model\Pagamento::BOLETO );
			$payment->setDescricao( 'A compra será confirmada somente após a aprovação do pagamento.' );
			$payment->setDataVencimento( $this->helper()->getBoletoExpiration() );
			$payment->setValor( number_format( $order->getGrandTotal(), 2 ) );
			$payment->setCliente( $customer );
			$payment->setEndereco( $customer_address );

			return $this->doPayment( $payment );
		}
	}

	public function doCcPayment($order_id) 
	{
		$order = Mage::getModel('sales/order')->loadByIncrementId($order_id);
			
		if($order && $order->getId()){
			
			$cc_holdername = Mage::app()->getRequest()->getParam('z4money_holdername');
			$cc_taxvat = $order->getCustomerTaxvat();
			$cc_number = Mage::app()->getRequest()->getParam('z4money_ccnumber');
			$cc_cvv = Mage::app()->getRequest()->getParam('z4money_cccvv');
			$cc_expiration = Mage::app()->getRequest()->getParam('z4money_ccexpiration') . '/' . Mage::app()->getRequest()->getParam('z4money_ccexpiration_yr');
			$cc_installments = Mage::app()->getRequest()->getParam('z4money_ccinstallments');
			$cc_totais = $this->helper()->getCreditInterestData( $cc_installments, $order->getGrandTotal() )[0];
			
			$birthdate = $order->getCustomerDob() ? explode( ' ', $order->getCustomerDob() )[0] : '';
			
			$address = $order->getBillingAddress();

			$customer = new Z4Money\SDK\Model\Cliente();
			$customer->setNome( $order->getCustomerName() );
			$customer->setCPF( $cc_taxvat );
			$customer->setDataNascimento( $birthdate );
			$customer->setEmail( $order->getCustomerEmail() );
			$customer->setCelular( $address->getTelephone() );

			$customer_address = new Z4Money\SDK\Model\Endereco();
			$customer_address->setNumero( (isset($address->getStreet()[1])) ? $address->getStreet()[1] : 'NONE' );
			$customer_address->setLogradouro( (isset($address->getStreet()[0])) ? $address->getStreet()[0] : 'NONE' );
			$customer_address->setCEP( $address->getPostcode() );
			$customer_address->setCidade( $address->getCity() );
			$customer_address->setEstado( $this->helper()->getRegionCodeById($address->getRegionId()) );
			$customer_address->setComplemento( (isset($address->getStreet()[2])) ? $address->getStreet()[2] : 'NONE' );

			$card = new Z4Money\SDK\Model\Cartao();
			$card->SetTitular( $cc_holdername );
			$card->setStatusNumero( $cc_number );
			$card->setCodigoSeguranca( $cc_cvv );
			$card->setValidade( $cc_expiration );
		
			$payment = new Z4Money\SDK\Model\Pagamento();
			$payment->setTipoPagamentoId( Z4Money\SDK\Model\Pagamento::CARTAO_DE_CREDITO  );
			$payment->setValor( $cc_totais );
			$payment->setCliente( $customer );
			$payment->setEndereco( $customer_address );
			$payment->setCartao( $card );
			$payment->setParcelas( $cc_installments );
		
			return $this->doPayment( $payment );
		}
	}
	
	public function doPaymentRefund( $payment_id, $amount ) 
	{
		$id = new Z4Money\SDK\Model\Id();
		$id->setPaymentId( $payment_id );
		$id->setAmount( $amount );

		return $this->doRefund( $id );
	}
	
	public function returnPaymentStatus( $payment_id ) 
	{
		$id = new Z4Money\SDK\Model\Id();
		$id->setPaymentId( $payment_id );

		return $this->doStatus( $id );
	}

	public function getWebhookData() 
	{
		$data    = array( 'slug' => 'url_webhook', 'value' => $this->helper()->getNotificationUrl() );	
		$webhook = new Z4Money\SDK\Model\Webhook( $data );
		
		return $webhook;
	}
	
    public function addInformation($order, $additional){
        if($order && $order->getId() && is_array($additional) && count($additional) >= 1){
            foreach ($additional as $key => $value) {
                $order->getPayment()->setAdditionalInformation($key, $value)->save();
            }
        }
    }

    public function setOrderAsPendingPayment($order)
    {
        $message = 'O pedido está aguardando pagamento';
        $notifyCustomer = true;
        $order->setState(
            Mage_Sales_Model_Order::STATE_PENDING_PAYMENT,
            Mage_Sales_Model_Order::STATE_PENDING_PAYMENT,
            $message,
            $notifyCustomer
        );
    }

    public function setOrderAsProcessingPayment($order)
    {
        $message = 'O pedido foi confirmado e está em processamento';
        $notifyCustomer = true;
        $order->setState(
            Mage_Sales_Model_Order::STATE_PROCESSING,
            Mage_Sales_Model_Order::STATE_PROCESSING,
            $message,
            $notifyCustomer
        );
    }

    public function setOrderAsCancelPayment($order)
    {
        $message = 'Houve um erro ao concluir o pedido.';
        $notifyCustomer = true;
        $order->setState(
            Mage_Sales_Model_Order::STATE_CANCELED,
            Mage_Sales_Model_Order::STATE_CANCELED,
            $message,
            $notifyCustomer
        );
    }
	
    protected function helper(){
        return Mage::helper('z4money_gateway');
    }

    public function log($msg){
        Mage::log($msg, null, 'z4money.log');
    }

}
