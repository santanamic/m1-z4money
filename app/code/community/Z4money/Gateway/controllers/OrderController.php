<?php

class Z4money_Gateway_OrderController extends Mage_Core_Controller_Front_Action
{

    public function updateAction()
	{ @ob_clean();
		
		$_payload =  json_decode( file_get_contents("php://input"), true );
		
		$this->log( sprintf( 'Z4Money Gateway received a URL notification: %s', print_r( $_payload, true ) ) );
		
		if( json_last_error() == JSON_ERROR_NONE ) {

			$order = $this->helper()->getOrderByPaymentId($_payload['data']['id']);

			if($order) {

				$status = $_payload['data']['status_pedido_id'];

				switch ( $status ) {
					case '3': 
					case '4': 
					  if($order['status'] == 'pending'){ 
						   if($order->canCancel()) {
							$order->cancel()->save();
						}
						
					   }
					break;

					case '6': 
						$service = Mage::getModel('sales/service_order', $order);
						foreach ($order->getInvoiceCollection() as $invoice) {
							if ($invoice->canRefund()) {
								$creditmemo = $service->prepareInvoiceCreditmemo($invoice);
								$creditmemo->register();
								$creditmemo->save();
							}
						}
						$order->save();
				break;
						
					case '2': 
					case '7': 
						$order->setState(Mage_Sales_Model_Order::STATE_PROCESSING)->setStatus(Mage_Sales_Model_Order::STATE_PROCESSING);
						$history = $order->addStatusHistoryComment('Pedido aprovado através da Z4Money.', false);
						$history->setIsCustomerNotified(true);
						$order->save();
					break;
				}
			}
		}

		exit;
		
    }


    protected function helper(){
        return Mage::helper('z4money_gateway/data');
    }  

    private function log($msg){
        Mage::log($msg, null, 'z4money_notifications.log');
    }

}
