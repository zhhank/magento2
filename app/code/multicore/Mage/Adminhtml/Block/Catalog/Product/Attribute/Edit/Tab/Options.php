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
 * Product attribute add/edit form options tab
 *
 * @method Mage_Adminhtml_Block_Catalog_Product_Attribute_Edit_Tab_Options setReadOnly(bool $value)
 * @method null|bool getReadOnly
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_Adminhtml_Block_Catalog_Product_Attribute_Edit_Tab_Options
    extends Mage_Eav_Block_Adminhtml_Attribute_Edit_Options_Abstract
{
    /**
     * Retrieve option values collection
     * It is represented by an array in case of system attribute
     *
     * @param Mage_Eav_Model_Entity_Attribute_Abstract $attribute
     * @return array|Mage_Eav_Model_Resource_Entity_Attribute_Option_Collection
     */
    protected function _getOptionValuesCollection(Mage_Eav_Model_Entity_Attribute_Abstract $attribute)
    {
        if ($this->canManageOptionDefaultOnly()) {
            $options = Mage::getModel($attribute->getSourceModel())
                ->setAttribute($attribute)
                ->getAllOptions(true);
            return array_reverse($options);
        } else {
            return parent::_getOptionValuesCollection($attribute);
        }
    }
}
