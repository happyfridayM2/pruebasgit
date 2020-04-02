<?php
namespace Magebees\Products\Model\Catalog\ProductLink;
use Magento\Catalog\Model\ProductLink\Converter\ConverterPool;
use Magento\Framework\Exception\NoSuchEntityException;

class CollectionProvider extends \Magento\Catalog\Model\ProductLink\CollectionProvider
{
    /*
    protected $providers;
    protected $converterPool;
    
    public function __construct(ConverterPool $converterPool, array $providers = [])
    {
        $this->converterPool = $converterPool;
        $this->providers = $providers;
    }
    */
	public function getCollection(\Magento\Catalog\Model\Product $product, $type)
    {
        if (!isset($this->providers[$type])) {
            throw new NoSuchEntityException(__('Collection provider is not registered'));
        }
        $products = $this->providers[$type]->getLinkedProducts($product);
        $converter = $this->converterPool->getConverter($type);
        $output = [];
        $sorterItems = [];
        foreach ($products as $item) {
            $output[$item->getId()] = $converter->convert($item);
        }
        //code for fixed the less Related, Cross Sell and Upsell product display issue in backend.
        $newPosition = 0;
        $i = 0;
        foreach ($output as $item) {
            if(isset($item['position'])){
                $itemPosition = $item['position'];
                if (!isset($sorterItems[$itemPosition])) {
                    $sorterItems[$itemPosition] = $item;
                } else {
                    $newPosition = $itemPosition + 1;
                    $sorterItems[$newPosition] = $item;
                }
            }else{
                $sorterItems[$i++] = $item;
            }
        }
        ksort($sorterItems);
        return $sorterItems;
    }	
}