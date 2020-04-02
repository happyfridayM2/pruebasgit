<?php
namespace Oct8ne\Oct8ne\Helper;

use \Magento\Framework\App\Helper\AbstractHelper;
use \Magento\Framework\App\Helper\Context;
use \Magento\Catalog\Api\ProductRepositoryInterfaceFactory;
use \Magento\Framework\View\Asset\Repository;
use \Magento\Tax\Api\TaxCalculationInterface;
use \Magento\Framework\Pricing\PriceCurrencyInterface as CurrencyHelper;
use \Magento\Catalog\Block\Product\ListProduct;
use \Magento\Framework\Data\Form\FormKey;
use \Magento\Catalog\Model\Product\Option;
use \Magento\Store\Model\StoreManagerInterface;


class ProductSummaryHelper extends AbstractHelper
{

    protected $_productRepository; //`para leer un producto de la base de datos
    protected $_context; //contexto
    protected $_assetRepo; //para obtener la url de la imagen placeholder de mi modulo
    protected $_taxCalcualtion; //para calcular impuestos
    protected $_currencyHelper; //para formatear y convertir precios
    protected $_listProduct;
    protected $_formKey;
    protected $_option;
    protected $_storeManager;


    public function __construct(Context $context, StoreManagerInterface $storeManager, ProductRepositoryInterfaceFactory $productRepository, Repository $assetRepo, TaxCalculationInterface $taxCalculation, CurrencyHelper $currencyHelper, ListProduct $listproduct, FormKey $formKey, Option $option)
    {
        parent::__construct($context);

        $this->_productRepository = $productRepository;
        $this->_context = $context;
        $this->_assetRepo = $assetRepo;
        $this->_taxCalcualtion = $taxCalculation;
        $this->_currencyHelper = $currencyHelper;
        $this->_listProduct = $listproduct;
        $this->_formKey = $formKey;
        $this->_option = $option;
        $this->_storeManager = $storeManager;
    }

    /**
     * Obtiene informacion básica sobre el producto
     * @param $id
     * @param $oct8ne_context
     * @param $extended para especificar si es productInfo o no
     * @return array
     */
    public function getProductSummary($id, $oct8ne_context, $extended = false)
    {
        try {

            $result = array();
            $medias = array();

            $img_folder = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'catalog/product';

            //OBTIENE EL PRODUCTO REQUERIDO
            $product = $this->_productRepository->create()->getById($id, false, $oct8ne_context["context_shop"]);

            $rate = $this->getRateValue($product);

            //OBTIENE LOS PRECIOS DEL PRODUCTO CON Y SIN TASAS
            $prices = $this->getPricesWWTaxes($product, $rate);


            //CONVIERTE A LA MONEDA REQUERIDA
            $prices = $this->formatPrices($prices, $oct8ne_context);

            $result["internalId"] = $id;
            $result["title"] = $product->getName();
            $result["formattedPrice"] = $this->_currencyHelper->format($prices["final_incl"], false, 2, null, !empty($oct8ne_context["context_currency"]) ? $oct8ne_context["context_currency"] : $oct8ne_context["current_currency"]);
            $result["formattedPrevPrice"] = $this->_currencyHelper->format($prices["incl"], false, 2, null, !empty($oct8ne_context["context_currency"]) ? $oct8ne_context["context_currency"] : $oct8ne_context["current_currency"]);;
            $result["productUrl"] = $this->cleanProtocol($product->getUrlModel()->getUrl($product));
            $result["thumbnail"] = !empty($product->getImage()) ? $this->cleanProtocol($img_folder . $product->getImage()) : $this->cleanProtocol($this->_assetRepo->getUrl("Oct8ne_Oct8ne::images/demo.jpg"));

            //se requiere mas informacion (Product summary)
            if ($extended) {

                $result["description"] = $product->getDescription();
                //url añadir producto al carro
                $aux = $this->cleanProtocol($this->_listProduct->getAddToCartUrl($product) . "?form_key=" . $this->_formKey->getFormKey() . "&qty=1");
                //$aux = str_replace("https","http", $aux);
                //$result["addToCartUrl"] = str_replace("http","https", $aux);

                $result["addToCartUrl"] = $aux;

                //&& !empty($product->getOptions()) ? "true" : "false"
                $result["useProductUrl"] = $product->getTypeId() == 'configurable' ? "true" : "false";

                //obtiene los medias de un producto (imagenes y videos)
                $medias_aux = $product->getMediaGalleryEntries();

                foreach ($medias_aux as $media) {

                    if ($media->getMediaType() == "image") {

                        $medias[] = array("url" => $this->cleanProtocol($img_folder . $media->getFile()));
                    }
                }
                $result["medias"] = $medias;

            }

            return $result;

        } catch (\Magento\Framework\Exception\NoSuchEntityException $exception) {

            return $result;
        }

    }

//https://www.magento.local/magento/checkout/cart/add/uenc/aHR0cDovL3d3dy5tYWdlbnRvLmxvY2FsL21hZ2VudG8vb2N0OG5lL2ZyYW1lL3Byb2R1Y3RpbmZvP3Byb2R1Y3RJZHM9MSwyJmN1cnJlbmN5PUVVUiZsb2NhbGU9ZXMtRVM,/product/1/?product=1&form_key=GSVrRCl14hrikUag&qty=1


