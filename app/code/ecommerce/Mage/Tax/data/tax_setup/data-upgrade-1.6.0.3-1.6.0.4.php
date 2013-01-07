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
 * @package     Mage_Tax
 * @copyright   Copyright (c) 2012 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/** @var $catalogInstaller Mage_Catalog_Model_Resource_Setup */
$catalogInstaller = Mage::getResourceModel(
    'Mage_Catalog_Model_Resource_Setup',
    array('resourceName' => 'catalog_setup')
);

$entityTypeId = $catalogInstaller->getEntityTypeId(Mage_Catalog_Model_Product::ENTITY);
$attribute = $catalogInstaller->getAttribute(Mage_Catalog_Model_Product::ENTITY, 'tax_class_id');

$catalogInstaller->addAttributeToSet(
    $entityTypeId,
    $catalogInstaller->getAttributeSetId($entityTypeId, 'Minimal'),
    $catalogInstaller->getGeneralGroupName(),
    $attribute['attribute_id']
);
