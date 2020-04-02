<?php

namespace Happyfriday\Customerlogin\Plugin\Customer;

class CreatePost
{
    protected $_url;
    protected $_responseHttp;

    public function __construct(
        \Magento\Framework\UrlInterface $url,
        \Magento\Framework\App\Response\Http $responseHttp
    ) {
        $this->_url = $url;
        $this->_responseHttp = $responseHttp;
    }

    public function aroundExecute(\Magento\Customer\Controller\Account\CreatePost $subject, $proceed)
    {
        $customerInformation = $subject->getRequest()->getPostValue();
        $password = $customerInformation['password'];
        $customerInformation['password_confirmation'] = $password;
        $subject->getRequest()->setParam('password_confirmation', $password);
        $result = $proceed();

        $url = $this->_url->getUrl('customer/account');
        $resultUrl = $this->_responseHttp->setRedirect($url);
        return $resultUrl;
    }


}