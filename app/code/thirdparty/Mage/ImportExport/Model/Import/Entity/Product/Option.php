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
 * Entity class which provide possibility to import product custom options
 *
 * @category    Mage
 * @package     Mage_ImportExport
 * @author      Magento Core Team <core@magentocommerce.com>
 *
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @todo Need to explode this class because of several responsibilities
 * @todo Refactor in the scope of https://wiki.magento.com/display/MAGE2/Technical+Debt+%28Team-Donetsk-B%29
 */
class Mage_ImportExport_Model_Import_Entity_Product_Option extends Mage_ImportExport_Model_Import_Entity_Abstract
{
    /**#@+
     * Custom option column names
     */
    const COLUMN_SKU         = 'sku';
    const COLUMN_PREFIX      = '_custom_option_';
    const COLUMN_STORE       = '_custom_option_store';
    const COLUMN_TYPE        = '_custom_option_type';
    const COLUMN_TITLE       = '_custom_option_title';
    const COLUMN_IS_REQUIRED = '_custom_option_is_required';
    const COLUMN_SORT_ORDER  = '_custom_option_sort_order';
    const COLUMN_ROW_TITLE   = '_custom_option_row_title';
    const COLUMN_ROW_PRICE   = '_custom_option_row_price';
    const COLUMN_ROW_SKU     = '_custom_option_row_sku';
    const COLUMN_ROW_SORT    = '_custom_option_row_sort';
    /**#@-*/

    /**
     * XML path to page size parameter
     */
    const XML_PATH_PAGE_SIZE = 'import/format_v1/page_size';

    /**
     * All stores code-ID pairs
     *
     * @var array
     */
    protected $_storeCodeToId = array();

    /**
     * List of products sku-ID pairs
     *
     * @var array
     */
    protected $_productsSkuToId = array();

    /**
     * Instance of import/export resource helper
     *
     * @var Mage_ImportExport_Model_Resource_Helper_Mysql4
     */
    protected $_resourceHelper;

    /**
     * Array of data helpers
     *
     * @var array
     */
    protected $_helpers;

    /**
     * Flag for global prices property
     *
     * @var bool
     */
    protected $_isPriceGlobal;

    /**
     * List of specific custom option types
     *
     * @var array
     */
    protected $_specificTypes = array(
        'date'      => array('price', 'sku'),
        'date_time' => array('price', 'sku'),
        'time'      => array('price', 'sku'),
        'field'     => array('price', 'sku', 'max_characters'),
        'area'      => array('price', 'sku', 'max_characters'),
        'drop_down' => true,
        'radio'     => true,
        'checkbox'  => true,
        'multiple'  => true
    );

    /**
     * Keep product id value for every row which will be imported
     *
     * @var int
     */
    protected $_rowProductId;

    /**
     * Keep product sku value for every row during validation
     *
     * @var string
     */
    protected $_rowProductSku;

    /**
     * Keep store id value for every row which will be imported
     *
     * @var int
     */
    protected $_rowStoreId;

    /**
     * Keep information about row status
     *
     * @var int
     */
    protected $_rowIsMain;

    /**
     * Keep type value for every row which will be imported
     *
     * @var int
     */
    protected $_rowType;

    /**
     * Product model instance
     *
     * @var Mage_Catalog_Model_Product
     */
    protected $_productModel;

    /**
     * DB data source model
     *
     * @var Mage_ImportExport_Model_Resource_Import_Data
     */
    protected $_dataSourceModel;

    /**
     * DB connection
     *
     * @var Varien_Db_Adapter_Interface
     */
    protected $_connection;

    /**
     * Custom options tables
     *
     * @var array
     */
    protected $_tables = array(
        'catalog_product_entity'            => null,
        'catalog_product_option'            => null,
        'catalog_product_option_title'      => null,
        'catalog_product_option_type_title' => null,
        'catalog_product_option_type_value' => null,
        'catalog_product_option_type_price' => null,
        'catalog_product_option_price'      => null,
    );

    /**
     * Parent import product entity
     *
     * @var Mage_ImportExport_Model_Import_Entity_Product
     */
    protected $_productEntity;

    /**
     * Existing custom options data
     *
     * @var array
     */
    protected $_oldCustomOptions;

    /**
     * New custom options data for existing products
     *
     * @var array
     */
    protected $_newOptionsOldData = array();

    /**
     * New custom options data for not existing products
     *
     * @var array
     */
    protected $_newOptionsNewData = array();

    /**
     * New custom options counter
     *
     * @var int
     */
    protected $_newCustomOptionId = 0;

    /**
     * Product options collection
     *
     * @var Mage_Catalog_Model_Resource_Product_Option_Collection
     */
    protected $_optionCollection;

    /**#@+
     * Error codes
     */
    const ERROR_INVALID_STORE          = 'optionInvalidStore';
    const ERROR_INVALID_TYPE           = 'optionInvalidType';
    const ERROR_EMPTY_TITLE            = 'optionEmptyTitle';
    const ERROR_INVALID_PRICE          = 'optionInvalidPrice';
    const ERROR_INVALID_MAX_CHARACTERS = 'optionInvalidMaxCharacters';
    const ERROR_INVALID_SORT_ORDER     = 'optionInvalidSortOrder';
    const ERROR_INVALID_ROW_PRICE      = 'optionInvalidRowPrice';
    const ERROR_INVALID_ROW_SORT       = 'optionInvalidRowSort';
    const ERROR_AMBIGUOUS_NEW_NAMES    = 'optionAmbiguousNewNames';
    const ERROR_AMBIGUOUS_OLD_NAMES    = 'optionAmbiguousOldNames';
    const ERROR_AMBIGUOUS_TYPES        = 'optionAmbiguousTypes';
    /**#@-*/

    /**
     * Collection by pages iterator
     *
     * @var Mage_ImportExport_Model_Resource_CollectionByPagesIterator
     */
    protected $_byPagesIterator;

    /**
     * Number of items to fetch from db in one query
     *
     * @var int
     */
    protected $_pageSize;

