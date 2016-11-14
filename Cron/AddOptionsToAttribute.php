<?php
/**
 * @author	Nertila Lika
 * @package     W3D ExportShopify
 * @copyright   Copyright (c) 2015 W3D Team (http://w3development.net) 
 */
namespace W3D\ExportShopify\Cron;

class AddOptionsToAttribute
{

    protected $_eavSetupFactory;
    protected $_storeManager;
    protected $_attributeFactory;
    protected $_logger;
    protected $eavConfig;
 
    public function __construct(\Psr\Log\LoggerInterface $logger,      
        \Magento\Eav\Setup\EavSetupFactory $eavSetupFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\ResourceModel\Eav\Attribute $attributeFactory,
        \Magento\Eav\Model\Config $eavConfig
    ) {
        $this->_eavSetupFactory = $eavSetupFactory;
        $this->_storeManager = $storeManager;
        $this->_attributeFactory = $attributeFactory;
        $this->_logger = $logger;
        $this->eavConfig = $eavConfig;
    }

    public function addOptions($attributeCode, $attribute_arr){
        $this->_logger->info('options number'. sizeof($attribute_arr));
        if(sizeof($attribute_arr) > 0){
        //$attribute_arr = ['#ff0000','#00ff00','#0000ff'];
        $attributeInfo = $this->_attributeFactory->getCollection()
               ->addFieldToFilter('attribute_code',['eq'=>$attributeCode])
               ->getFirstItem();

        $attribute_id = $attributeInfo->getAttributeId();

        $attribute = $this->eavConfig->getAttribute('catalog_product', $attributeCode);
        $options = $attribute->getOptions();
        $this->_logger->info('options '. print_r($options));
        
        $option=array();
        $option['attribute_id'] = $attributeInfo->getAttributeId();

        $allStores = $this->_storeManager->getStores();
        $this->_logger->info('1');
        foreach($attribute_arr as $key=>$value){
            $this->_logger->info('2');
            $found = false;

            foreach ($options as $opt) {
                $this->_logger->info('options '. $opt);
                if($opt == $value){
                    $this->_logger->info('value found');
                    $found = true;
                    break;
                }
            }
            $this->_logger->info('3');
            if(!$found){
                $this->_logger->info('4');
                $option['value'][$value][0]=$value;
                foreach($allStores as $store){
                    $option['value'][$value][$store->getId()] = $value;
                }
            }
        }
        
        $this->_logger->info('5');
        $eavS = $this->_eavSetupFactory->create();
        
        $eavS->addAttributeOption($option);
        $this->_logger->info('6');
        }
        return 0;
    }

}