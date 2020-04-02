<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Block\Adminhtml\GoogleWizard\Edit\Tab;

use Amasty\Feed\Block\Adminhtml\Category\Edit\Tab\ExcludeMapping as TabMapping;
use Amasty\Feed\Block\Adminhtml\Category\Edit\Tab\General as CategoryGeneral;
use Amasty\Feed\Model\Category\Notes;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class ExcludeCategories
 *
 * @package Amasty\Feed
 */
class ExcludeCategories extends TabGeneric
{
    /**
     * @var \Amasty\Feed\Model\Category\Repository
     */
    private $repository;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Amasty\Feed\Model\Category\Repository $repository,
        \Amasty\Feed\Model\RegistryContainer $registryContainer,
        array $data = []
    ) {
        parent::__construct($context, $registry, $formFactory, $registryContainer, $data);
        $this->feldsetId = 'base_fieldset';
        $this->repository = $repository;
    }

    /**
     * {@inheritdoc}
     */
    public function getTabLabel()
    {
        return __('Step 2: Exclude Categories');
    }

    /**
     * {@inheritdoc}
     */
    public function getTabTitle()
    {
        return __('Step 2: Exclude Categories');
    }

    /**
     * {@inheritdoc}
     */
    protected function prepareNotEmptyForm()
    {
        list($categoryMappingId, $feedId) = $this->getFeedStateConfiguration();

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix(CategoryGeneral::HTML_ID_PREFIX);

        $fieldset = $form->addFieldset($this->feldsetId, ['legend' => $this->getLegend()]);

        if ($categoryMappingId) {
            try {
                $category = $this->repository->getById($categoryMappingId);
            } catch (LocalizedException $e) {
                $category = $this->repository->getCategoryEmptyEntity()->setData('is_active', 1);
            }

            $fieldset->addField(
                'feed_category_id',
                'hidden',
                [
                    'name' => 'feed_category_id',
                    'value' => $categoryMappingId
                ]
            );
        } else {
            $category = $this->repository->getCategoryEmptyEntity()
                ->setData('is_active', 1);
        }

        if ($feedId) {
            $fieldset->addField(
                'feed_id',
                'hidden',
                [
                    'name'  => 'feed_id',
                    'value' => $feedId,
                ]
            );
        }

        $fieldset->addField(
            'mapping_note',
            'note',
            [
                'name' => 'mapping_note',
                'text' => __(Notes::$excludeNote)
            ]
        );

        $fieldset->addField(
            'mapping',
            'note',
            ['name' => 'mapping']
        );

        $className = TabMapping::class;
        $form->getElement(
            'mapping'
        )->setRenderer(
            $this->getLayout()->createBlock($className)
        );
        $form->addValues($category->getData());
        $this->setForm($form);

        return $this;
    }
}
