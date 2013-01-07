<?php
/**
 * Web API user edit form.
 *
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
 * @copyright   Copyright (c) 2012 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @method Mage_Webapi_Block_Adminhtml_User_Edit setApiUser() setApiUser(Mage_Webapi_Model_Acl_User $user)
 * @method Mage_Webapi_Model_Acl_User getApiUser() getApiUser()
 */
class Mage_Webapi_Block_Adminhtml_User_Edit_Form extends Mage_Backend_Block_Widget_Form
{
    /**
     * Prepare Form.
     *
     * @return Mage_Webapi_Block_Adminhtml_User_Edit_Form
     */
    protected function _prepareForm()
    {
        $form = new Varien_Data_Form();
        $form->setId('edit_form');
        $form->setAction($this->getUrl('*/*/save'));
        $form->setMethod('post');
        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }
}
