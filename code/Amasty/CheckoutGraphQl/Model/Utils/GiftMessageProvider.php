<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_CheckoutGraphQl
 */


namespace Amasty\CheckoutGraphQl\Model\Utils;

use Magento\GiftMessage\Model\Message;
use Magento\GiftMessage\Model\MessageFactory;

class GiftMessageProvider
{
    const MESSAGE_KEY = 'message';
    const SENDER_KEY = 'sender';
    const RECIPIENT_KEY = 'recipient';

    /**
     * @var MessageFactory
     */
    private $messageFactory;

    public function __construct(MessageFactory $messageFactory)
    {
        $this->messageFactory = $messageFactory;
    }

    /**
     * @param array $messageData
     * @return Message
     */
    public function prepareGiftMessage($messageData = []): Message
    {
        /** @var Message $message */
        $message = $this->messageFactory->create();
        $message->setData([
            self::MESSAGE_KEY => empty($messageData[self::MESSAGE_KEY]) ? '' : $messageData[self::MESSAGE_KEY],
            self::SENDER_KEY => empty($messageData[self::SENDER_KEY]) ? '' : $messageData[self::SENDER_KEY],
            self::RECIPIENT_KEY => empty($messageData[self::RECIPIENT_KEY]) ? '' : $messageData[self::RECIPIENT_KEY]
        ]);

        return $message;
    }
}
