<?php
namespace Oct8ne\Oct8ne\Helper;

use \Magento\Framework\App\Helper\AbstractHelper;
use \Magento\Framework\App\Helper\Context;
use \Magento\Framework\Module\ResourceInterface;



class Oct8neModuleInfoHelper extends AbstractHelper
{

    const MODULE_NAME = 'Oct8ne_Oct8ne';

    protected $_context;

    private $platform = "Magento 2";

    private $adapterName = "Oct8ne official adapter for Magento 2";

    private $adapterVersion = "";

    private $supportUrl = "http://www.oct8ne.com/support/magento";

    private $developedBy = "Oct8ne Inc";

    private $apiVersion = "2.3";

    private $enabled = false;


    /**
     * Oct8neModuleInfoHelper constructor.
     * @param Context $context
     */
    public function __construct(Context $context, ResourceInterface $moduleResource)
    {
        parent::__construct($context);

        $this->enabled = $context->getModuleManager()->isEnabled(self::MODULE_NAME) ? 'true' : 'false';

        $this->adapterVersion = $moduleResource->getDbVersion(self::MODULE_NAME);

    }

    /**
     * Devuelve la plataforma
     * @return string
     */
    public function getPlatform()
    {
        return $this->platform;
    }

    /**
     * Devuelve el nombre del adaptador
     * @return string
     */
    public function getAdapterName()
    {
        return $this->adapterName;
    }

    /**
     * Devuelve la version del adaptador (modulo)
     * @return string
     */
    public function getAdapterVersion()
    {
        return $this->adapterVersion;
    }

    /**
     * Devuelve la url de soporte
     * @return string
     */
    public function getSupportUrl()
    {
        return $this->supportUrl;
    }

    /**
     * Devuelve por quien está desarrollado
     * @return string
     */
    public function getDevelopedBy()
    {
        return $this->developedBy;
    }

    /**
     * Devuelve la version de la api
     * @return string
     */
    public function getApiVersion()
    {
        return $this->apiVersion;
    }

    /**
     * Devuelve si un modulo está actualizado o no
     * @return bool
     */
    public function isEnabled()
    {
        return $this->enabled;
    }

}