    /**
     * Constructor
     *
     * @param array $data
     */
    public function __construct(array $data = array())
    {
        if (isset($data['connection'])) {
            $this->_connection = $data['connection'];
        } else {
            $this->_connection = Mage::getSingleton('Mage_Core_Model_Resource')->getConnection('write');
        }
        if (isset($data['resource_helper'])) {
            $this->_resourceHelper = $data['resource_helper'];
        } else {
            $this->_resourceHelper = Mage::getResourceHelper('Mage_ImportExport');
        }

        if (isset($data['helpers'])) {
            $this->_helpers = $data['helpers'];
        }

        if (isset($data['is_price_global'])) {
            $this->_isPriceGlobal = $data['is_price_global'];
        } else {
            /** @var $catalogHelper Mage_Catalog_Helper_Data */
            $catalogHelper = Mage::helper('Mage_Catalog_Helper_Data');
            $this->_isPriceGlobal = $catalogHelper->isPriceGlobal();
        }

        $this->_initSourceEntities($data)
            ->_initTables($data)
            ->_initStores($data);

        $this->_initMessageTemplates();

        $this->_initProductsSku()
            ->_initOldCustomOptions();
    }

    /**
     * Initialization of error message templates
     *
     * @return Mage_ImportExport_Model_Import_Entity_Product_Option
     */
    protected function _initMessageTemplates()
    {
        // @codingStandardsIgnoreStart
        $this->_productEntity->addMessageTemplate(self::ERROR_INVALID_STORE,
            $this->_helper('Mage_ImportExport_Helper_Data')->__('Invalid custom option store.')
        );
        $this->_productEntity->addMessageTemplate(self::ERROR_INVALID_TYPE,
            $this->_helper('Mage_ImportExport_Helper_Data')->__('Invalid custom option type.')
        );
        $this->_productEntity->addMessageTemplate(self::ERROR_EMPTY_TITLE,
            $this->_helper('Mage_ImportExport_Helper_Data')->__('Empty custom option title.')
        );
        $this->_productEntity->addMessageTemplate(self::ERROR_INVALID_PRICE,
            $this->_helper('Mage_ImportExport_Helper_Data')->__('Invalid custom option price.')
        );
        $this->_productEntity->addMessageTemplate(self::ERROR_INVALID_MAX_CHARACTERS,
            $this->_helper('Mage_ImportExport_Helper_Data')->__('Invalid custom option maximum characters value.')
        );
        $this->_productEntity->addMessageTemplate(self::ERROR_INVALID_SORT_ORDER,
            $this->_helper('Mage_ImportExport_Helper_Data')->__('Invalid custom option sort order.')
        );
        $this->_productEntity->addMessageTemplate(self::ERROR_INVALID_ROW_PRICE,
            $this->_helper('Mage_ImportExport_Helper_Data')->__('Invalid custom option value price.')
        );
        $this->_productEntity->addMessageTemplate(self::ERROR_INVALID_ROW_SORT,
            $this->_helper('Mage_ImportExport_Helper_Data')->__('Invalid custom option value sort order.')
        );
        $this->_productEntity->addMessageTemplate(self::ERROR_AMBIGUOUS_NEW_NAMES,
            $this->_helper('Mage_ImportExport_Helper_Data')->__('Custom option with such title already declared in source file.')
        );
        $this->_productEntity->addMessageTemplate(self::ERROR_AMBIGUOUS_OLD_NAMES,
            $this->_helper('Mage_ImportExport_Helper_Data')->__('There are several existing custom options with such name.')
        );
        $this->_productEntity->addMessageTemplate(self::ERROR_AMBIGUOUS_TYPES,
            $this->_helper('Mage_ImportExport_Helper_Data')->__('Custom options have different types.')
        );
        // @codingStandardsIgnoreEnd
        return $this;
    }

    /**
     * Helper getter
     *
     * @param string $helperName
     * @return Mage_Core_Helper_Abstract
     */
    protected function _helper($helperName)
    {
        return isset($this->_helpers[$helperName]) ? $this->_helpers[$helperName] : Mage::helper($helperName);
    }

    /**
     * Initialize table names
     *
     * @param array $data
     * @return Mage_ImportExport_Model_Import_Entity_Product_Option
     */
    protected function _initTables(array $data)
    {
        if (isset($data['tables'])) {
            // all the entries of $data['tables'] which have keys that are present in $this->_tables
            $tables = array_intersect_key($data['tables'], $this->_tables);
            $this->_tables = array_merge($this->_tables, $tables);
        }
        foreach ($this->_tables as $key => $value) {
            if ($value == null) {
                $this->_tables[$key] = Mage::getSingleton('Mage_Core_Model_Resource')->getTableName($key);
            }
        }
        return $this;
    }

    /**
     * Initialize stores data
     *
     * @param array $data
     * @return Mage_ImportExport_Model_Import_Entity_Product_Option
     */
    protected function _initStores(array $data)
    {
        if (isset($data['stores'])) {
            $this->_storeCodeToId = $data['stores'];
        } else {
            /** @var $store Mage_Core_Model_Store */
            foreach (Mage::app()->getStores(true) as $store) {
                $this->_storeCodeToId[$store->getCode()] = $store->getId();
            }
        }
        return $this;
    }

