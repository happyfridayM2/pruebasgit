<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Cart
 */


namespace Amasty\Cart\Plugin\DataPost\Controller;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Message\MessageInterface;

class Ajax
{
    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var ResultFactory
     */
    private $resultFactory;

    /**
     * @var ManagerInterface
     */
    private $messageManager;

    public function __construct(
        RequestInterface $request,
        ResultFactory $resultFactory,
        ManagerInterface $messageManager
    ) {
        $this->request = $request;
        $this->resultFactory = $resultFactory;
        $this->messageManager = $messageManager;
    }

    /**
     * @param Action $subject
     * @param mixed $result
     *
     * @return mixed
     */
    public function afterExecute(Action $subject, $result)
    {
        if ($this->request->getParam('is_ajax', null)) {
            $result = $this->resultFactory->create(ResultFactory::TYPE_JSON)
                ->setData([]);

            $messageCollection = $this->messageManager->getMessages();
            /** @var MessageInterface $message */
            foreach ($messageCollection->getItems() as $message) {
                // replace message for wishlist added
                if ($message->getIdentifier() == 'addProductSuccessMessage') {
                    $message->setIdentifier('addAmProductSuccessMessage');
                }
            }
        }

        return $result;
    }
}
