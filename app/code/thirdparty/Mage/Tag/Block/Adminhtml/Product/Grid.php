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
 * @package     Mage_Tag
 * @copyright   Copyright (c) 2012 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Child Of Mage_Tag_Block_Adminhtml_Product
 *
 * @category   Mage
 * @package    Mage_Tag
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_Tag_Block_Adminhtml_Product_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    protected function _construct()
    {
        parent::_construct();
        if (Mage::registry('current_tag')) {
            $this->setId('tag_product_grid' . Mage::registry('current_tag')->getId());
        }
        $this->setDefaultSort('name');
        $this->setDefaultDir('ASC');
        $this->setUseAjax(true);
    }

    /*
     * Retrieves Grid Url
     *
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/product', array('_current' => true));
    }

    protected function _prepareCollection()
    {
        $tagId = Mage::registry('current_tag')->getId();
        $storeId = Mage::registry('current_tag')->getStoreId();
        $collection = Mage::getModel('Mage_Tag_Model_Tag')
            ->getEntityCollection()
            ->addTagFilter($tagId)
            ->addCustomerFilter(array('null' => false))
            ->addStoreFilter($storeId)
            ->addPopularity($tagId);

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _afterLoadCollection()
    {
        return parent::_afterLoadCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('product_id', array(
            'header'        => Mage::helper('Mage_Tag_Helper_Data')->__('ID'),
            'width'         => '50px',
            'align'         => 'right',
            'index'         => 'entity_id',
        ));

        $this->addColumn('name', array(
            'header'    => Mage::helper('Mage_Tag_Helper_Data')->__('Product Name'),
            'index'     => 'name',
        ));

        $this->addColumn('popularity', array(
            'header'        => Mage::helper('Mage_Tag_Helper_Data')->__('# of Uses'),
            'width'         => '50px',
            'align'         => 'right',
            'index'         => 'popularity',
            'type'          => 'number'
        ));

        $this->addColumn('sku', array(
            'header'    => Mage::helper('Mage_Tag_Helper_Data')->__('SKU'),
            'filter'    => false,
            'sortable'  => false,
            'width'     => 50,
            'align'     => 'right',
            'index'     => 'sku',
        ));

        return parent::_prepareColumns();
    }

    protected function _addColumnFilterToCollection($column)
    {
        if ($column->getIndex() == 'popularity') {
            $this->getCollection()->addPopularityFilter($column->getFilter()->getCondition());
            return $this;
        } else {
            return parent::_addColumnFilterToCollection($column);
        }
    }

    public function getRowUrl($row)
    {
        return $this->getUrl('*/catalog_product/edit', array('id' => $row->getProductId()));
    }

}
