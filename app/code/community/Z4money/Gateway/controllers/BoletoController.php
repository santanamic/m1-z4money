<?php

class Z4money_Gateway_BoletoController extends Mage_Adminhtml_Controller_Action
{

    public function _isAllowed(){
        return true;
    }

    public function generateAction(){
        $order_id = $this->getRequest()->getParam('order_id');
        $order =  Mage::getModel('sales/order')->loadByIncrementId($order_id);
		
		try {
		
			$sale = $this->helper()->doBoletoPayment($order_id);
			$return = $sale->getPedido();
			$payment_status = $return['status_pedido_id'];

			if( true === $sale->getSuccess() ) {

				switch ( $payment_status ) {
					case '1': 
					
						//$this->helper()->setOrderAsPendingPayment($order);

						$this->helper()->addInformation($order, [
							'boleto_url' => $return['urlBoleto'],
							'payment_id' => $return['id'],
						]);

					break;
					
				}
				
				Mage::getSingleton('adminhtml/session')->addSuccess('Boleto gerado com sucesso!');
				return $this->_redirectReferer();
				
			} else {
				Mage::getSingleton('adminhtml/session')->addError('Problema ao gerar boleto. O gateway retornou um erro: ' . print_r($sale->getMessage(), true) );
				return $this->_redirectReferer();
			}
				
		} catch( Exception $e ) {
			Mage::logException($e);
			Mage::getSingleton('adminhtml/session')->addError('Problema ao gerar boleto: ' . print_r($e->getMessage(), true));
            return $this->_redirectReferer();
		}
	
    }

    protected function helper(){
        return Mage::helper('z4money_gateway/order');
    }

}