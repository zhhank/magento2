<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Adminhtml
 * @copyright   Copyright (c) 2012 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Adminhtml catalog super product configurable tab
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_Adminhtml_Block_Catalog_Product_Edit_Tab_Super_Config extends Mage_Adminhtml_Block_Widget
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{

    protected $_template = 'catalog/product/edit/super/config.phtml';

    /**
     * Initialize block
     *
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setProductId($this->getRequest()->getParam('id'));

        $this->setId('config_super_product');
        $this->setCanEditPrice(true);
        $this->setCanReadPrice(true);
    }

    /**
     * Retrieve Tab class (for loading)
     *
     * @return string
     */
    public function getTabClass()
    {
        return 'ajax';
    }

    /**
     * Check block is readonly
     *
     * @return boolean
     */
    public function isReadonly()
    {
        return (bool) $this->_getProduct()->getCompositeReadonly();
    }

    /**
     * Check whether attributes of configurable products can be editable
     *
     * @return boolean
     */
    public function isAttributesConfigurationReadonly()
    {
        return (bool) $this->_getProduct()->getAttributesConfigurationReadonly();
    }

    /**
     * Get configurable product type
     *
     * @return Mage_Catalog_Model_Product_Type_Configurable
     */
    protected function _getProductType()
    {
        return Mage::getModel('Mage_Catalog_Model_Product_Type_Configurable');
    }

    /**
     * Check whether prices of configurable products can be editable
     *
     * @return boolean
     */
    public function isAttributesPricesReadonly()
    {
        return $this->_getProduct()->getAttributesConfigurationReadonly() ||
            (Mage::helper('Mage_Catalog_Helper_Data')->isPriceGlobal() && $this->isReadonly());
    }

    /**
     * Prepare Layout data
     *
     * @return Mage_Adminhtml_Block_Catalog_Product_Edit_Tab_Super_Config
     */
    protected function _prepareLayout()
    {
        $this->addChild('create_empty', 'Mage_Adminhtml_Block_Widget_Button', array(
            'label' => Mage::helper('Mage_Catalog_Helper_Data')->__('Create Empty'),
            'class' => 'add',
            'onclick' => 'superProduct.createEmptyProduct()'
        ));
        $this->addChild('super_settings', 'Mage_Adminhtml_Block_Catalog_Product_Edit_Tab_Super_Settings');

        if ($this->_getProduct()->getId()) {
            $this->setChild('simple',
                $this->getLayout()->createBlock('Mage_Adminhtml_Block_Catalog_Product_Edit_Tab_Super_Config_Simple',
                    'catalog.product.edit.tab.super.config.simple')
            );

            $this->addChild('create_from_configurable', 'Mage_Adminhtml_Block_Widget_Button', array(
                'label' => Mage::helper('Mage_Catalog_Helper_Data')->__('Copy From Configurable'),
                'class' => 'add',
                'onclick' => 'superProduct.createNewProduct()'
            ));
        }

        return parent::_prepareLayout();
    }

    /**
     * Retrieve currently edited product object
     *
     * @return Mage_Catalog_Model_Product
     */
    protected function _getProduct()
    {
        return Mage::registry('current_product');
    }

    /**
     * Retrieve attributes data
     *
     * @return array
     */
    public function getAttributes()
    {
        $attributes = (array)$this->_getProductType()->getConfigurableAttributesAsArray($this->_getProduct());
        foreach ($attributes as &$attribute) {
            if (isset($attribute['values']) && is_array($attribute['values'])) {
                foreach ($attribute['values'] as &$attributeValue) {
                    if (!$this->getCanReadPrice()) {
                        $attributeValue['pricing_value'] = '';
                        $attributeValue['is_percent'] = 0;
                    }
                    $attributeValue['can_edit_price'] = $this->getCanEditPrice();
                    $attributeValue['can_read_price'] = $this->getCanReadPrice();
                }
            }
        }
        return $attributes;
    }

    /**
     * Retrieve Links in JSON format
     *
     * @return string
     */
    public function getLinksJson()
    {
        $products = $this->_getProductType()
            ->getUsedProducts($this->_getProduct());
        if(!$products) {
            return '{}';
        }
        $data = array();
        foreach ($products as $product) {
            $data[$product->getId()] = $this->getConfigurableSettings($product);
        }
        return Mage::helper('Mage_Core_Helper_Data')->jsonEncode($data);
    }

    /**
     * Retrieve configurable settings
     *
     * @param Mage_Catalog_Model_Product $product
     * @return array
     */
    public function getConfigurableSettings($product) {
        $data = array();
        $attributes = $this->_getProductType()
            ->getUsedProductAttributes($this->_getProduct());
        foreach ($attributes as $attribute) {
            $data[] = array(
                'attribute_id' => $attribute->getId(),
                'label'        => $product->getAttributeText($attribute->getAttributeCode()),
                'value_index'  => $product->getData($attribute->getAttributeCode())
            );
        }

        return $data;
    }

    /**
     * Retrieve Grid child HTML
     *
     * @return string
     */
    public function getGridHtml()
    {
        return $this->getChildHtml('grid');
    }

    /**
     * Retrieve Grid JavaScript object name
     *
     * @return string
     */
    public function getGridJsObject()
    {
        return $this->getChildBlock('grid')->getJsObjectName();
    }

    /**
     * Retrieve Create New Empty Product URL
     *
     * @return string
     */
    public function getNewEmptyProductUrl()
    {
        return $this->getUrl(
            '*/*/new',
            array(
                'set'      => $this->_getProduct()->getAttributeSetId(),
                'type'     => Mage_Catalog_Model_Product_Type::TYPE_SIMPLE,
                'required' => $this->_getRequiredAttributesIds(),
                'popup'    => 1
            )
        );
    }

    /**
     * Retrieve Create New Product URL
     *
     * @return string
     */
    public function getNewProductUrl()
    {
        return $this->getUrl(
            '*/*/new',
            array(
                'set'      => $this->_getProduct()->getAttributeSetId(),
                'type'     => Mage_Catalog_Model_Product_Type::TYPE_SIMPLE,
                'required' => $this->_getRequiredAttributesIds(),
                'popup'    => 1,
                'product'  => $this->_getProduct()->getId()
            )
        );
    }

    /**
     * Retrieve Quick create product URL
     *
     * @return string
     */
    public function getQuickCreationUrl()
    {
        return $this->getUrl(
            '*/*/quickCreate',
            array(
                'product'  => $this->_getProduct()->getId()
            )
        );
    }

    /**
     * Retrieve Required attributes Ids (comma separated)
     *
     * @return string
     */
    protected function _getRequiredAttributesIds()
    {
        $attributesIds = array();
        $configurableAttributes = $this->_getProduct()
            ->getTypeInstance()->getConfigurableAttributes($this->_getProduct());
        foreach ($configurableAttributes as $attribute) {
            $attributesIds[] = $attribute->getProductAttribute()->getId();
        }

        return implode(',', $attributesIds);
    }

    /**
     * Retrieve Tab label
     *
     * @return string
     */
    public function getTabLabel()
    {
        return Mage::helper('Mage_Catalog_Helper_Data')->__('Associated Products');
    }

    /**
     * Retrieve Tab title
     *
     * @return string
     */
    public function getTabTitle()
    {
        return Mage::helper('Mage_Catalog_Helper_Data')->__('Associated Products');
    }

    /**
     * Can show tab flag
     *
     * @return bool
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Check is a hidden tab
     *
     * @return bool
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Show "Use default price" checkbox
     *
     * @return bool
     */
    public function getShowUseDefaultPrice()
    {
        return !Mage::helper('Mage_Catalog_Helper_Data')->isPriceGlobal()
            && $this->_getProduct()->getStoreId();
    }

    /**
     * Get list of used attributes
     *
     * @return array
     */
    public function getSelectedAttributes()
    {
        return $this->_getProduct()->isConfigurable()
            ? array_filter($this->_getProductType()->getUsedProductAttributes($this->_getProduct()))
            : array();
    }

    /**
     * Retrieve all possible attribute values combinations
     *
     * @return array
     */
    public function getVariations()
    {
        $attributesCount = 0;
        $variationalAttributes = array();
        $usedProductAttributes = $this->getSelectedAttributes();
        foreach ($usedProductAttributes as $attribute) {
            /** @var $attribute Mage_Catalog_Model_Resource_Eav_Attribute */
            $variationalAttributes[] = array(
                'id' => $attribute->getId(),
                'values' => $attribute->getSource()->getAllOptions(false),
            );
            $attributesCount++;
        }

        $variations = array();
        $currentVariation = array_fill(0, $attributesCount, 0);
        $variationalAttributes = array_reverse($variationalAttributes);
        $lastAttribute = $attributesCount - 1;
        do {
            for ($attributeIndex = 0; $attributeIndex < $attributesCount - 1; ++$attributeIndex) {
                if ($currentVariation[$attributeIndex] >= count($variationalAttributes[$attributeIndex]['values'])) {
                    $currentVariation[$attributeIndex] = 0;
                    ++$currentVariation[$attributeIndex + 1];
                }
            }
            if ($currentVariation[$lastAttribute] >= count($variationalAttributes[$lastAttribute]['values'])) {
                break;
            }

            $filledVariation = array();
            for ($attributeIndex = $attributesCount; $attributeIndex--;) {
                $currentAttribute = $variationalAttributes[$attributeIndex];
                $filledVariation[$currentAttribute['id']] =
                    $currentAttribute['values'][$currentVariation[$attributeIndex]];
            }

            $variations[] = $filledVariation;
            $currentVariation[0]++;
        } while (1);
        return $variations;
    }
}
