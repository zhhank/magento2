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
 * Admin product tax class add form
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */

class Mage_Adminhtml_Block_Tax_Rate_Form extends Mage_Adminhtml_Block_Widget_Form
{
    const FORM_ELEMENT_ID = 'rate-form';
    
    protected $_titles = null;

    protected $_template = 'tax/rate/form.phtml';


    protected function _construct()
    {
        parent::_construct();
        $this->setDestElementId(self::FORM_ELEMENT_ID);

    }

    protected function _prepareForm()
    {
        $rateObject = new Varien_Object(Mage::getSingleton('Mage_Tax_Model_Calculation_Rate')->getData());
        $form = new Varien_Data_Form();

        $countries = Mage::getModel('Mage_Directory_Model_Config_Source_Country')->toOptionArray();
        unset($countries[0]);

        if (!$rateObject->hasTaxCountryId()) {
            $rateObject->setTaxCountryId(Mage::getStoreConfig(Mage_Tax_Model_Config::CONFIG_XML_PATH_DEFAULT_COUNTRY));
        }

        if (!$rateObject->hasTaxRegionId()) {
            $rateObject->setTaxRegionId(Mage::getStoreConfig(Mage_Tax_Model_Config::CONFIG_XML_PATH_DEFAULT_REGION));
        }

        $regionCollection = Mage::getModel('Mage_Directory_Model_Region')
            ->getCollection()
            ->addCountryFilter($rateObject->getTaxCountryId());

        $regions = $regionCollection->toOptionArray();
        if ($regions) {
            $regions[0]['label'] = '*';
        } else {
            $regions = array(array('value' => '', 'label' => '*'));
        }

        $fieldset = $form->addFieldset('base_fieldset', array('legend' => Mage::helper('Mage_Tax_Helper_Data')->__('Tax Rate Information')));

        if ($rateObject->getTaxCalculationRateId() > 0) {
            $fieldset->addField('tax_calculation_rate_id', 'hidden', array(
                'name'  => 'tax_calculation_rate_id',
                'value' => $rateObject->getTaxCalculationRateId()
            ));
        }

        $fieldset->addField('code', 'text', array(
            'name'     => 'code',
            'label'    => Mage::helper('Mage_Tax_Helper_Data')->__('Tax Identifier'),
            'title'    => Mage::helper('Mage_Tax_Helper_Data')->__('Tax Identifier'),
            'class'    => 'required-entry',
            'required' => true,
        ));

        $fieldset->addField('tax_country_id', 'select', array(
            'name'     => 'tax_country_id',
            'label'    => Mage::helper('Mage_Tax_Helper_Data')->__('Country'),
            'required' => true,
            'values'   => $countries
        ));

        $fieldset->addField('tax_region_id', 'select', array(
            'name'   => 'tax_region_id',
            'label'  => Mage::helper('Mage_Tax_Helper_Data')->__('State'),
            'values' => $regions
        ));

        $fieldset->addField('zip_is_range', 'select', array(
            'name'    => 'zip_is_range',
            'label'   => Mage::helper('Mage_Tax_Helper_Data')->__('Zip/Post is Range'),
            'options' => array(
                '0' => Mage::helper('Mage_Tax_Helper_Data')->__('No'),
                '1' => Mage::helper('Mage_Tax_Helper_Data')->__('Yes'),
            )
        ));

        if (!$rateObject->hasTaxPostcode()) {
            $rateObject->setTaxPostcode(Mage::getStoreConfig(Mage_Tax_Model_Config::CONFIG_XML_PATH_DEFAULT_POSTCODE));
        }

        $fieldset->addField('tax_postcode', 'text', array(
            'name'  => 'tax_postcode',
            'label' => Mage::helper('Mage_Tax_Helper_Data')->__('Zip/Post Code'),
            'note'  => Mage::helper('Mage_Tax_Helper_Data')->__("'*' - matches any; 'xyz*' - matches any that begins on 'xyz' and not longer than %d.", Mage::helper('Mage_Tax_Helper_Data')->getPostCodeSubStringLength()),
        ));

        $fieldset->addField('zip_from', 'text', array(
            'name'      => 'zip_from',
            'label'     => Mage::helper('Mage_Tax_Helper_Data')->__('Range From'),
            'required'  => true,
            'maxlength' => 9,
            'class'     => 'validate-digits'
        ));

        $fieldset->addField('zip_to', 'text', array(
            'name'      => 'zip_to',
            'label'     => Mage::helper('Mage_Tax_Helper_Data')->__('Range To'),
            'required'  => true,
            'maxlength' => 9,
            'class'     => 'validate-digits'
        ));

        $fieldset->addField('rate', 'text', array(
            'name'     => 'rate',
            'label'    => Mage::helper('Mage_Tax_Helper_Data')->__('Rate Percent'),
            'title'    => Mage::helper('Mage_Tax_Helper_Data')->__('Rate Percent'),
            'required' => true,
            'class'    => 'validate-not-negative-number'
        ));

        $form->setAction($this->getUrl('*/tax_rate/save'));
        $form->setUseContainer(true);
        $form->setId(self::FORM_ELEMENT_ID);
        $form->setMethod('post');

        if (!Mage::app()->hasSingleStore()) {
            $form->addElement(
                Mage::getBlockSingleton('Mage_Adminhtml_Block_Tax_Rate_Title_Fieldset')
                    ->setLegend(Mage::helper('Mage_Tax_Helper_Data')
                    ->__('Tax Titles'))
            );
        }

        $rateData = $rateObject->getData();
        if ($rateObject->getZipIsRange()) {
            list($rateData['zip_from'], $rateData['zip_to']) = explode('-', $rateData['tax_postcode']);
        }
        $form->setValues($rateData);
        $this->setForm($form);

        $this->setChild(
            'form_after',
            $this->getLayout()->createBlock('Mage_Adminhtml_Block_Widget_Form_Element_Dependence')
                ->addFieldMap('zip_is_range', 'zip_is_range')
                ->addFieldMap('tax_postcode', 'tax_postcode')
                ->addFieldMap('zip_from', 'zip_from')
                ->addFieldMap('zip_to', 'zip_to')
                ->addFieldDependence('zip_from', 'zip_is_range', '1')
                ->addFieldDependence('zip_to', 'zip_is_range', '1')
                ->addFieldDependence('tax_postcode', 'zip_is_range', '0')
        );

        return parent::_prepareForm();
    }

    /**
     * Get Tax Rates Collection
     *
     * @return array
     */
    public function getRateCollection()
    {
        if ($this->getData('rate_collection') == null) {
            $rateCollection = Mage::getModel('Mage_Tax_Model_Calculation_Rate')->getCollection()
                ->joinRegionTable();
            $rates = array();

            foreach ($rateCollection as $rate) {
                $item = $rate->getData();
                foreach ($rate->getTitles() as $title) {
                    $item['title[' . $title->getStoreId() . ']'] = $title->getValue();
                }
                $rates[] = $item;
            }

            $this->setRateCollection($rates);
        }
        return $this->getData('rate_collection');
    }
}
