<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Block\Adminhtml\GoogleWizard\Edit\Tab;

use Amasty\Feed\Block\Adminhtml\Feed\Edit\Tab\Buttons\TestConnection;

/**
 * Class Delivery
 *
 * @package Amasty\Feed
 */
class Delivery extends TabGeneric
{
    /**
     * @var \Amasty\Feed\Model\FormFieldDependencyFactory
     */
    private $dependencyFactory;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Amasty\Feed\Model\RegistryContainer $registryContainer,
        \Amasty\Feed\Model\FormFieldDependencyFactory $dependencyFactory,
        array $data = []
    ) {
        $this->feldsetId = 'amfeed_delivery';
        $this->legend = __('Upload feeds to Google servers automatically?');
        $this->dependencyFactory = $dependencyFactory;

        parent::__construct($context, $registry, $formFactory, $registryContainer, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function getTabLabel()
    {
        return __('Step 7: Upload to Google Server');
    }

    /**
     * {@inheritdoc}
     */
    public function getTabTitle()
    {
        return __('Step 7: Upload to Google Server');
    }

    /**
     * {@inheritdoc}
     */
    protected function prepareNotEmptyForm()
    {
        /** @var \Amasty\Feed\Model\FormFieldDependency $dependency */
        $dependency = $this->dependencyFactory->create();

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('feed_');

        $fieldset = $form->addFieldset(
            'delivery_fieldset',
            ['legend' => __('Upload feeds to google server automatically?')]
        );

        $enabledSelect = $fieldset->addField(
            'delivery_enabled',
            'select',
            [
                'label' => __('Enabled'),
                'title' => __('Enabled'),
                'name' => 'delivery_enabled',
                'options' => [
                    '1' => __('Yes'),
                    '0' => __('No')
                ]
            ]
        );

        $fieldset->addField(
            'delivery_host',
            'text',
            [
                'name' => 'delivery_host',
                'label' => __('Host'),
                'title' => __('Host'),
                'required' => true,
                'note' => '<small>' . __('Add port if necessary (example.com:321)') . '</small>'
            ]
        );

        $typeSelect = $fieldset->addField(
            'delivery_type',
            'select',
            [
                'label' => __('Protocol'),
                'title' => __('Protocol'),
                'name' => 'delivery_type',
                'options' => [
                    'ftp' => __('FTP'),
                    'sftp' => __('SFTP')
                ],
            ]
        );

        $fieldset->addField(
            'delivery_user',
            'text',
            [
                'name' => 'delivery_user',
                'label' => __('User'),
                'title' => __('User'),
                'required' => true
            ]
        );

        $fieldset->addField(
            'delivery_password',
            'password',
            [
                'name' => 'delivery_password',
                'label' => __('Password'),
                'title' => __('Password'),
                'required' => true
            ]
        );

        $fieldset->addField(
            'delivery_path',
            'text',
            [
                'name' => 'delivery_path',
                'label' => __('Path'),
                'title' => __('Path'),
                'required' => true
            ]
        );

        $modeSelect = $fieldset->addField(
            'delivery_passive_mode',
            'select',
            [
                'label' => __('Passive Mode'),
                'title' => __('Passive Mode'),
                'name' => 'delivery_passive_mode',
                'options' => [
                    '1' => __('Yes'),
                    '0' => __('No')
                ]
            ]
        );

        $fieldset->addField('button', 'button', [])
            ->setRenderer($this->getLayout()->createBlock(TestConnection::class));

        foreach ($fieldset->getChildren() as $element) {
            if ($element->getHtmlId() !== $enabledSelect->getHtmlId()) {
                $dependency->addDepend($element->getHtmlId(), $enabledSelect->getHtmlId(), '1');
            }
        }
        $dependency->addDepend($modeSelect->getHtmlId(), $typeSelect->getHtmlId(), 'ftp');
        $dependency->depend($this);

        $fieldset->addField(
            'setup_complete',
            'hidden',
            [
                'name'  => 'setup_complete',
                'value' => 1
            ]
        );

        $this->setForm($form);

        return $this;
    }
}
