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
 * @package     Mage_DesignEditor
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Background image renderer test
 */
class Mage_DesignEditor_Model_Editor_Tools_QuickStyles_Renderer_BackgroundImageTest
    extends PHPUnit_Framework_TestCase
{
    /**
     * @cover Mage_DesignEditor_Model_Editor_Tools_QuickStyles_Renderer_BackgroundImage::toCss
     * @dataProvider backgroundImageData
     */
    public function testToCss($expectedResult, $data)
    {
        /** @var $rendererModel Mage_DesignEditor_Model_Editor_Tools_QuickStyles_Renderer_BackgroundImage */
        $rendererModel = $this->getMock(
            'Mage_DesignEditor_Model_Editor_Tools_QuickStyles_Renderer_BackgroundImage', null, array(), '', false
        );

        $this->assertEquals($expectedResult, $rendererModel->toCss($data));
    }

    /**
     * @return array
     */
    public function backgroundImageData()
    {
        return array(array(
            'expected_result' => ".header { background-image: url('path/image.gif'); }",
            'data'            => array(
                'type'      => 'image-uploader',
                'default'   => 'bg.gif',
                'selector'  => '.header',
                'attribute' => 'background-image',
                'value'     => 'path/image.gif',
            ),
        ));
    }
}
