<?php
/**
 * Factory of web API dispatchers.
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
 */
class Mage_Webapi_Controller_Dispatcher_Factory
{
    /**
     * List of available web API dispatchers.
     *
     * @var array array({api type} => {API dispatcher class})
     */
    protected $_apiDispatcherMap = array(
        Mage_Webapi_Controller_Front::API_TYPE_REST => 'Mage_Webapi_Controller_Dispatcher_Rest',
        Mage_Webapi_Controller_Front::API_TYPE_SOAP => 'Mage_Webapi_Controller_Dispatcher_Soap',
    );

    /**
     * @var Magento_ObjectManager
     */
    protected $_objectManager;

    /**
     * Initialize dependencies.
     *
     * @param Magento_ObjectManager $objectManager
     */
    public function __construct(Magento_ObjectManager $objectManager)
    {
        $this->_objectManager = $objectManager;
    }

    /**
     * Create front controller instance.
     *
     * Use current API type to define proper request class.
     *
     * @param string $apiType
     * @return Mage_Webapi_Controller_DispatcherInterface
     * @throws LogicException If there is no corresponding dispatcher class for current API type.
     */
    public function get($apiType)
    {
        if (!isset($this->_apiDispatcherMap[$apiType])) {
            throw new LogicException(
                sprintf('There is no corresponding dispatcher class for the "%s" API type.', $apiType)
            );
        }
        $dispatcherClass = $this->_apiDispatcherMap[$apiType];
        return $this->_objectManager->get($dispatcherClass);
    }
}
