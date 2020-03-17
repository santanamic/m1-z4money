<?php

class Z4money_Gateway_Block_Boleto_Info extends Mage_Payment_Block_Info {
    protected function _construct() {
        parent::_construct();
        $this->setTemplate('z4money/gateway/payment/info/boleto.phtml');
    }
}
