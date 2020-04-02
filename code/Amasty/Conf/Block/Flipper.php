<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Conf
 */


namespace Amasty\Conf\Block;

use Amasty\Conf\Helper\Data;
use Magento\Framework\View\Element\Template;
use Magento\Eav\Model\Entity\Collection\AbstractCollection;

class Flipper extends \Magento\Framework\View\Element\Template
{
    /**
     * @var AbstractCollection
     */
    protected $collection;

    /**
     * Path to template file in theme.
     *
     * @var string
     */
    protected $_template = 'Amasty_Conf::product/list/flipper.phtml';

    /**
     * @var \Magento\Catalog\Helper\ImageFactory
     */
    private $helperFactory;

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    private $jsonEncoder;

    /**
     * Flipper constructor.
     * @param Template\Context $context
     * @param \Magento\Catalog\Helper\ImageFactory $helperFactory
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        \Magento\Catalog\Helper\ImageFactory $helperFactory,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->helperFactory = $helperFactory;
        $this->jsonEncoder = $jsonEncoder;
    }

    public function applyFlipperToCollection(AbstractCollection $collection, $listBlock)
    {
        $this->setCollection($collection);
        $this->setListBlock($listBlock);

        return $this->toHtml();
    }

    public function getImageConfiguration()
    {
        $collection = $this->getCollection();
        $listBlock = $this->getListBlock();
        $flipperImageId = Data::FLIPPER_IMAGE_ID;
        $data = [];

        /* method from templates/product/list.phtml */
        $image = ($listBlock->getMode() == 'grid') ? 'category_page_grid' : 'category_page_list';

        foreach ($collection as $product) {
            if ($product->getData($flipperImageId)) {
                $productImage = $listBlock->getImage($product, $image);

                /** @var \Magento\Catalog\Helper\Image $helper */
                $helper = $this->helperFactory->create()
                    ->init($product, $image, ['type' => $flipperImageId]);

                $flipperImage = $helper->getUrl();
                if ($flipperImage && strpos($flipperImage, 'placeholder') === false) {
                    $data[] = [
                        'product_id' => $product->getId(),
                        'img_src'    => $productImage->getImageUrl(),
                        'flipper'     => $flipperImage
                    ];
                }
            }
        }

        return $data;
    }

    /**
     * @param AbstractCollection $collection
     */
    public function setCollection(AbstractCollection $collection)
    {
        $this->collection = $collection;
    }

    /**
     * @return AbstractCollection
     */
    public function getCollection()
    {
        return $this->collection;
    }

    /**
     * @param array $data
     * @return string
     */
    public function encodeConfig(array $data)
    {
       return $this->jsonEncoder->encode($data);
    }
}