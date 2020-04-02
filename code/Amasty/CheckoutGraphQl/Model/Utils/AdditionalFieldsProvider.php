<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_CheckoutGraphQl
 */


namespace Amasty\CheckoutGraphQl\Model\Utils;

use Amasty\Checkout\Api\Data\AdditionalFieldsInterface;
use Amasty\Checkout\Model\AdditionalFields;
use Amasty\Checkout\Model\AdditionalFieldsFactory;

class AdditionalFieldsProvider
{
    /**
     * @var AdditionalFieldsFactory
     */
    private $fieldsFactory;

    public function __construct(AdditionalFieldsFactory $fieldsFactory)
    {
        $this->fieldsFactory = $fieldsFactory;
    }

    /**
     * @param array $fieldsData
     * @return AdditionalFields
     */
    public function prepareAdditionalFields($fieldsData = []): AdditionalFields
    {
        /** @var AdditionalFields $fields */
        $fields = $this->fieldsFactory->create();

        if (!empty($fieldsData[AdditionalFieldsInterface::COMMENT])) {
            $fields->setComment($fieldsData[AdditionalFieldsInterface::COMMENT]);
        }

        if (isset($fieldsData[AdditionalFieldsInterface::IS_SUBSCRIBE])) {
            $fields->setSubscribe($fieldsData[AdditionalFieldsInterface::IS_SUBSCRIBE]);
        }

        if (isset($fieldsData[AdditionalFieldsInterface::IS_REGISTER])) {
            $fields->setRegister($fieldsData[AdditionalFieldsInterface::IS_REGISTER]);
        }

        if (!empty($fieldsData[AdditionalFieldsInterface::REGISTER_DOB])) {
            $fields->setDateOfBirth($fieldsData[AdditionalFieldsInterface::REGISTER_DOB]);
        }

        return $fields;
    }
}
