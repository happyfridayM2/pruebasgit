<?php

namespace IWD\All\Block\Adminhtml;

/**
 * Class Support
 * @package IWD\All\Block\Adminhtml
 */
class Support extends \Magento\Backend\Block\Template
{
    /**
     * @var \Magento\Backend\Model\Auth\Session
     */
    private $authSession;

    /**
     * @var \Magento\Framework\Module\ModuleListInterface
     */
    private $moduleList;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Model\Auth\Session $authSession,
        \Magento\Framework\Module\ModuleListInterface $moduleList,
        array $data = []
    ) {
        $this->authSession = $authSession;
        $this->moduleList = $moduleList;

        parent::__construct($context, $data);
    }

    /**
     * @return string
     */
    protected function _toHtml()
    {
        $section = $this->getRequest()->getParam('section', false);
        if ($section == 'iwd_support') {
            return parent::_toHtml();
        } else {
            return '';
        }
    }

    /**
     * @return string
     */
    public function getFormUrl()
    {
        return $this->_urlBuilder->getUrl('iwdall/support/send');
    }

    /**
     * @return string
     */
    public function getIwdExtensions()
    {
        $modules = $this->moduleList->getNames();

        $dispatchResult = new \Magento\Framework\DataObject($modules);
        $this->_eventManager->dispatch(
            'adminhtml_system_config_advanced_disableoutput_render_before',
            ['modules' => $dispatchResult]
        );
        $modules = $dispatchResult->toArray();

        sort($modules);

        $options = '';
        foreach ($modules as $moduleName) {
            if (strpos(strtolower($moduleName), 'iwd') === 0) {
                $options .= '<option value="' . $moduleName . '">' . $moduleName . '</option>';
            }
        }

        return $options;
    }

    /**
     * @return mixed|string
     */
    public function getAdminEmail()
    {
        return $this->authSession->getUser()->getEmail();
    }

    /**
     * @return mixed|string
     */
    public function getAdminName()
    {
        return $this->authSession->getUser()->getUsername();
    }
}
