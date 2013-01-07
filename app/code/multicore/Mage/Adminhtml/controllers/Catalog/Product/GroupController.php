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

class Mage_Adminhtml_Catalog_Product_GroupController extends Mage_Adminhtml_Controller_Action
{
    public function saveAction()
    {
        $model = Mage::getModel('Mage_Eav_Model_Entity_Attribute_Group');

        $model->setAttributeGroupName($this->getRequest()->getParam('attribute_group_name'))
              ->setAttributeSetId($this->getRequest()->getParam('attribute_set_id'));

        if( $model->itemExists() ) {
            Mage::getSingleton('Mage_Adminhtml_Model_Session')->addError(Mage::helper('Mage_Catalog_Helper_Data')->__('A group with the same name already exists.'));
        } else {
            try {
                $model->save();
            } catch (Exception $e) {
                Mage::getSingleton('Mage_Adminhtml_Model_Session')->addError(Mage::helper('Mage_Catalog_Helper_Data')->__('An error occurred while saving this group.'));
            }
        }
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('Mage_Core_Model_Authorization')->isAllowed('Mage_Catalog::products');
    }
}