    /**
     * Initialize source entities and collections
     *
     * @param array $data
     * @return Mage_ImportExport_Model_Import_Entity_Product_Option
     */
    protected function _initSourceEntities(array $data)
    {
        if (isset($data['data_source_model'])) {
            $this->_dataSourceModel = $data['data_source_model'];
        } else {
            $this->_dataSourceModel = Mage_ImportExport_Model_Import::getDataSourceModel();
        }
        if (isset($data['product_model'])) {
            $this->_productModel = $data['product_model'];
        } else {
            $this->_productModel = Mage::getModel('Mage_Catalog_Model_Product');
        }
        if (isset($data['option_collection'])) {
            $this->_optionCollection = $data['option_collection'];
        } else {
            $this->_optionCollection = Mage::getResourceModel('Mage_Catalog_Model_Resource_Product_Option_Collection');
        }
        if (isset($data['product_entity'])) {
            $this->_productEntity = $data['product_entity'];
        } else {
            Mage::throwException(
                $this->_helper('Mage_ImportExport_Helper_Data')->__('Option entity must have a parent product entity.')
            );
        }
        if (isset($data['collection_by_pages_iterator'])) {
            $this->_byPagesIterator = $data['collection_by_pages_iterator'];
        } else {
            $this->_byPagesIterator
                = Mage::getResourceModel('Mage_ImportExport_Model_Resource_CollectionByPagesIterator');
        }
        if (isset($data['page_size'])) {
            $this->_pageSize = $data['page_size'];
        } else {
            $this->_pageSize = self::XML_PATH_PAGE_SIZE ? (int) Mage::getStoreConfig(self::XML_PATH_PAGE_SIZE) : 0;
        }
        return $this;
    }

    /**
     * Load exiting custom options data
     *
     * @return Mage_ImportExport_Model_Import_Entity_Product_Option
     */
    protected function _initOldCustomOptions()
    {
        if (!$this->_oldCustomOptions) {
            $oldCustomOptions = array();
            $optionTitleTable = $this->_tables['catalog_product_option_title'];
            $productIds = array_values($this->_productsSkuToId);
            foreach ($this->_storeCodeToId as $storeId) {
                $addCustomOptions = function (Mage_Catalog_Model_Product_Option $customOption)
                    use (&$oldCustomOptions, $storeId)
                {
                    $productId = $customOption->getProductId();
                    if (!isset($oldCustomOptions[$productId])) {
                        $oldCustomOptions[$productId] = array();
                    }
                    if (isset($oldCustomOptions[$productId][$customOption->getId()])) {
                        $oldCustomOptions[$productId][$customOption->getId()]['titles'][$storeId]
                            = $customOption->getTitle();
                    } else {
                        $oldCustomOptions[$productId][$customOption->getId()] = array(
                            'titles' => array($storeId => $customOption->getTitle()),
                            'type'   => $customOption->getType()
                        );
                    }
                };
                /** @var $collection Mage_Catalog_Model_Resource_Product_Option_Collection */
                $this->_optionCollection->reset();
                $this->_optionCollection->addProductToFilter($productIds);
                $this->_optionCollection->getSelect()
                    ->join(
                    array('option_title' => $optionTitleTable),
                    'option_title.option_id = main_table.option_id',
                    array('title' => 'title', 'store_id' => 'store_id')
                )->where('option_title.store_id = ?', $storeId);

                $this->_byPagesIterator->iterate($this->_optionCollection, $this->_pageSize, array($addCustomOptions));
            }
            $this->_oldCustomOptions = $oldCustomOptions;
        }
        return $this;
    }

    /**
     * Imported entity type code getter
     *
     * @return string
     */
    public function getEntityTypeCode()
    {
        return 'product_options';
    }

    /**
     * Validate ambiguous situations:
     * - several custom options have the same name in input file;
     * - several custom options have the same name in DB;
     * - custom options with the same name have different data types.
     *
     * @return bool
     */
    public function validateAmbiguousData()
    {
        $errorRows = $this->_findNewOptionsWithTheSameTitles();
        if ($errorRows) {
            $this->_addRowsErrors(self::ERROR_AMBIGUOUS_NEW_NAMES, $errorRows);
            return false;
        }
        if ($this->getBehavior() == Mage_ImportExport_Model_Import::BEHAVIOR_APPEND) {
            $errorRows = $this->_findOldOptionsWithTheSameTitles();
            if ($errorRows) {
                $this->_addRowsErrors(self::ERROR_AMBIGUOUS_OLD_NAMES, $errorRows);
                return false;
            }
            $errorRows = $this->_findNewOldOptionsTypeMismatch();
            if ($errorRows) {
                $this->_addRowsErrors(self::ERROR_AMBIGUOUS_TYPES, $errorRows);
                return false;
            }
        }
        return true;
    }

    /**
     * Find options with the same titles for input data
     *
     * @return array
     */
    protected function _findNewOptionsWithTheSameTitles()
    {
        $errorRows = array_unique(array_merge(
            $this->_getNewOptionsWithTheSameTitlesErrorRows($this->_newOptionsNewData),
            $this->_getNewOptionsWithTheSameTitlesErrorRows($this->_newOptionsOldData)
        ));
        sort($errorRows);
        return $errorRows;
    }

    /**
     * Get error rows numbers for required product data
     *
     * @param array $sourceProductData
     * @return array
     */
    protected function _getNewOptionsWithTheSameTitlesErrorRows(array $sourceProductData)
    {
        $errorRows = array();
        foreach ($sourceProductData as $options) {
            foreach ($options as $outerKey => $outerData) {
                foreach ($options as $innerKey => $innerData) {
                    if ($innerKey != $outerKey) {
                        if (count($outerData['titles']) == count($innerData['titles'])) {
                            $outerTitles = $outerData['titles'];
                            $innerTitles = $innerData['titles'];
                            ksort($outerTitles);
                            ksort($innerTitles);
                            if ($outerTitles === $innerTitles) {
                                $errorRows = array_merge($errorRows, $innerData['rows'], $outerData['rows']);
                            }
                        }
                    }
                }
            }
        }
        return $errorRows;
    }

    /**
     * Find options with the same titles in DB
     *
     * @return array
     */
    protected function _findOldOptionsWithTheSameTitles()
    {
        $errorRows = array();
        foreach ($this->_newOptionsOldData as $productId => $options) {
            foreach ($options as $outerData) {
                if (isset($this->_oldCustomOptions[$productId])) {
                    $optionsCount = 0;
                    foreach ($this->_oldCustomOptions[$productId] as $innerData) {
                        if (count($outerData['titles']) == count($innerData['titles'])) {
                            $outerTitles = $outerData['titles'];
                            $innerTitles = $innerData['titles'];
                            ksort($outerTitles);
                            ksort($innerTitles);
                            if ($outerTitles === $innerTitles) {
                                $optionsCount++;
                            }
                        }
                    }
                    if ($optionsCount > 1) {
                        $errorRows = array_merge($errorRows, $outerData['rows']);
                    }
                }
            }
        }
        sort($errorRows);
        return $errorRows;
    }

