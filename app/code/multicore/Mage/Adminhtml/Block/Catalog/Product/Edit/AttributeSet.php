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
 * Create product attribute set selector
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_Adminhtml_Block_Catalog_Product_Edit_AttributeSet extends Mage_Backend_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();

        $fieldset = $form->addFieldset('settings', array(
            'legend' => Mage::helper('Mage_Catalog_Helper_Data')->__('Product Settings')
        ));

        $entityType = Mage::registry('product')->getResource()->getEntityType();

        $fieldset->addField('attribute_set_id', 'select', array(
            'label' => Mage::helper('Mage_Catalog_Helper_Data')->__('Attribute Set'),
            'title' => Mage::helper('Mage_Catalog_Helper_Data')->__('Attribute Set'),
            'name'  => 'set',
            'value' => Mage::registry('product')->getAttributeSetId(),
            'values'=> Mage::getResourceModel('Mage_Eav_Model_Resource_Entity_Attribute_Set_Collection')
                ->setEntityTypeFilter($entityType->getId())
                ->load()
                ->toOptionArray()
        ));

        $fieldset->addField(
            'type_id',
            'hidden',
            array(
                'name' => 'type_id',
                'value' => Mage::registry('product')->getTypeId(),
            )
        );
        $this->setForm($form);
    }
}