    /**
     * Obtiene si hay impuestos o no
     * @param $product
     * @return float|int
     */
    public function getRateValue($product)
    {

        //tasa de cambio a 0 por defecto
        $rate = 0;

        //obtiene que tax class se aplica al producto
        $taxAttribute = $product->getCustomAttribute('tax_class_id');

        if ($taxAttribute) {

            //obtengo la id del rate
            $productRateId = $taxAttribute->getValue();

            if ($productRateId) {

                //obtengo el valor que se aplica
                $rate = $this->_taxCalcualtion->getCalculatedRate($productRateId);
            }
        }

        return $rate;
    }


    /**
     * Obtiene los precios con y sin descuento
     * @param $product Producto
     * @param $rate Ratio de taxas
     * @return array
     */
    public function getPricesWWTaxes($product, $rate)
    {

        $prodyctType = $product->getTypeId();


        switch ($prodyctType){

            case "bundle":
                $regular_price = $product->getPriceInfo()->getPrice('regular_price')->getAmount()->getValue();
                $final_price = $product->getPriceInfo()->getPrice('final_price')->getValue();
            break;

            case "grouped":
                $final_price = $product->getPriceInfo()->getPrice('final_price')->getValue();
                $regular_price = $final_price;
                break;

            case "configurable":
                $final_price = $product->getPriceInfo()->getPrice('final_price')->getValue();
                $regular_price = $final_price;
                break;

            default:
                $regular_price = $product->getPrice();
                $final_price = $product->getFinalPrice(1);
                break;
        }


        if ((int)$this->scopeConfig->getValue('tax/calculation/price_includes_tax', \Magento\Store\Model\ScopeInterface::SCOPE_STORE) === 1) {
            // Product price in catalog is including tax.
            $priceExcludingTax = $regular_price / (1 + ($rate / 100));
            $finalPriceExcludingTax = $final_price / (1 + ($rate / 100));

        } else {
            // Product price in catalog is excluding tax.
            $priceExcludingTax = $regular_price;
            $finalPriceExcludingTax = $final_price;

        }

        if ((int)$this->scopeConfig->getValue('tax/calculation/price_includes_tax', \Magento\Store\Model\ScopeInterface::SCOPE_STORE) === 1) {

            $priceIncludingTax = $priceExcludingTax + ($priceExcludingTax * ($rate / 100));
            $finalPriceIncludingTax = $finalPriceExcludingTax + ($finalPriceExcludingTax * ($rate / 100));
        }else{

            $priceIncludingTax = $priceExcludingTax;
            $finalPriceIncludingTax = $finalPriceExcludingTax;
        }

        return [
            'incl' => $priceIncludingTax,
            'excl' => $priceExcludingTax,
            'final_incl' => $finalPriceIncludingTax,
            'final_excl' => $finalPriceExcludingTax
        ];
    }

    /**
     * Convierte los precios a la moneda requerida
     * @param $prices
     * @param $oct8ne_context
     */
    public function formatPrices($prices, $oct8ne_context)
    {

        if (!empty($oct8ne_context["context_currency"] && $oct8ne_context["context_currency"] != $oct8ne_context["current_currency"])) {

            foreach ($prices as $key => $value) {

                $prices[$key] = $this->_currencyHelper->convertAndRound($value, NULL, $oct8ne_context["context_currency"], 2);

            }
        }

        return $prices;
    }


    /**
     * Quita el protocolo
     * @param $string
     * @return mixed
     */
    public function cleanProtocol($string)
    {

        $aux = str_replace("https:", "", $string);
        $aux = str_replace("http:", "", $aux);
        return $aux;


    }


}