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
 * @package     Mage_ImportExport
 * @copyright   Copyright (c) 2012 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * ImportExport import data resource model
 *
 * @category    Mage
 * @package     Mage_ImportExport
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_ImportExport_Model_Resource_Import_Data
    extends Mage_Core_Model_Resource_Db_Abstract
    implements IteratorAggregate
{
    /**
     * @var IteratorIterator
     */
    protected $_iterator = null;

    /**
     * Helper to encode/decode json
     *
     * @var Mage_Core_Helper_Data
     */
    protected $_jsonHelper;

    /**
     * Class constructor
     *
     * @param Mage_Core_Model_Resource $resource
     * @param Mage_Core_Helper_Data $coreHelper
     * @param array $arguments
     */
    public function __construct(Mage_Core_Model_Resource $resource,
        Mage_Core_Helper_Data $coreHelper,
        array $arguments = array()
    ) {
        parent::__construct($resource);
        $this->_jsonHelper = $coreHelper;
    }

    /**
     * Resource initialization
     */
    protected function _construct()
    {
        $this->_init('importexport_importdata', 'id');
    }

    /**
     * Retrieve an external iterator
     *
     * @return IteratorIterator
     */
    public function getIterator()
    {
        $adapter = $this->_getWriteAdapter();
        $select = $adapter->select()
            ->from($this->getMainTable(), array('data'))
            ->order('id ASC');
        $stmt = $adapter->query($select);

        $stmt->setFetchMode(Zend_Db::FETCH_NUM);
        if ($stmt instanceof IteratorAggregate) {
            $iterator = $stmt->getIterator();
        } else {
            // Statement doesn't support iterating, so fetch all records and create iterator ourself
            $rows = $stmt->fetchAll();
            $iterator = new ArrayIterator($rows);
        }

        return $iterator;
    }

    /**
     * Clean all bunches from table.
     *
     * @return Varien_Db_Adapter_Interface
     */
    public function cleanBunches()
    {
        return $this->_getWriteAdapter()->delete($this->getMainTable());
    }

    /**
     * Return behavior from import data table.
     *
     * @return string
     */
    public function getBehavior()
    {
        return $this->getUniqueColumnData('behavior');
    }

    /**
     * Return entity type code from import data table.
     *
     * @return string
     */
    public function getEntityTypeCode()
    {
        return $this->getUniqueColumnData('entity');
    }

    /**
     * Return request data from import data table
     *
     * @throws Mage_Core_Exception
     *
     * @param string $code parameter name
     * @return string
     */
    public function getUniqueColumnData($code)
    {
        $adapter = $this->_getReadAdapter();
        $values = array_unique($adapter->fetchCol(
            $adapter->select()
                ->from($this->getMainTable(), array($code))
        ));

        if (count($values) != 1) {
            Mage::throwException(
                Mage::helper('Mage_ImportExport_Helper_Data')->__('Error in data structure: %s values are mixed', $code)
            );
        }
        return $values[0];
    }

    /**
     * Get next bunch of validated rows.
     *
     * @return array|null
     */
    public function getNextBunch()
    {
        if (null === $this->_iterator) {
            $this->_iterator = $this->getIterator();
            $this->_iterator->rewind();
        }
        if ($this->_iterator->valid()) {
            $dataRow = $this->_iterator->current();
            $dataRow = $this->_jsonHelper->jsonDecode($dataRow[0]);
            $this->_iterator->next();
        } else {
            $this->_iterator = null;
            $dataRow = null;
        }
        return $dataRow;
    }

    /**
     * Save import rows bunch.
     *
     * @param string $entity
     * @param string $behavior
     * @param array $data
     * @return int
     */
    public function saveBunch($entity, $behavior, array $data)
    {
        return $this->_getWriteAdapter()->insert(
            $this->getMainTable(),
            array(
                'behavior'       => $behavior,
                'entity'         => $entity,
                'data'           => $this->_jsonHelper->jsonEncode($data)
            )
        );
    }
}
