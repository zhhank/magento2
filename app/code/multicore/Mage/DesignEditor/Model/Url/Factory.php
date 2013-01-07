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
 * @package     Mage_Core
 * @copyright   Copyright (c) 2012 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_DesignEditor_Model_Url_Factory implements Magento_ObjectManager_Factory
{
    /**
     * Default url model class name
     */
    const CLASS_NAME = 'Mage_Core_Model_Url';

    /**
     * @var Magento_ObjectManager
     */
    protected $_objectManager;

    /**
     * @param Magento_ObjectManager $objectManager
     */
    public function __construct(Magento_ObjectManager $objectManager)
    {
        $this->_objectManager = $objectManager;
    }

    /**
     * Replace name of url model
     *
     * @param string $className
     * @return Mage_DesignEditor_Model_Url_Factory
     */
    public function replaceClassName($className)
    {
        $this->_objectManager->addAlias(self::CLASS_NAME, $className);

        return $this;
    }

    /**
     * Create url model new instance
     *
     * @param array $arguments
     * @return Mage_Core_Model_Url
     */
    public function createFromArray(array $arguments = array())
    {
        return $this->_objectManager->create(self::CLASS_NAME, $arguments, false);
    }
}