    /**
     * Find source file options, which have analogs in DB with the same name, but with different type
     *
     * @return array
     */
    protected function _findNewOldOptionsTypeMismatch()
    {
        $errorRows = array();
        foreach ($this->_newOptionsOldData as $productId => $options) {
            foreach ($options as $outerData) {
                if (isset($this->_oldCustomOptions[$productId])) {
                    foreach ($this->_oldCustomOptions[$productId] as $innerData) {
                        if (count($outerData['titles']) == count($innerData['titles'])) {
                            $outerTitles = $outerData['titles'];
                            $innerTitles = $innerData['titles'];
                            ksort($outerTitles);
                            ksort($innerTitles);
                            if ($outerTitles === $innerTitles && $outerData['type'] != $innerData['type']) {
                                $errorRows = array_merge($errorRows, $outerData['rows']);
                            }
                        }
                    }
                }
            }
        }
        sort($errorRows);
        return $errorRows;
    }

    /**
     * Checks that option exists in DB
     *
     * @param array $newOptionData
     * @param array $newOptionTitles
     * @return bool|int
     */
    protected function _findExistingOptionId(array $newOptionData, array $newOptionTitles)
    {
        $productId = $newOptionData['product_id'];
        if (isset($this->_oldCustomOptions[$productId])) {
            ksort($newOptionTitles);
            $existingOptions = $this->_oldCustomOptions[$productId];
            foreach ($existingOptions as $optionId => $optionData) {
                if ($optionData['type'] == $newOptionData['type'] && $optionData['titles'] == $newOptionTitles) {
                    return $optionId;
                }
            }
        }

        return false;
    }

    /**
     * Add errors for all required rows
     *
     * @param string $errorCode
     * @param array $errorNumbers
     */
    protected function _addRowsErrors($errorCode, array $errorNumbers)
    {
        foreach ($errorNumbers as $rowNumber) {
            $this->_productEntity->addRowError($errorCode, $rowNumber);
        }
    }

    /**
     * Validate main custom option row
     *
     * @param array $rowData
     * @param int $rowNumber
     * @return bool
     */
    protected function _validateMainRow(array $rowData, $rowNumber)
    {
        if (!empty($rowData[self::COLUMN_STORE])
            && !array_key_exists($rowData[self::COLUMN_STORE], $this->_storeCodeToId)) {
            $this->_productEntity->addRowError(self::ERROR_INVALID_STORE, $rowNumber);
        } elseif (!empty($rowData[self::COLUMN_TYPE])
            && !array_key_exists($rowData[self::COLUMN_TYPE], $this->_specificTypes)) {   // type
            $this->_productEntity->addRowError(self::ERROR_INVALID_TYPE, $rowNumber);
        } elseif (empty($rowData[self::COLUMN_TITLE])) {                             // title
            $this->_productEntity->addRowError(self::ERROR_EMPTY_TITLE, $rowNumber);
        } elseif ($this->_validateSpecificTypeParameters($rowData, $rowNumber)) {     // price, max_character
            if ($this->_validateMainRowAdditionalData($rowData, $rowNumber)) {
                $this->_saveNewOptionData($rowData, $rowNumber);
                return true;
            }
        }
        return false;
    }

    /**
     * Validation of additional data in main row
     *
     * @param array $rowData
     * @param int $rowNumber
     * @return bool
     */
    protected function _validateMainRowAdditionalData(array $rowData, $rowNumber)
    {
        if (!empty($rowData[self::COLUMN_SORT_ORDER]) && !ctype_digit((string)$rowData[self::COLUMN_SORT_ORDER])) {
            $this->_productEntity->addRowError(self::ERROR_INVALID_SORT_ORDER, $rowNumber);
        } else {
            return true;
        }
        return false;
    }

    /**
     * Save validated option data
     *
     * @param array $rowData
     * @param $rowNumber
     */
    protected function _saveNewOptionData(array $rowData, $rowNumber)
    {
        if (!empty($rowData[self::COLUMN_SKU])) {
            $this->_rowProductSku = $rowData[self::COLUMN_SKU];
        }
        if (!empty($rowData[self::COLUMN_TYPE])) {
            $this->_newCustomOptionId++;
        }
        // get store ID
        if (!empty($rowData[self::COLUMN_STORE])) {
            $storeCode = $rowData[self::COLUMN_STORE];
            $storeId = $this->_storeCodeToId[$storeCode];
        } else {
            $storeId = Mage_Core_Model_App::ADMIN_STORE_ID;
        }
        if (isset($this->_productsSkuToId[$this->_rowProductSku])) {
            // save in existing data array
            $productId = $this->_productsSkuToId[$this->_rowProductSku];
            if (!isset($this->_newOptionsOldData[$productId])) {
                $this->_newOptionsOldData[$productId] = array();
            }
            if (!isset($this->_newOptionsOldData[$productId][$this->_newCustomOptionId])) {
                $this->_newOptionsOldData[$productId][$this->_newCustomOptionId] = array(
                    'titles' => array(),
                    'rows'   => array(),
                    'type'   => $rowData[self::COLUMN_TYPE],
                );
            }
            // set title
            $this->_newOptionsOldData[$productId][$this->_newCustomOptionId]['titles'][$storeId]
                = $rowData[self::COLUMN_TITLE];
            // set row number
            $this->_newOptionsOldData[$productId][$this->_newCustomOptionId]['rows'][] = $rowNumber;
        } else {
            // save in new data array
            $productSku = $this->_rowProductSku;
            if (!isset($this->_newOptionsNewData[$this->_rowProductSku])) {
                $this->_newOptionsNewData[$this->_rowProductSku] = array();
            }
            if (!isset($this->_newOptionsNewData[$productSku][$this->_newCustomOptionId])) {
                $this->_newOptionsNewData[$productSku][$this->_newCustomOptionId] = array(
                    'titles' => array(),
                    'rows'   => array(),
                    'type'   => $rowData[self::COLUMN_TYPE],
                );
            }
            // set title
            $this->_newOptionsNewData[$productSku][$this->_newCustomOptionId]['titles'][$storeId]
                = $rowData[self::COLUMN_TITLE];
            // set row number
            $this->_newOptionsNewData[$productSku][$this->_newCustomOptionId]['rows'][] = $rowNumber;
        }
    }

