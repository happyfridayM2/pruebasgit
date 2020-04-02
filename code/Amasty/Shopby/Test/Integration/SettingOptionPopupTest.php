<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Shopby
 */

// phpcs:ignoreFile

namespace Amasty\Shopby\Test\Integration;

use Magento\TestFramework\TestCase\AbstractBackendController;

class SettingOptionPopupTest extends AbstractBackendController
{
    public function setUp()
    {
        $this->resource = 'Amasty_ShopbyBase::option';
        $this->uri = 'backend/amshopby_option/option/settings/option_id/49/filter_code/attr_color';
        parent::setUp();
    }

    public function testAclHasAccess()
    {
        parent::testAclHasAccess();
        $body = $this->getResponse()->getBody();
        $this->assertContains('<fieldset class="fieldset admin__fieldset form-inline" id="featured_fieldset">', $body);
        $this->assertContains(' <fieldset class="fieldset admin__fieldset form-inline" id="seo_fieldset">', $body);
        $this->assertContains('<fieldset class="fieldset admin__fieldset form-inline" id="meta_data_fieldset">', $body);
        $this->assertContains('<fieldset class="fieldset admin__fieldset form-inline" id="product_list_fieldset">', $body);
    }
}
