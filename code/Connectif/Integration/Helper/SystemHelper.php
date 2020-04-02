<?php

namespace Connectif\Integration\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Module\ModuleListInterface;
use Magento\Framework\App\Filesystem\DirectoryList;

class SystemHelper extends AbstractHelper
{

    protected $_moduleList;
    protected $_dir;

    /**
     * @param Context $context
     */
    public function __construct(
        Context $context,
        DirectoryList $dir,
        ModuleListInterface $moduleList
    )
    {
        parent::__construct($context);
        $this->_moduleList = $moduleList;
        $this->_dir = $dir;
    }

    public function doPostRequestCn($action, $obj, $apiEndpoint)
    {  
        $data = json_encode($obj);
        $response = "";
        $url = $apiEndpoint . $action;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($data),
        ));
        $response = curl_exec($ch);
        curl_close($ch);

        if ($response == false || $response == '') {
            // Server is not responding
            return false;
        } else {
            return json_decode($response);
        }
    }

     /*
     * @return string
     */
    public function getModuleVersion()
    {
        $connectifModule = $this->_moduleList->getOne('Connectif_Integration');
        return $connectifModule['setup_version'];
    }

     /*
     * @return string
     */
    public function getConnectifAppConfig()
    {
        $base_path = $this->_dir->getPath('app');
        $jsonApplicationConfig = json_decode(
            file_get_contents(
                $base_path . '/code/Connectif/Integration/cn-application-config-string.json'
            )
        );

        return $jsonApplicationConfig->applicationConfigString;
    }
}