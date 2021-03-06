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
 * @category    Magento
 * @package     Mage_ImportExport
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Test class for Mage_ImportExport_Model_Source_Import_BehaviorAbstract
 */
class Mage_ImportExport_Model_Source_Import_BehaviorAbstractTest
    extends Mage_ImportExport_Model_Source_Import_BehaviorTestCaseAbstract
{
    /**
     * Source array data
     *
     * @var array
     */
    protected $_sourceArray = array(
        'key_1' => 'label_1',
        'key_2' => 'label_2',
    );

    /**
     * Expected options (without first empty record)
     *
     * @var array
     */
    protected $_expectedOptions = array(
        array(
            'value' => 'key_1',
            'label' => 'label_1',
        ),
        array(
            'value' => 'key_2',
            'label' => 'label_2',
        ),
    );

    public function setUp()
    {
        parent::setUp();

        $model = $this->getMockForAbstractClass(
            'Mage_ImportExport_Model_Source_Import_BehaviorAbstract',
            array(array('helpers' => $this->_helpers)),
            '',
            true,
            true,
            true,
            array('toArray')
        );
        $model->expects($this->any())
            ->method('toArray')
            ->will($this->returnValue($this->_sourceArray));

        $this->_model = $model;
    }

    /**
     * Test for toOptionArray method
     *
     * @covers Mage_ImportExport_Model_Source_Import_BehaviorAbstract::toOptionArray
     */
    public function testToOptionArray()
    {
        $actualOptions = $this->_model->toOptionArray();

        // all elements must have value and label fields
        foreach ($actualOptions as $option) {
            $this->assertArrayHasKey('value', $option);
            $this->assertArrayHasKey('label', $option);
        }

        // first element must has empty value
        $firstElement = $actualOptions[0];
        $this->assertEquals('', $firstElement['value']);

        // other elements must be equal to expected data
        $actualOptions = array_slice($actualOptions, 1);
        $this->assertEquals($this->_expectedOptions, $actualOptions);
    }
}
