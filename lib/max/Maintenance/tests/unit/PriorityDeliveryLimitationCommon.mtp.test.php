<?php

/*
+---------------------------------------------------------------------------+
| Openads v${RELEASE_MAJOR_MINOR}                                                              |
| ============                                                              |
|                                                                           |
| Copyright (c) 2003-2007 Openads Limited                                   |
| For contact details, see: http://www.openads.org/                         |
|                                                                           |
| This program is free software; you can redistribute it and/or modify      |
| it under the terms of the GNU General Public License as published by      |
| the Free Software Foundation; either version 2 of the License, or         |
| (at your option) any later version.                                       |
|                                                                           |
| This program is distributed in the hope that it will be useful,           |
| but WITHOUT ANY WARRANTY; without even the implied warranty of            |
| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the             |
| GNU General Public License for more details.                              |
|                                                                           |
| You should have received a copy of the GNU General Public License         |
| along with this program; if not, write to the Free Software               |
| Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA |
+---------------------------------------------------------------------------+
$Id$
*/

require_once MAX_PATH . '/lib/max/Maintenance/Priority/DeliveryLimitation/Common.php';
require_once 'Date.php';

/**
 * A class for testing the Maintenance_Priority_DeliveryLimitation_Common class.
 *
 * @package    MaxMaintenance
 * @subpackage TestSuite
 * @author     Andrew Hill <andrew@m3.net>
 * @author     James Floyd <james@m3.net>
 */
class Maintenance_TestOfPriorityDeliveryLimitation_Common extends UnitTestCase
{

    /**
     * The constructor method.
     */
    function Maintenance_TestOfPriorityDeliveryLimitation_Common()
    {
        $this->UnitTestCase();
    }

    /**
     * A method to test the calculateNonDeliveryDeliveryLimitation() method.
     *
     * Tests that the method in the class returns a PEAR::Error, as method is abstract.
     */
    function testCalculateNonDeliveryDeliveryLimitation()
    {
        PEAR::pushErrorHandling(null);
        $this->assertTrue(is_a(MAX_Maintenance_Priority_DeliveryLimitation_Common::calculateNonDeliveryDeliveryLimitation(), 'pear_error'));
        PEAR::popErrorHandling();
    }

    /**
     * A method to test the minutesPerTimePeriod() method.
     *
     * Tests that the method in the class returns a PEAR::Error, as method is abstract.
     */
    function testMinutesPerTimePeriod()
    {
        PEAR::pushErrorHandling(null);
        $this->assertTrue(is_a(MAX_Maintenance_Priority_DeliveryLimitation_Common::minutesPerTimePeriod(), 'pear_error'));
        PEAR::popErrorHandling();
    }

    /**
     * A method to test the deliveryBlocked() method.
     *
     * Tests that the method in the class returns a PEAR::Error, as method is abstract.
     */
    function testDeliveryBlocked()
    {
        $oDate = new Date();
        PEAR::pushErrorHandling(null);
        $this->assertTrue(is_a(MAX_Maintenance_Priority_DeliveryLimitation_Common::deliveryBlocked($oDate), 'pear_error'));
        PEAR::popErrorHandling();
    }

    /**
     * A method to test the _getNonDeliveryOperator() method.
     *
     * Tests all possible valid inputs for the correct response, as well as an invalid input.
     */
    function test_getNonDeliveryOperator()
    {
        $this->assertTrue(MAX_Maintenance_Priority_DeliveryLimitation_Common::_getNonDeliveryOperator('==') == '!=');
        $this->assertTrue(MAX_Maintenance_Priority_DeliveryLimitation_Common::_getNonDeliveryOperator('!=') == '==');
        $this->assertTrue(MAX_Maintenance_Priority_DeliveryLimitation_Common::_getNonDeliveryOperator('<=') == '>');
        $this->assertTrue(MAX_Maintenance_Priority_DeliveryLimitation_Common::_getNonDeliveryOperator('>=') == '<');
        $this->assertTrue(MAX_Maintenance_Priority_DeliveryLimitation_Common::_getNonDeliveryOperator('<') == '>=');
        $this->assertTrue(MAX_Maintenance_Priority_DeliveryLimitation_Common::_getNonDeliveryOperator('>') == '<=');
        PEAR::pushErrorHandling(null);
        $this->assertTrue(is_a(MAX_Maintenance_Priority_DeliveryLimitation_Common::_getNonDeliveryOperator('hello'), 'pear_error'));
        PEAR::popErrorHandling();
    }

}

?>
