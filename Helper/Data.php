<?php
/**
 * @author W3D Team
 * @copyright Copyright (c) 2016 W3D
 * @package W3D_ExportOrder
 */
namespace W3D\ExportShopify\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterfac
     */
    protected $_scopeConfig;

    CONST API_KEY      = 'w3d_exportshopify/general/api_key';
    CONST SECRET = 'w3d_exportshopify/general/secret';
    CONST SHOP_URL  = 'w3d_exportshopify/general/shop_url';

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        parent::__construct($context);

        $this->_scopeConfig = $scopeConfig;
    }

    public function getApiKey(){
        return $this->_scopeConfig->getValue(self::API_KEY);
    }

    public function getSecret(){
        return $this->_scopeConfig->getValue(self::SECRET);
    }

    public function getShopUrl(){
        return $this->_scopeConfig->getValue(self::SHOP_URL);
    }
}

