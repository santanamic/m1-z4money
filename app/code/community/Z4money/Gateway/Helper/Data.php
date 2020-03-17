<?php
class Z4money_Gateway_Helper_Data extends Mage_Core_Helper_Abstract
{

    public function getApiToken(){
        return Mage::getStoreConfig('payment/z4money_gateway/general_api_key');
    }
	
    public function getConfig($c){
		
		$options = array(
			'days_to_boleto_expire'        => 'z4money_gateway_cc',
			'creditcard_max_installments'  => 'z4money_gateway_cc',
			'creditcard_interest_rate'     => 'z4money_gateway_cc',
			'creditcard_free_installments' => 'z4money_gateway_cc',
			'creditcard_free_installments' => 'z4money_gateway_cc',
			'days_to_boleto_expire'        => 'z4money_gateway_boleto',
		);
		
		if( isset( $options[$c] ) ) {
			return Mage::getStoreConfig('payment/' . $options[$c] . '/' . $c);
		}
		
        return null;
    }

	public function getBoletoExpiration()
	{
		$boletoExpire = $this->getConfig('days_to_boleto_expire') ?: 1;
		return $this->addDaysCurrentDate($boletoExpire);
	}	
	
	public function getRegionCodeById($id)
	{
		$regionModel = Mage::getModel('directory/region')->load($id);
		return $regionModel->getCode();
	}
	
	public function getInstallments( $amount = null ) 
	{       
	   if (is_null($amount)) {
           $amount = Mage::getSingleton('checkout/cart')->getQuote()->getGrandTotal();

        }
		
		$html         = '';        
		$installments = $this->getConfig('creditcard_max_installments');

		$html .= '<select id="z4money_ccinstallments" name="z4money_ccinstallments" class="z4money_ccinstallments">';

		$_interest_rate = $this->getValidValue( $this->getConfig('creditcard_interest_rate') );
		$interest_rate = $this->getValidValue( $_interest_rate ) / 100;

		for ( $i = 1; $i <= $installments; $i++ ) {
			$credit_total    = $amount / $i;
			$credit_interest = sprintf( 'sem juros. Total: %s', $this->getMoneyFormat( $amount ) );
			$smallest_value  = ( 5 <= $this->getConfig('creditcard_min_installment_value') ) ? $this->getConfig('creditcard_min_installment_value') : 5;

			if ( $i >= $this->getConfig('creditcard_free_installments') && 0 < $interest_rate ) {
				$interest_total = $amount * ( $interest_rate / ( 1 - ( 1 / pow( 1 + $interest_rate, $i ) ) ) );
				$interest_amount = $interest_total * $i;

				if ( $credit_total < $interest_total ) {
					$credit_total    = $interest_total;
					$credit_interest = sprintf( 'com juros de %s%% a.m. Total: %s', $this->getValidValue( $_interest_rate ), $this->getMoneyFormat( $interest_amount ) );
				}
			}

			if ( 1 != $i && $credit_total < $smallest_value ) {
				continue;
			}

			$at_sight = ( 1 == $i ) ? 'z4money_atsight' : '';

			$html .= '<option value="' . $i . '" class="' . $at_sight . '">' . sprintf( '%sx de %s %s', $i, $this->getMoneyFormat( $credit_total ), $credit_interest ) . '</option>';
		}

		$html .= '</select>';
			
		return $html;
	}
	
	public function getValidValue( $value ) 
	{
		$value = str_replace( '%', '', $value );
		$value = str_replace( ',', '.', $value );

		return $value;
	}

	public function getMoneyFormat( $value ) 
	{
		$value = number_format( $value, 2, ',', '.' );
	
		return 'R$' . $value;
	}
	
	public function getCreditInterestData($installments, $amount)
	{
		$amount          = (float) $amount;
		$interest_amount = $amount;
		$real_interest_total  = 0;
		$interest_rate        = 0;
			
		if ( $installments >= $this->getConfig('creditcard_free_installments') ) {
			$interest_rate        = $this->getValidValue( $this->getConfig('creditcard_interest_rate') ) / 100;
			$interest_total       = number_format( $amount * ( $interest_rate / ( 1 - ( 1 / pow( 1 + $interest_rate, $installments ) ) ) ), 2 );
			$interest_amount = $interest_total * $installments;
			$interest_order_calc  = $interest_amount - $amount; // fix interest total for 1 installments
			$real_interest_total  = $interest_order_calc < 0 ? 0 : $interest_order_calc; 
		}

		return [ 
			$interest_amount, 
			$real_interest_total, 
			$interest_rate * 100 
		];
	}
	
    public function getNotificationUrl(){
        return Mage::getUrl('z4money_gateway/order/update');
    }
	
	public function addDaysCurrentDate( $days )
	{
		$date = date('Y-m-d');
		$_days = '+' . $days . ' days'; 
		return date( 'Y-m-d', strtotime( $_days, strtotime( $date ) ) );
	}

	public function getOrderByPaymentId( $payment_id )
	{
		$resource = Mage::getSingleton('core/resource');
		$readConnection = $resource->getConnection('core_read');
		$query = str_replace('_PAYMENT_ID_', $payment_id, "SELECT `entity_id` FROM `sales_flat_order_payment` WHERE (`method` = 'z4money_gateway_boleto' OR `method` = 'z4money_gateway_cc') AND `additional_information` LIKE '%i:_PAYMENT_ID_;%' OR `additional_information` LIKE '%:_PAYMENT_ID_;%'");
		$order_id = $readConnection->fetchOne($query);
		
		if($order_id) {
			$order = Mage::getModel('sales/order')->load($order_id);
			
			if( $order->getPayment()->hasAdditionalInformation('payment_id') && $order->getPayment()->getAdditionalInformation('payment_id') == $payment_id ) {
				return $order;
			}
		}
		
		return false;
	}
	
	
}