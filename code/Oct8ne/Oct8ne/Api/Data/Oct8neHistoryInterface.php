<?php

/**
 * Created by PhpStorm.
 * User: migue
 * Date: 23/05/2017
 * Time: 15:50
 */

namespace Oct8ne\Oct8ne\Api\Data;

//DEFINICION DE LOS GETTERS PARA INTERACTUAR CON NUESTEO MODELO

interface Oct8neHistoryInterface
{

    //DEFINICION CAMPOS DE LA BASE DE DATOS
    const HISTORY_ID = "id_oct8nehistory";

    const CART_ID = "cart_id";

    const SESSION_ID = "session_id";

    const CREATION_TIME = 'creation_time';

    const UPDATE_TIME   = 'update_time';

    /**
     * GET ID
     * @return mixed
     */

    public function getId();

    /**
     * SET ID
     * @return mixed
     */
    public function setId($id);

    /**
     * GET CART ID
     * @return mixed
     */
    public function getCartId();

    /**
     * SET CART ID
     * @return mixed
     */
    public function setCartId($id);

    /**
     * GET COOKIE
     * @return mixed
     */
    public function getCookie();

    /**
     * SET COOKIE
     * @return mixed
     */
    public function setCookie($cookie);


    /**
     * @return mixed
     */
    public function getCreationTime();

    /**
     * @return mixed
     */
    public function getUpdateTime();

    /**
     * @param $creationTime
     * @return mixed
     */
    public function setCreationTime($creationTime);

    /**
     * @param $updateTime
     * @return mixed
     */
    public function setUpdateTime($updateTime);

}