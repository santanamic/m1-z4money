<?php

class Z4money_Gateway_Block_Boleto_Form extends Mage_Payment_Block_Form {
    protected function _construct() {
        parent::_construct();
        $this->setTemplate('z4money/gateway/payment/form/boleto.phtml');
    }
}
