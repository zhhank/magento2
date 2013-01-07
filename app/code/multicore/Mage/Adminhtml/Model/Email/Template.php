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
 * Adminhtml email template model
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author     Magento Core Team <core@magentocommerce.com>
 */

class Mage_Adminhtml_Model_Email_Template extends Mage_Core_Model_Email_Template
{
    /**
     * Collect all system config pathes where current template is used as default
     *
     * @return array
     */
    public function getSystemConfigPathsWhereUsedAsDefault()
    {
        $templateCode = $this->getOrigTemplateCode();
        if (!$templateCode) {
            return array();
        }
        $paths = array();

        // find nodes which are using $templateCode value
        $defaultCfgNodes = Mage::getConfig()->getXpath('default/*/*[*="' . $templateCode . '"]');
        if (!is_array($defaultCfgNodes)) {
            return array();
        }

        foreach ($defaultCfgNodes as $node) {
            // create email template path in system.xml
            $sectionName = $node->getParent()->getName();
            $groupName = $node->getName();
            $fieldName = substr($templateCode, strlen($sectionName . '_' . $groupName . '_'));
            $paths[] = array('path' => implode('/', array($sectionName, $groupName, $fieldName)));
        }
        return $paths;
    }

    /**
     * Collect all system config pathes where current template is currently used
     *
     * @return array
     */
    public function getSystemConfigPathsWhereUsedCurrently()
    {
        $templateId = $this->getId();
        if (!$templateId) {
            return array();
        }

        /** @var Mage_Backend_Model_Config_Structure $configStructure  */
        $configStructure = Mage::getSingleton('Mage_Backend_Model_Config_Structure');
        $templatePaths = $configStructure
            ->getFieldPathsByAttribute('source_model', 'Mage_Backend_Model_Config_Source_Email_Template');

        if (!count($templatePaths)) {
            return array();
        }

        $configData = $this->_getResource()->getSystemConfigByPathsAndTemplateId($templatePaths, $templateId);
        if (!$configData) {
            return array();
        }

        return $configData;
    }
}