    /**
     * Validate secondary custom option row
     *
     * @param array $rowData
     * @param int $rowNumber
     * @return bool
     */
    protected function _validateSecondaryRow(array $rowData, $rowNumber)
    {
        if (!empty($rowData[self::COLUMN_STORE])
            && !array_key_exists($rowData[self::COLUMN_STORE], $this->_storeCodeToId)) {
            $this->_productEntity->addRowError(self::ERROR_INVALID_STORE, $rowNumber);
        } elseif (!empty($rowData[self::COLUMN_ROW_PRICE])
            && !is_numeric(rtrim($rowData[self::COLUMN_ROW_PRICE], '%'))) {
            $this->_productEntity->addRowError(self::ERROR_INVALID_ROW_PRICE, $rowNumber);
        } elseif (!empty($rowData[self::COLUMN_ROW_SORT]) && !ctype_digit((string)$rowData[self::COLUMN_ROW_SORT])) {
            $this->_productEntity->addRowError(self::ERROR_INVALID_ROW_SORT, $rowNumber);
        } else {
            if (isset($this->_productsSkuToId[$this->_rowProductSku])) {
                $productId = $this->_productsSkuToId[$this->_rowProductSku];
                $this->_newOptionsOldData[$productId][$this->_newCustomOptionId]['rows'][] = $rowNumber;
            } else {
                $productSku = $this->_rowProductSku;
                $this->_newOptionsNewData[$productSku][$this->_newCustomOptionId]['rows'][] = $rowNumber;
            }
            return true;
        }
        return false;
    }

    /**
     * Validate data row
     *
     * @param array $rowData
     * @param int $rowNumber
     * @return bool
     */
    public function validateRow(array $rowData, $rowNumber)
    {
        if (isset($this->_validatedRows[$rowNumber])) {
            return !isset($this->_invalidRows[$rowNumber]);
        }
        $this->_validatedRows[$rowNumber] = true;

        if ($this->_isRowWithCustomOption($rowData)) {
            if ($this->_isMainOptionRow($rowData)) {
                if (!$this->_validateMainRow($rowData, $rowNumber)) {
                    return false;
                }
            }
            if ($this->_isSecondaryOptionRow($rowData)) {
                if (!$this->_validateSecondaryRow($rowData, $rowNumber)) {
                    return false;
                }
            }
            return true;
        }
        return false;
    }

    /**
     * Validation of specific type parameters
     *
     * @param array $rowData
     * @param int $rowNumber
     * @return bool
     */
    protected function _validateSpecificTypeParameters(array $rowData, $rowNumber)
    {
        if (!empty($rowData[self::COLUMN_TYPE])) {
            if (isset($this->_specificTypes[$rowData[self::COLUMN_TYPE]])) {
                $typeParameters = $this->_specificTypes[$rowData[self::COLUMN_TYPE]];
                if (is_array($typeParameters)) {
                    foreach ($typeParameters as $typeParameter) {
                        if (!$this->_validateSpecificParameterData($typeParameter, $rowData, $rowNumber)) {
                            return false;
                        }
                    }
                }
            } else {
                return false;
            }
        }
        return true;
    }

    /**
     * Validate one specific parameter
     *
     * @param string $typeParameter
     * @param array $rowData
     * @param int $rowNumber
     * @return bool
     */
    protected function _validateSpecificParameterData($typeParameter, array $rowData, $rowNumber)
    {
        $fieldName = self::COLUMN_PREFIX . $typeParameter;
        if ($typeParameter == 'price') {
            if (!empty($rowData[$fieldName]) && !is_numeric(rtrim($rowData[$fieldName], '%'))) {
                $this->_productEntity->addRowError(self::ERROR_INVALID_PRICE, $rowNumber);
                return false;
            }
        } elseif ($typeParameter == 'max_characters') {
            if (!empty($rowData[$fieldName]) && !ctype_digit((string)$rowData[$fieldName])) {
                $this->_productEntity->addRowError(self::ERROR_INVALID_MAX_CHARACTERS, $rowNumber);
                return false;
            }
        }
        return true;
    }

    /**
     * Checks that current row contains custom option information
     *
     * @param array $rowData
     * @return bool
     */
    protected function _isRowWithCustomOption(array $rowData)
    {
        return !empty($rowData[self::COLUMN_TYPE])
            || !empty($rowData[self::COLUMN_TITLE])
            || !empty($rowData[self::COLUMN_ROW_TITLE]);
    }

    /**
     * Checks that current row a main option row (i.e. contains option data)
     *
     * @param array $rowData
     * @return bool
     */
    protected function _isMainOptionRow(array $rowData)
    {
        return !empty($rowData[self::COLUMN_TYPE]) || !empty($rowData[self::COLUMN_TITLE]);
    }

    /**
     * Checks that current row a secondary option row (i.e. contains option value data)
     *
     * @param array $rowData
     * @return bool
     */
    protected function _isSecondaryOptionRow(array $rowData)
    {
        return !empty($rowData[self::COLUMN_ROW_TITLE]);
    }

