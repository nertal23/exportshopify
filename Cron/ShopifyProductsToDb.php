<?php
namespace W3D\ExportShopify\Cron;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Catalog\Setup\CategorySetup;
use Magento\ConfigurableProduct\Helper\Product\Options\Factory;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Eav\Api\Data\AttributeOptionInterface;
use Magento\TestFramework\Helper\Bootstrap;
use W3D\ExportShopify\Shopify\Client;
use W3D\ExportShopify\Shopify\Cron;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class ShopifyProductsToDb{

	protected $_logger;
	protected $_helper;
	protected $_objectManager;
	protected $_attributeFactory;
	protected $_productRepository;
	protected $addAttribute;
    protected $setup;
    protected $context;
    protected $_addOptions;

	public function __construct(\Psr\Log\LoggerInterface $logger,
		\W3D\ExportShopify\Helper\Data $helper,
		\Magento\Catalog\Model\ResourceModel\Eav\Attribute $attributeFactory,
		\W3D\ExportShopify\Cron\AddNewAttribute $addAttribute,
        \W3D\ExportShopify\Cron\AddOptionsToAttribute $addOptions,
        \Magento\Framework\Setup\ModuleDataSetupInterface $setup) {
        $this->_logger = $logger;
        $this->_helper = $helper;
        $this->_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->_attributeFactory = $attributeFactory;
        $this->addAttribute = $addAttribute;
        $this->setup = $setup;
        $this->_addOptions = $addOptions;
        
    }

	public function storeToDb(){

		$apiKey = $this->_helper->getApiKey();
		$secret = $this->_helper->getSecret();
		$shopUrl = $this->_helper->getShopUrl();

		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();

		$productRepository = $objectManager->create(ProductRepositoryInterface::class);

		$attributeSetId = 4;
		if($apiKey != '' && $secret != '' && $shopUrl != ''){
			$baseUrl = 'https://'.$apiKey.':'.$secret.'@'.$shopUrl;
		}else{
			$baseUrl = 'https://21173374b46885f165429cb72b2e223c:d86a74cced6c0009d8b6cfea0e1146fa@onlinesnailstore.myshopify.com/admin/';
		}

        $response = json_decode(file_get_contents($baseUrl.'products/count.json')); 

		$productCount = $response->count; 

		$pages = ceil($productCount/250);

		$current_page = 1;
		while ($current_page <= $pages) {
			$response = json_decode(file_get_contents($baseUrl.'products.json?limit=250&page='.$current_page)); 
			foreach ($response->products as $product) {
				$variantsNr = sizeof($product->variants);
				if($variantsNr < 2){
					foreach ($product->variants as $variant) {
						$productSimple = $objectManager->create(Product::class);
						$productSimple->setTypeId(Type::TYPE_SIMPLE)
	        			->setAttributeSetId($attributeSetId)
        				->setWebsiteIds([1])
        				->setName($product->title)
        				->setSku($product->id)
        				->setPrice($variant->price)
        				->setVisibility(Visibility::VISIBILITY_BOTH)
        				->setStatus(Status::STATUS_ENABLED)
        				->setStockData(['use_config_manage_stock' => 1, 'qty' => $variant->inventory_quantity, 'is_qty_decimal' => 0, 'is_in_stock' => 1]);
    					$productSimple = $productRepository->save($productSimple);
					}
				}
			}
			$current_page++;
		}
	}

	private function seoUrl($string) {
        //Lower case everything
        $string = strtolower($string);
        //Make alphanumeric (removes all other characters)
        $string = preg_replace("/[^a-z0-9_\s-]/", "", $string);
        //Clean up multiple dashes or whitespaces
        $string = preg_replace("/[\s-]+/", " ", $string);
        //Convert whitespaces and underscore to dash
        $string = preg_replace("/[\s_]/", "-", $string);
        return $string;
    }
}