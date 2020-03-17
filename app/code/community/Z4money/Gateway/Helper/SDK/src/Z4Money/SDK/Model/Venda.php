<?php

namespace Z4Money\SDK\Model;

class Venda
{
    public $container = [];

    public function __construct(array $data = null)
    {
        $this->container['success'] = isset($data['success']) ? $data['success'] : null;
        $this->container['pedido'] = isset($data['pedido']) ? $data['pedido'] : null;
        $this->container['error'] = isset($data['error']) ? $data['error'] : null;
        $this->container['message'] = isset($data['message']) ? $data['message'] : null;
    }

    public function getSuccess()
    {
        return $this->container['success'];
    }

    public function setSuccess($success)
    {
        $this->container['success'] = $success;

        return $this;
    }

    public function getPedido()
    {
        return $this->container['pedido'];
    }

    public function setPedido($pedido)
    {
        $this->container['pedido'] = $pedido->container;

        return $this;
    }

    public function getError()
    {
        return $this->container['error'];
    }

    public function setError($error)
    {
        $this->container['error'] = $error;

        return $this;
    }

    public function getMessage()
    {
        return $this->container['message'];
    }

    public function setMessage($message)
    {
        $this->container['message'] = $message;

        return $this;
    }
}
