<?php
namespace IWD\All\Controller\Adminhtml\Support;

use IWD\All\Model\Support;
use Magento\Backend\App\Action\Context;
use Magento\Backend\App\Action;

/**
 * Class Send
 * @package IWD\All\Controller\Adminhtml\Support
 */
class Send extends Action
{
    /**
     * @var \IWD\All\Model\Support
     */
    private $support;

    /**
     * Send constructor.
     * @param Context $context
     * @param Support $support
     */
    public function __construct(
        Context $context,
        Support $support
    ) {
        parent::__construct($context);
        $this->support = $support;
    }

    /**
     * @return $this
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();

        try {
            $params = $this->getRequest()->getParams();
            $this->support->sendTicket($params);
            $this->messageManager->addSuccessMessage(
                __('Thank you for contacting IWD Agency\'s support team. We will review your comment and contact you shortly.')
            );
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }

        return $resultRedirect->setPath('admin/system_config/edit', ['section' => 'iwd_support']);
    }
}
