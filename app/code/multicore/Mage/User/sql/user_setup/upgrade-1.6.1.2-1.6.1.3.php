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
 * @package     Mage_User
 * @copyright   Copyright (c) 2012 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
/** @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;

$tableName = $installer->getTable('admin_rule');
/** @var Varien_Db_Adapter_Interface $connection */
$connection = $installer->getConnection();

$condition = $connection->prepareSqlCondition('resource_id', array(
    array('like' => '%xmlconnect%'),
    array(
        'in' => array(
            /**
             * Include both old and new identifiers, as depending on install or upgrade process there can be
             * either first or second in the database
             */
            'admin/system/convert/gui',
            'Mage_Adminhtml::gui',
            'admin/system/convert/profiles',
            'Mage_Adminhtml::profiles'
        ),
    ),
));
$connection->delete($tableName, $condition);
