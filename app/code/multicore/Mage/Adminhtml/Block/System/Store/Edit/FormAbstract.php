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
 * Adminhtml store edit form
 *
 * @category    Mage
 * @package     Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
abstract class Mage_Adminhtml_Block_System_Store_Edit_FormAbstract extends Mage_Adminhtml_Block_Widget_Form
{

    /**
     * Class constructor
     *
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('coreStoreForm');
    }

    /**
     * Prepare form data
     *
     * return Mage_Adminhtml_Block_Widget_Form
     */
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form(array(
            'id'        => 'edit_form',
            'action'    => $this->getData('action'),
            'method'    => 'post'
        ));

        $this->_prepareStoreFieldSet($form);

        $form->addField('store_type', 'hidden', array(
            'name'      => 'store_type',
            'no_span'   => true,
            'value'     => Mage::registry('store_type')
        ));

        $form->addField('store_action', 'hidden', array(
            'name'      => 'store_action',
            'no_span'   => true,
            'value'     => Mage::registry('store_action')
        ));

        $form->setAction($this->getUrl('*/*/save'));
        $form->setUseContainer(true);
        $this->setForm($form);

        Mage::dispatchEvent('adminhtml_store_edit_form_prepare_form', array('block' => $this));

        return parent::_prepareForm();
    }

    /**
     * Build store type specific fieldset
     *
     * @abstract
     * @param Varien_Data_Form $form
     */
    abstract protected function _prepareStoreFieldset(Varien_Data_Form $form);
}
