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
 * Adminhtml  system templates grid block
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Adminhtml_Block_System_Email_Template_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    protected function _construct()
    {
        parent::_construct();
        $this->setEmptyText(Mage::helper('Mage_Adminhtml_Helper_Data')->__('No Templates Found'));
        $this->setId('systemEmailTemplateGrid');
        $this->setUseAjax(true);
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getResourceSingleton('Mage_Core_Model_Resource_Email_Template_Collection');

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('template_id',
            array(
                  'header'=>Mage::helper('Mage_Adminhtml_Helper_Data')->__('ID'),
                  'index'=>'template_id'
            )
        );

        $this->addColumn('code',
            array(
                'header'=>Mage::helper('Mage_Adminhtml_Helper_Data')->__('Template Name'),
                'index'=>'template_code'
        ));

        $this->addColumn('added_at',
            array(
                'header'=>Mage::helper('Mage_Adminhtml_Helper_Data')->__('Date Added'),
                'index'=>'added_at',
                'gmtoffset' => true,
                'type'=>'datetime'
        ));

        $this->addColumn('modified_at',
            array(
                'header'=>Mage::helper('Mage_Adminhtml_Helper_Data')->__('Date Updated'),
                'index'=>'modified_at',
                'gmtoffset' => true,
                'type'=>'datetime'
        ));

        $this->addColumn('subject',
            array(
                'header'=>Mage::helper('Mage_Adminhtml_Helper_Data')->__('Subject'),
                'index'=>'template_subject'
        ));
        /*
        $this->addColumn('sender',
            array(
                'header'=>Mage::helper('Mage_Adminhtml_Helper_Data')->__('Sender'),
                'index'=>'template_sender_email',
                'renderer' => 'Mage_Adminhtml_Block_System_Email_Template_Grid_Renderer_Sender'
        ));
        */
        $this->addColumn('type',
            array(
                'header'=>Mage::helper('Mage_Adminhtml_Helper_Data')->__('Template Type'),
                'index'=>'template_type',
                'filter' => 'Mage_Adminhtml_Block_System_Email_Template_Grid_Filter_Type',
                'renderer' => 'Mage_Adminhtml_Block_System_Email_Template_Grid_Renderer_Type'
        ));

        $this->addColumn('action',
            array(
                'header'	=> Mage::helper('Mage_Adminhtml_Helper_Data')->__('Action'),
                'index'		=> 'template_id',
                'sortable'  => false,
                'filter' 	=> false,
                'width'		=> '100px',
                'renderer'  => 'Mage_Adminhtml_Block_System_Email_Template_Grid_Renderer_Action'
        ));
        return $this;
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('id'=>$row->getId()));
    }

}

