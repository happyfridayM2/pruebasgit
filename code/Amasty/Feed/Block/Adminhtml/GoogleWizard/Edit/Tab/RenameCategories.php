<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Block\Adminhtml\GoogleWizard\Edit\Tab;

use Amasty\Feed\Block\Adminhtml\Category\Edit\Tab\RenameMapping as TabMapping;
use \Amasty\Feed\Block\Adminhtml\Category\Edit\Tab\General as CategoryGeneral;
use Amasty\Feed\Model\Category\Notes;
use Amasty\Feed\Model\Category\Repository;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class RenameCategories
 *
 * @package Amasty\Feed
 */
class RenameCategories extends TabGeneric
{
    /**
     * @var \Amasty\Feed\Ui\Component\Form\GoogleTaxonomyOptions
     */
    private $googleTaxonomyOptions;

    /**
     * @var Repository
     */
    private $repository;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Amasty\Feed\Model\Category\Repository $repository,
        \Amasty\Feed\Model\RegistryContainer $registryContainer,
        \Amasty\Feed\Ui\Component\Form\GoogleTaxonomyOptions $googleTaxonomyOptions,
        array $data = []
    ) {
        parent::__construct($context, $registry, $formFactory, $registryContainer, $data);
        $this->feldsetId = 'base_fieldset';
        $this->googleTaxonomyOptions = $googleTaxonomyOptions;
        $this->repository = $repository;
    }

    /**
     * {@inheritdoc}
     */
    public function getTabLabel()
    {
        return __('Step 3: Rename Categories');
    }

    /**
     * {@inheritdoc}
     */
    public function getTabTitle()
    {
        return __('Step 3: Rename Categories');
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
            $category = $this->repository->getCategoryEmptyEntity()->setData('is_active', 1);
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
            'use_taxonomy',
            'hidden',
            [
                'name'  => 'use_taxonomy',
                'value' => 1,
            ]
        );

        $fieldset->addField(
            'mapping_note',
            'note',
            [
                'name' => 'mapping_note',
                'text' => __(Notes::$mappingNote, Notes::$href)
            ]
        );

        $fieldset->addField(
            'taxonomy_source',
            'select',
            [
                'name' => 'taxonomy_source',
                'label' => __('Google Taxonomy source:'),
                'title' => __('Google Taxonomy source:'),
                'values' => $this->googleTaxonomyOptions->toOptionArray(),
                'value' => 'en-US'
            ]
        );

        $fieldset->addField(
            'mapping',
            'note',
            ['name' => 'mapping']
        );

        $form->getElement('mapping')
            ->setRenderer($this->getLayout()->createBlock(TabMapping::class));
        $form->addValues($category->getData());
        $this->setForm($form);

        return $this;
    }
}
