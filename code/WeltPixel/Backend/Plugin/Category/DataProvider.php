<?php
namespace WeltPixel\Backend\Plugin\Category;

use Magento\Eav\Model\Config;

class DataProvider
{
    /**
     * @var Config
     */
    private $eavConfig;

    public function __construct(
        Config $eavConfig
    )
    {
        $this->eavConfig = $eavConfig;
    }

    /**
     * @param \Magento\Catalog\Model\Category\DataProvider $subject
     * @param array $result
     * @return array
     */
    public function afterPrepareMeta(\Magento\Catalog\Model\Category\DataProvider $subject, $result)
    {
        $meta = array_merge_recursive($result, $this->_prepareFieldsMeta(
            $this->_getFieldsMap(),
            $subject->getAttributesMeta($this->eavConfig->getEntityType('catalog_category'))
        ));

        return $meta;
    }

    /**
     * Prepare fields meta based on xml declaration of form and fields metadata
     *
     * @param array $fieldsMap
     * @param array $fieldsMeta
     * @return array
     */
    protected function _prepareFieldsMeta($fieldsMap, $fieldsMeta)
    {
        $result = [];
        foreach ($fieldsMap as $fieldSet => $fields) {
            foreach ($fields as $field) {
                if (isset($fieldsMeta[$field])) {
                    $result[$fieldSet]['children'][$field]['arguments']['data']['config'] = $fieldsMeta[$field];
                }
            }
        }
        return $result;
    }

    /**
     * Rewrite this in all subclassess, provide the list with category attributes
     * @return array
     */
    protected function _getFieldsMap() {
        return [];
    }


}
