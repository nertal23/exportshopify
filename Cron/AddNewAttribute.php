<?php
/**
 * @author		Sashas
 * @category    Sashas
 * @package     Sashas_CustomerAttribute
 * @copyright   Copyright (c) 2015 Sashas IT Support Inc. (http://www.extensions.sashas.org) 
 */
namespace W3D\ExportShopify\Cron;
use Magento\Eav\Setup\EavSetup;
 
/**
 * @codeCoverageIgnore
 */
class AddNewAttribute
{
    
    /**
     * @var CustomerSetupFactory
     */
    protected $customerSetupFactory;
    
    /**
     * @var AttributeSetFactory
     */
    private $attributeSetFactory;
    protected $_attributeFactory;
    protected $_logger;
    protected $eavSetup;
    
    /**
     * @param CustomerSetupFactory $customerSetupFactory
     * @param AttributeSetFactory $attributeSetFactory
     */
    public function __construct(\Psr\Log\LoggerInterface $logger,
        \Magento\Eav\Setup\EavSetup $eavSetup,
        \Magento\Catalog\Model\ResourceModel\Eav\Attribute $attributeFactory) {
        
        $this->_attributeFactory = $attributeFactory;
        $this->_logger = $logger;
        $this->eavSetup = $eavSetup;
    }
 
    
    /**
     * {@inheritdoc}
     */
    public function addAttributes($attributeCode, $attributeLabel)
    {
        $attributeGroup = 'Default'; 

        $attributeInfo = $this->_attributeFactory->getCollection()
               ->addFieldToFilter('attribute_code',['eq'=>$attributeCode])
               ->getFirstItem();

        $attribute_id = $attributeInfo->getAttributeId();

        if($attribute_id === null){
            $entityTypeId = $this->eavSetup->getEntityTypeId(\Magento\Catalog\Api\Data\ProductAttributeInterface::ENTITY_TYPE_CODE);

            $attributeSetId = $this->eavSetup->getDefaultAttributeSetId($entityTypeId);

            $this->eavSetup->addAttribute($entityTypeId, $attributeCode, [
                'type' => 'varchar',
                'label' => $attributeLabel,
                'input' => 'text',
                'required' => false,
                'visible' => true,
                'user_defined' => true,
                'sort_order' => 1000,
                'position' => 1000,
                'system' => 0,
            ]);
            $this->eavSetup->addAttributeToSet($entityTypeId, $attributeSetId, 'Default', $attributeCode);
        }
        $this->_logger->info('create setup');
      
    }
}
