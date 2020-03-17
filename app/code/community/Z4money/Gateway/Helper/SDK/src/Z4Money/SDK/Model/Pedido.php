<?php

namespace Z4Money\SDK\Model;

class Pedido
{
    public $container = [];

    public function __construct(array $data = null)
    {
        $this->container['tipo_pedido_id'] = isset($data['tipo_pedido_id']) ? $data['tipo_pedido_id'] : null;
        $this->container['status_pedido_id'] = isset($data['status_pedido_id']) ? $data['status_pedido_id'] : null;
        $this->container['splitted'] = isset($data['splitted']) ? $data['splitted'] : null;
        $this->container['id'] = isset($data['id']) ? $data['id'] : null;
        $this->container['usuario_id'] = isset($data['usuario_id']) ? $data['usuario_id'] : null;
        $this->container['cliente_id'] = isset($data['cliente_id']) ? $data['cliente_id'] : null;
        $this->container['estabelecimento_id'] = isset($data['estabelecimento_id']) ? $data['estabelecimento_id'] : null;
        $this->container['modified'] = isset($data['modified']) ? $data['modified'] : null;
        $this->container['created'] = isset($data['created']) ? $data['created'] : null;
        $this->container['urlBoleto'] = isset($data['urlBoleto']) ? $data['urlBoleto'] : null;
    }

    public function getTipoPedidoId()
    {
        return $this->container['tipo_pedido_id'];
    }

    public function SetTipoPedidoId($tipo_pedido_id)
    {
        $this->container['tipo_pedido_id'] = $tipo_pedido_id;

        return $this;
    }

    public function getStatusPedidoId()
    {
        return $this->container['status_pedido_id'];
    }

    public function setStatusPedidoId($status_pedido_id)
    {
        $this->container['status_pedido_id'] = $status_pedido_id;

        return $this;
    }

    public function getSplitted()
    {
        return $this->container['splitted'];
    }

    public function setSplitted($splitted)
    {
        $this->container['splitted'] = $splitted;

        return $this;
    }

    public function getId()
    {
        return $this->container['id'];
    }

    public function setId($id)
    {
        $this->container['id'] = $id;

        return $this;
    }

    public function getUsuarioId()
    {
        return $this->container['usuario_id'];
    }

    public function setUsuarioId($usuario_id)
    {
        $this->container['usuario_id'] = $usuario_id;

        return $this;
    }

    public function getClienteId()
    {
        return $this->container['cliente_id'];
    }

    public function setClienteId($cliente_id)
    {
        $this->container['cliente_id'] = $cliente_id;

        return $this;
    }

    public function getEstabelecimentoId()
    {
        return $this->container['estabelecimento_id'];
    }

    public function setEstabelecimentoId($estabelecimento_id)
    {
        $this->container['estabelecimento_id'] = $estabelecimento_id;

        return $this;
    }

    public function getModified()
    {
        return $this->container['modified'];
    }

    public function setModified($modified)
    {
        $this->container['modified'] = $modified;

        return $this;
    }

    public function getCreated()
    {
        return $this->container['created'];
    }

    public function setCreated($created)
    {
        $this->container['created'] = $created;

        return $this;
    }

    public function getUrlBoleto()
    {
        return $this->container['urlBoleto'];
    }

    public function setUrlBoleto($urlBoleto)
    {
        $this->container['urlBoleto'] = $urlBoleto;

        return $this;
    }
}
