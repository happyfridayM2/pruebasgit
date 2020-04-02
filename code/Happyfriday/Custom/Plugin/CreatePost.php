<?php
namespace Happyfriday\Custom\Plugin;

class CreatePost
{
    /**
     * @var \Magento\Framework\Controller\Result\RedirectFactory
     */
    protected $resultRedirectFactory;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @param \Magento\Framework\Controller\Result\RedirectFactory $resultRedirectFactory
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     */
    public function __construct(
        \Magento\Framework\Controller\Result\RedirectFactory $resultRedirectFactory,
        \Magento\Framework\Message\ManagerInterface $messageManager
    ) {
        $this->resultRedirectFactory = $resultRedirectFactory;
        $this->messageManager = $messageManager;
    }

    public function aroundExecute(
        \Magento\Customer\Controller\Account\CreatePost $subject,
        \Closure $proceed
    ) {
            $recaptcha_response_field = $subject->getRequest()->getPost('captcha-response-register');
            if($recaptcha_response_field) {
                $secretKey = '6Lexd34UAAAAAMFhQXJELVz7l_RxnekLRVvrcTJx';
                $response = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=".$secretKey."&response=".$recaptcha_response_field."&remoteip=".$_SERVER['REMOTE_ADDR']);
                $result = json_decode($response, true);
                if(isset($result['success']) && ($result['success'])) {
                    return $proceed();
                } else {
                    $resultRedirect = $this->resultRedirectFactory->create();
                    $this->messageManager->addError(
                        __('There was an error with the recaptcha code, please try again.')
                    );
                    $resultRedirect->setPath('customer/account/login');
                    return $resultRedirect;
                }
            } else {
                $this->messageManager->addError(
                    __('There was an error with the recaptcha code, please try again.')
                );
                $resultRedirect = $this->resultRedirectFactory->create();
                $resultRedirect->setPath('customer/account/login');
                return $resultRedirect;
            }

        return $proceed();
    }
}