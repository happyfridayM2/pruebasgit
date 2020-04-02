<?php

namespace Connectif\Integration\Controller\Newsletter;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Connectif\Integration\Helper\NewsletterHelper;
use Connectif\Integration\Helper\ConfigHelper;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Request\InvalidRequestException;

class OptOut extends Action implements CsrfAwareActionInterface
{
    const EMAIL_PARAM = 'email';
    const CHECKSUM_PARAM = 'checksum';
    private $_newsletterHelper;
    private $_configHelper;

    /**
     * OptIn constructor.
     * @param Context $context
     * @param NewsletterHelper $newsletterHelper
     * @param ConfigHelper $configHelper
     */
    public function __construct(
        Context $context,
        NewsletterHelper $newsletterHelper,
        ConfigHelper $configHelper
    ) {
        parent::__construct($context);
        $this->_newsletterHelper = $newsletterHelper;
        $this->_configHelper = $configHelper;
    }

    public function createCsrfValidationException(RequestInterface $request): ?InvalidRequestException
    {
        return null;
    }

    public function validateForCsrf(RequestInterface $request): ?bool
    {
        return true;
    }

    public function execute()
    {
        $email = $this->getRequest()->getParam(self::EMAIL_PARAM);
        $checksum = $this->getRequest()->getParam(self::CHECKSUM_PARAM);

        if (!$email || !$checksum) {
            return;
        }

        // TODO: If single Account / Else multiaccount
        $this->unsubscribeAccounts($email, $checksum, $this->getSecretKeysSingleAccount());
    }

    private function getSecretKeysSingleAccount()
    {
        return array($this->_configHelper->getSecretKey());
    }

    private function unsubscribeAccounts($email, $request_checksum, $secretKeys)
    {
        foreach ($secretKeys as $secretKey) {
            if ($secretKey === null) {
                continue;
            }
            $local_checksum = hash_hmac('sha1', $email, $secretKey);
            if ($local_checksum == $request_checksum) {
                $this->_newsletterHelper->unsubscribeEmail($email);
            }
        }
    }
}
