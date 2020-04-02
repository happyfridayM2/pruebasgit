<?php

namespace Oct8ne\Oct8ne\Model\ResourceModel;
/**
 * Created by PhpStorm.
 * User: migue
 * Date: 23/05/2017
 * Time: 16:45
 */
class Oct8neHistory extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb

{

    /**
     * Resource initialization
     * Nombre de la tabla y clave primaria
     * @return void
     */
    protected function _construct()
    {
        $this->_init('oct8nehistory', 'id_oct8nehistory');
    }

    /**
     * Comprueba si existe el carrito dentro de la tabla history. Si existe devuelve la id
     * @param $cartId
     */
    public function existHistoryByCartId($cartId){

        $return = false;

        $adapter = $this->getConnection();

        $select = $adapter->select()
                  ->from($this->getMainTable(), '*')
                  ->where('cart_id = :cartId')
                  ->limit(1);

        $binds = ['cartId' => (int)$cartId];

        $result = $adapter->fetchOne($select, $binds);

        return $result;

    }

}