<?php

/**
 * Created by PhpStorm.
 * User: migue
 * Date: 23/05/2017
 * Time: 15:58
 */

namespace Oct8ne\Oct8ne\Model;

use \Oct8ne\Oct8ne\Api\Data\Oct8neHistoryInterface;
use \Magento\Framework\DataObject\IdentityInterface;

class Oct8neHistory extends \Magento\Framework\Model\AbstractModel implements IdentityInterface, Oct8neHistoryInterface
{


    const CACHE_TAG = 'oct8nehistory';

    //identificador unico para usar cache
    protected $_cacheTag = 'oct8nehistory';

    //prefijo para eventos que se disparan
    protected $_eventPrefix = 'oct8nehistory';


    /**
     * Inicializar resource model
     */
    protected function _construct()
    {
        $this->_init('Oct8ne\Oct8ne\Model\ResourceModel\Oct8neHistory');
    }

    /**
     * Devuelve un id unico para cada objeto en el sist
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }


    /**
     * Devuelve la id
     * @return mixed
     */
    public function getId()
    {
        return $this->getData(self::HISTORY_ID);
    }

    /**
     * Establece la id
     * @param mixed $id
     * @return $this
     */
    public function setId($id)
    {
        return $this->setData(self::HISTORY_ID, $id);
    }

    /**
     * Obtiene la id del carrito
     * @return mixed
     */
    public function getCartId()
    {
        return $this->getData(self::CART_ID);
    }

    /**
     * Establece la id del carrito
     * @param $id
     * @return $this
     */
    public function setCartId($id)
    {
        return $this->setData(self::CART_ID, $id);
    }

    /**
     * Obtiene la session (cookie)
     * @return mixed
     */
    public function getCookie()
    {
        return $this->getData(self::SESSION_ID);

    }

    /**
     * Establece la sesion
     * @param $cookie
     * @return $this
     */
    public function setCookie($cookie)
    {
        return $this->setData(self::SESSION_ID, $cookie);
    }


    /**
     * Get creation time
     *
     * @return string|null
     */
    public function getCreationTime()
    {
        return $this->getData(self::CREATION_TIME);
    }

    /**
     * Get update time
     *
     * @return string|null
     */
    public function getUpdateTime()
    {
        return $this->getData(self::UPDATE_TIME);
    }

    /**
     * Set creation time
     * @param $creation_time
     * @return $this
     */
    public function setCreationTime($creation_time)
    {
        return $this->setData(self::CREATION_TIME, $creation_time);
    }


    /**
     * Set update time
     * @param $update_time
     * @return $this
     */
    public function setUpdateTime($update_time)
    {
        return $this->setData(self::UPDATE_TIME, $update_time);
    }
}