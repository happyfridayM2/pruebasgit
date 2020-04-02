<?php
/**
 * Created by PhpStorm.
 * User: migue
 * Date: 02/06/2017
 * Time: 12:06
 */

namespace Oct8ne\Oct8ne\Controller\Frame;

use \Magento\Framework\App\Action\Context;
use \Oct8ne\Oct8ne\Helper\Oct8neModuleInfoHelper;
use \Oct8ne\Oct8ne\Helper\ResponseHelper;

class GetAdapterInfo extends \Magento\Framework\App\Action\Action
{

    /**
     * @var ProductSummaryHelper|Oct8neModuleInfoHelper
     */
    protected $_helper;

    /**
     * @var Context
     */
    protected $_context;

    protected $_responseHelper;

    /**
     * Inyeccion de dependencias. Inyectamos lo que nos hace falta
     * GetAdapterInfo constructor.
     * @param Context $context
     * @param ProductSummaryHelper $helper
     */

    public function __construct(Context $context, Oct8neModuleInfoHelper $helper, ResponseHelper $responseHelper)
    {
        $this->_context = $context;
        $this->_helper = $helper;
        $this->_responseHelper = $responseHelper;

        parent::__construct($context);
    }

    /**
     * Todos los controladores ejecutan el execute despues de la construccion
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {

        $data = array(
            "platform" => $this->_helper->getPlatform(),
            "adapterName" => $this->_helper->getAdapterName(),
            "adapterVersion" => $this->_helper->getAdapterVersion(),
            "developedBy" => $this->_helper->getDevelopedBy(),
            "supportUrl" => $this->_helper->getSupportUrl(),
            "apiVersion" => $this->_helper->getApiVersion(),
            "enabled" => $this->_helper->isEnabled()
        );

        return $this->_responseHelper->buildResponse($data);

    }
}