    /**
     * Checks that complex options contain values
     *
     * @param array $options
     * @param array $titles
     * @param array $typeValues
     * @return bool
     */
    protected function _isReadyForSaving(array &$options, array &$titles, array $typeValues)
    {
        // if complex options do not contain values - ignore them
        foreach ($options as $key => $optionData) {
            $optionId = $optionData['option_id'];
            $optionType = $optionData['type'];
            if ($this->_specificTypes[$optionType] === true && !isset($typeValues[$optionId])) {
                unset($options[$key], $titles[$optionId]);
            }
        }
        if ($options) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Import data rows
     *
     * @return boolean
     */
    protected function _importData()
    {
        $this->_initProductsSku();

        $nextOptionId = $this->_resourceHelper->getNextAutoincrement($this->_tables['catalog_product_option']);
        $nextValueId  = $this->_resourceHelper->getNextAutoincrement(
            $this->_tables['catalog_product_option_type_value']
        );
        $prevOptionId = 0;

        while ($bunch = $this->_dataSourceModel->getNextBunch()) {
            $products   = array();
            $options    = array();
            $titles     = array();
            $prices     = array();
            $typeValues = array();
            $typePrices = array();
            $typeTitles = array();

            foreach ($bunch as $rowNumber => $rowData) {
                if (!$this->isRowAllowedToImport($rowData, $rowNumber)) {
                    continue;
                }
                if (!$this->_parseRequiredData($rowData)) {
                    continue;
                }
                $optionData = $this->_collectOptionMainData($rowData, $prevOptionId, $nextOptionId, $products, $prices);
                if ($optionData != null) {
                    $options[] = $optionData;
                }
                $this->_collectOptionTypeData($rowData, $prevOptionId, $nextValueId, $typeValues, $typePrices,
                    $typeTitles
                );
                $this->_collectOptionTitle($rowData, $prevOptionId, $titles);
            }

            // Save prepared custom options data !!!
            if ($this->getBehavior() != Mage_ImportExport_Model_Import::BEHAVIOR_APPEND) {
                $this->_deleteEntities(array_keys($products));
            }

            if ($this->_isReadyForSaving($options, $titles, $typeValues)) {
                if ($this->getBehavior() == Mage_ImportExport_Model_Import::BEHAVIOR_APPEND) {
                    $this->_compareOptionsWithExisting($options, $titles, $prices, $typeValues);
                }
                $this->_saveOptions($options)
                    ->_saveTitles($titles)
                    ->_savePrices($prices)
                    ->_saveSpecificTypeValues($typeValues)
                    ->_saveSpecificTypePrices($typePrices)
                    ->_saveSpecificTypeTitles($typeTitles)
                    ->_updateProducts($products);
            }
        }

        return true;
    }


    /**
     * Load data of existed products
     *
     * @return Mage_ImportExport_Model_Import_Entity_Product_Option
     */
    protected function _initProductsSku()
    {
        if (!$this->_productsSkuToId) {
            $columns = array('entity_id', 'sku');
            foreach ($this->_productModel->getProductEntitiesInfo($columns) as $product) {
                $this->_productsSkuToId[$product['sku']] = $product['entity_id'];
            }
        }

        return $this;
    }

    /**
     * Collect custom option main data to import
     *
     * @param array $rowData
     * @param int $prevOptionId
     * @param int $nextOptionId
     * @param array $products
     * @param array $prices
     * @return array|null
     */
    protected function _collectOptionMainData(array $rowData, &$prevOptionId, &$nextOptionId, array &$products,
        array &$prices
    ) {
        $optionData = null;

        if ($this->_rowIsMain) {
            $optionData = $this->_getOptionData($rowData, $this->_rowProductId, $nextOptionId, $this->_rowType);

            if (!$this->_isRowHasSpecificType($this->_rowType)
                && $priceData = $this->_getPriceData($rowData, $nextOptionId, $this->_rowType)) {
                $prices[$nextOptionId] = $priceData;
            }

            if (!isset($products[$this->_rowProductId])) {
                $products[$this->_rowProductId] = $this->_getProductData($rowData, $this->_rowProductId);
            }

            $prevOptionId = $nextOptionId++;
        }

        return $optionData;
    }

    /**
     * Collect custom option type data to import
     *
     * @param array $rowData
     * @param int $prevOptionId
     * @param int $nextValueId
     * @param array $typeValues
     * @param array $typePrices
     * @param array $typeTitles
     */
    protected function _collectOptionTypeData(array $rowData, &$prevOptionId, &$nextValueId, array &$typeValues,
        array &$typePrices, array &$typeTitles
    ) {
        if ($this->_isRowHasSpecificType($this->_rowType) && $prevOptionId) {
            $specificTypeData = $this->_getSpecificTypeData($rowData, $nextValueId);
            if ($specificTypeData) {
                $typeValues[$prevOptionId][] = $specificTypeData['value'];

                // ensure default title is set
                if (!isset($typeTitles[$nextValueId][Mage_Core_Model_App::ADMIN_STORE_ID])) {
                    $typeTitles[$nextValueId][Mage_Core_Model_App::ADMIN_STORE_ID] = $specificTypeData['title'];
                }
                $typeTitles[$nextValueId][$this->_rowStoreId] = $specificTypeData['title'];;

                if ($specificTypeData['price']) {
                    if ($this->_isPriceGlobal) {
                        $typePrices[$nextValueId][Mage_Core_Model_App::ADMIN_STORE_ID] = $specificTypeData['price'];
                    } else {
                        // ensure default price is set
                        if (!isset($typePrices[$nextValueId][Mage_Core_Model_App::ADMIN_STORE_ID])) {
                            $typePrices[$nextValueId][Mage_Core_Model_App::ADMIN_STORE_ID] = $specificTypeData['price'];
                        }
                        $typePrices[$nextValueId][$this->_rowStoreId] = $specificTypeData['price'];
                    }
                }

                $nextValueId++;
            }
        }
    }

    /**
     * Collect custom option title to import
     *
     * @param array $rowData
     * @param int $prevOptionId
     * @param array $titles
     */
    protected function _collectOptionTitle(array $rowData, $prevOptionId, array &$titles)
    {
        if (!empty($rowData[self::COLUMN_TITLE])) {
            if (!isset($titles[$prevOptionId][Mage_Core_Model_App::ADMIN_STORE_ID])) { // ensure default title is set
                $titles[$prevOptionId][Mage_Core_Model_App::ADMIN_STORE_ID] = $rowData[self::COLUMN_TITLE];
            }
            $titles[$prevOptionId][$this->_rowStoreId] = $rowData[self::COLUMN_TITLE];
        }
    }

    /**
     * Find duplicated custom options and update existing options data
     *
     * @param array $options
     * @param array $titles
     * @param array $prices
     * @param array $typeValues
     * @return Mage_ImportExport_Model_Import_Entity_Product_Option
     */
    protected function _compareOptionsWithExisting(array &$options, array &$titles, array &$prices, array &$typeValues)
    {
        foreach ($options as &$optionData) {
            $newOptionId = $optionData['option_id'];
            if ($optionId = $this->_findExistingOptionId($optionData, $titles[$newOptionId])) {
                $optionData['option_id'] = $optionId;
                $titles[$optionId] = $titles[$newOptionId];
                unset($titles[$newOptionId]);
                if (isset($prices[$newOptionId])) {
                    $prices[$newOptionId]['option_id'] = $optionId;
                }
                if (isset($typeValues[$newOptionId])) {
                    $typeValues[$optionId] = $typeValues[$newOptionId];
                    unset($typeValues[$newOptionId]);
                }
            }
        }

        return $this;
    }

    /**
     * Parse required data from current row and store to class internal variables some data
     * for underlying dependent rows
     *
     * @param array $rowData
     * @return bool
     */
    protected function _parseRequiredData(array $rowData)
    {
        if ($rowData[self::COLUMN_SKU] != '') {
            $this->_rowProductId = $this->_productsSkuToId[$rowData[self::COLUMN_SKU]];
        } elseif (!isset($this->_rowProductId)) {
            return false;
        }

        // Init store
        if (!empty($rowData[self::COLUMN_STORE])) {
            if (!isset($this->_storeCodeToId[$rowData[self::COLUMN_STORE]])) {
                return false;
            }
            $this->_rowStoreId = $this->_storeCodeToId[$rowData[self::COLUMN_STORE]];
        } else {
            $this->_rowStoreId = Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID;
        }
        // Init option type and set param which tell that row is main
        if (!empty($rowData[self::COLUMN_TYPE])) { // get custom option type if its specified
            if (!isset($this->_specificTypes[$rowData[self::COLUMN_TYPE]])) {
                $this->_rowType = null;
                return false;
            }
            $this->_rowType = $rowData[self::COLUMN_TYPE];
            $this->_rowIsMain = true;
        } else {
            if (null === $this->_rowType) {
                return false;
            }
            $this->_rowIsMain = false;
        }

        return array(
            $this->_rowProductId,
            $this->_rowStoreId,
            $this->_rowType,
            $this->_rowIsMain
        );
    }

    /**
     * Checks that current row has specific type
     *
     * @param string $type
     * @return bool
     */
    protected function _isRowHasSpecificType($type)
    {
        if (isset($this->_specificTypes[$type])) {
            return $this->_specificTypes[$type] === true;
        }

        return false;
    }

    /**
     * Retrieve product data for future update
     *
     * @param array $rowData
     * @param int $productId
     * @return array
     */
    protected function _getProductData(array $rowData, $productId)
    {
        $productData = array(
            'entity_id'        => $productId,
            'has_options'      => 1,
            'required_options' => 0,
            'updated_at'       => Varien_Date::now(),
        );

        if (!empty($rowData[self::COLUMN_IS_REQUIRED])) {
            $productData['required_options'] = 1;
        }

        return $productData;
    }

    /**
     * Retrieve option data
     *
     * @param array $rowData
     * @param int $productId
     * @param int $optionId
     * @param string $type
     * @return array
     */
    protected function _getOptionData(array $rowData, $productId, $optionId, $type)
    {
        $optionData = array(
            'option_id'      => $optionId,
            'sku'            => '',
            'max_characters' => 0,
            'file_extension' => null,
            'image_size_x'   => 0,
            'image_size_y'   => 0,
            'product_id'     => $productId,
            'type'           => $type,
            'is_require'     => empty($rowData[self::COLUMN_IS_REQUIRED]) ? 0 : 1,
            'sort_order'     => empty($rowData[self::COLUMN_SORT_ORDER]) ? 0 : abs($rowData[self::COLUMN_SORT_ORDER])
        );

        if (!$this->_isRowHasSpecificType($type)) { // simple option may have optional params
            foreach ($this->_specificTypes[$type] as $paramSuffix) {
                if (isset($rowData[self::COLUMN_PREFIX . $paramSuffix])) {
                    $data = $rowData[self::COLUMN_PREFIX . $paramSuffix];

                    if (array_key_exists($paramSuffix, $optionData)) {
                        $optionData[$paramSuffix] = $data;
                    }
                }
            }
        }
        return $optionData;
    }

    /**
     * Retrieve price data or false in case when price is empty
     *
     * @param array $rowData
     * @param int $optionId
     * @param string $type
     * @return array|bool
     */
    protected function _getPriceData(array $rowData, $optionId, $type)
    {
        if (in_array('price', $this->_specificTypes[$type]) && isset($rowData[self::COLUMN_PREFIX . 'price'])
            && strlen($rowData[self::COLUMN_PREFIX . 'price']) > 0) {
            $priceData = array(
                'option_id'  => $optionId,
                'store_id'   => Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID,
                'price_type' => 'fixed'
            );

            $data = $rowData[self::COLUMN_PREFIX . 'price'];
            if ('%' == substr($data, -1)) {
                $priceData['price_type'] = 'percent';
            }
            $priceData['price'] = (float) rtrim($data, '%');

            return $priceData;
        }

        return false;
    }

    /**
     * Retrieve specific type data
     *
     * @param array $rowData
     * @param int $optionTypeId
     * @return array|bool
     */
    protected function _getSpecificTypeData(array $rowData, $optionTypeId)
    {
        if (!empty($rowData[self::COLUMN_ROW_TITLE]) && empty($rowData[self::COLUMN_STORE])) {
            $valueData = array(
                'option_type_id' => $optionTypeId,
                'sort_order'     => empty($rowData[self::COLUMN_ROW_SORT]) ? 0 : abs($rowData[self::COLUMN_ROW_SORT]),
                'sku'            => !empty($rowData[self::COLUMN_ROW_SKU]) ? $rowData[self::COLUMN_ROW_SKU] : ''
            );

            $priceData = false;
            if (!empty($rowData[self::COLUMN_ROW_PRICE])) {
                $priceData = array(
                    'price'      => (float) rtrim($rowData[self::COLUMN_ROW_PRICE], '%'),
                    'price_type' => 'fixed'
                );
                if ('%' == substr($rowData[self::COLUMN_ROW_PRICE], -1)) {
                    $priceData['price_type'] = 'percent';
                }
            }

            return array(
                'value' => $valueData,
                'title' => $rowData[self::COLUMN_ROW_TITLE],
                'price' => $priceData
            );
        }

        return false;
    }

    /**
     * Delete custom options for products
     *
     * @param array $productIds
     * @return Mage_ImportExport_Model_Import_Entity_Product_Option
     */
    protected function _deleteEntities(array $productIds)
    {
        $this->_connection->delete($this->_tables['catalog_product_option'],
            $this->_connection->quoteInto('product_id IN (?)', $productIds)
        );

        return $this;
    }

    /**
     * Delete custom option type values
     *
     * @param array $optionIds
     * @return Mage_ImportExport_Model_Import_Entity_Product_Option
     */
    protected function _deleteSpecificTypeValues(array $optionIds)
    {
        $this->_connection->delete($this->_tables['catalog_product_option_type_value'],
            $this->_connection->quoteInto('option_id IN (?)', $optionIds)
        );

        return $this;
    }

    /**
     * Save custom options main info
     *
     * @param array $options options data
     * @return Mage_ImportExport_Model_Import_Entity_Product_Option
     */
    protected function _saveOptions(array $options)
    {
        $this->_connection->insertOnDuplicate($this->_tables['catalog_product_option'], $options);

        return $this;
    }

    /**
     * Save custom option titles
     *
     * @param array $titles option titles data
     * @return Mage_ImportExport_Model_Import_Entity_Product_Option
     */
    protected function _saveTitles(array $titles)
    {
        $titleRows = array();
        foreach ($titles as $optionId => $storeInfo) {
            foreach ($storeInfo as $storeId => $title) {
                $titleRows[] = array(
                    'option_id' => $optionId,
                    'store_id'  => $storeId,
                    'title'     => $title
                );
            }
        }
        if ($titleRows) {
            $this->_connection->insertOnDuplicate($this->_tables['catalog_product_option_title'],
                $titleRows,
                array('title')
            );
        }

        return $this;
    }

    /**
     * Save custom option prices
     *
     * @param array $prices option prices data
     * @return Mage_ImportExport_Model_Import_Entity_Product_Option
     */
    protected function _savePrices(array $prices)
    {
        if ($prices) {
            $this->_connection->insertOnDuplicate($this->_tables['catalog_product_option_price'],
                $prices,
                array('price', 'price_type')
            );
        }

        return $this;
    }

    /**
     * Save custom option type values
     *
     * @param array $typeValues option type values
     * @return Mage_ImportExport_Model_Import_Entity_Product_Option
     */
    protected function _saveSpecificTypeValues(array $typeValues)
    {
        $this->_deleteSpecificTypeValues(array_keys($typeValues));

        $typeValueRows = array();
        foreach ($typeValues as $optionId => $optionInfo) {
            foreach ($optionInfo as $row) {
                $row['option_id'] = $optionId;
                $typeValueRows[]  = $row;
            }
        }
        if ($typeValueRows) {
            $this->_connection->insertMultiple($this->_tables['catalog_product_option_type_value'],
                $typeValueRows
            );
        }

        return $this;
    }

    /**
     * Save custom option type prices
     *
     * @param array $typePrices option type prices
     * @return Mage_ImportExport_Model_Import_Entity_Product_Option
     */
    protected function _saveSpecificTypePrices(array $typePrices)
    {
        $optionTypePriceRows = array();
        foreach ($typePrices as $optionTypeId => $storesData) {
            foreach ($storesData as $storeId => $row) {
                $row['option_type_id'] = $optionTypeId;
                $row['store_id']       = $storeId;
                $optionTypePriceRows[] = $row;
            }
        }
        if ($optionTypePriceRows) {
            $this->_connection->insertOnDuplicate($this->_tables['catalog_product_option_type_price'],
                $optionTypePriceRows,
                array('price', 'price_type')
            );
        }

        return $this;
    }

    /**
     * Save custom option type titles
     *
     * @param array $typeTitles option type titles
     * @return Mage_ImportExport_Model_Import_Entity_Product_Option
     */
    protected function _saveSpecificTypeTitles(array $typeTitles)
    {
        $optionTypeTitleRows = array();
        foreach ($typeTitles as $optionTypeId => $storesData) {
            foreach ($storesData as $storeId => $title) {
                $optionTypeTitleRows[] = array(
                    'option_type_id' => $optionTypeId,
                    'store_id'       => $storeId,
                    'title'          => $title
                );
            }
        }
        if ($optionTypeTitleRows) {
            $this->_connection->insertOnDuplicate($this->_tables['catalog_product_option_type_title'],
                $optionTypeTitleRows,
                array('title')
            );
        }

        return $this;
    }

    /**
     * Update product data which related to custom options information
     *
     * @param array $data product data which will be updated
     * @return Mage_ImportExport_Model_Import_Entity_Product_Option
     */
    protected function _updateProducts(array $data)
    {
        if ($data) {
            $this->_connection->insertOnDuplicate($this->_tables['catalog_product_entity'],
                $data,
                array('has_options', 'required_options', 'updated_at')
            );
        }

        return $this;
    }
}
