<?php

/*
+---------------------------------------------------------------------------+
| Max Media Manager v0.3                                                    |
| =================                                                         |
|                                                                           |
| Copyright (c) 2003-2006 m3 Media Services Limited                         |
| For contact details, see: http://www.m3.net/                              |
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

require_once MAX_PATH . '/lib/OA/Dal/Maintenance/Common.php';
require_once 'Date.php';

/**
 * A class for testing the non-DB specific OA_Dal_Maintenance_Common class.
 *
 * @package    OpenadsDal
 * @subpackage TestSuite
 * @author     Andrew Hill <andrew.hill@openads.net>
 */
class Test_OA_Dal_Maintenance_Common extends UnitTestCase
{

    /**
     * The constructor method.
     */
    function Test_OA_Dal_Maintenance_Common()
    {
        $this->UnitTestCase();
    }

    /**
     * A method to test the setProcessLastRunInfo() method.
     *
     * Requirements:
     * Test 1: Test with invalid data, and ensure false is returned.
     * Test 2: Test that basic information is logged correctly.
     * Test 3: Test that bad table and column names return false.
     * Test 4: Test that run type information is logged correctly.
     */
    function testSetProcessLastRunInfo()
    {
        $conf = $GLOBALS['_MAX']['CONF'];
        $oDbh = &OA_DB::singleton();

        $oStartDate    = new Date('2006-10-05 12:07:01');
        $oEndDate      = new Date('2006-10-05 12:15:00');
        $oUpdateToDate = new Date('2006-10-05 11:59:59');
        $log_maintenance_priority = $conf['table']['prefix'] . $conf['table']['log_maintenance_priority'];

        $oOADalMaintenanceCommon = new OA_Dal_Maintenance_Common();

        // Test 1
        $result = $oOADalMaintenanceCommon->setProcessLastRunInfo(
            null,
            $oEndDate,
            $oUpdateToDate,
            $log_maintenance_priority,
            true
        );
        $this->assertFalse($result);
        $result = $oOADalMaintenanceCommon->setProcessLastRunInfo(
            $oStartDate,
            null,
            $oUpdateToDate,
            $tableName,
            true
        );
        $this->assertFalse($result);
        $result = $oOADalMaintenanceCommon->setProcessLastRunInfo(
            $oStartDate,
            $oEndDate,
            'foo',
            $log_maintenance_priority,
            true
        );
        $this->assertFalse($result);
        $this->assertFalse($result);
        $result = $oOADalMaintenanceCommon->setProcessLastRunInfo(
            $oStartDate,
            $oEndDate,
            $oUpdateToDate,
            $log_maintenance_priority,
            17
        );
        $this->assertFalse($result);

        // Test 2
        $result = $oOADalMaintenanceCommon->setProcessLastRunInfo(
            $oStartDate,
            $oEndDate,
            $oUpdateToDate,
            $log_maintenance_priority,
            true
        );
        $this->assertTrue($result);
        $query = "
            SELECT
                log_maintenance_priority_id,
                start_run,
                end_run,
                operation_interval,
                duration,
                updated_to
            FROM
                $log_maintenance_priority";
        $rc = $oDbh->query($query);
        $aRow = $rc->fetchRow();
        $this->assertEqual($aRow['log_maintenance_priority_id'], 1);
        $this->assertEqual($aRow['start_run'], '2006-10-05 12:07:01');
        $this->assertEqual($aRow['end_run'], '2006-10-05 12:15:00');
        $this->assertEqual($aRow['operation_interval'], $conf['maintenance']['operationInterval']);
        $this->assertEqual($aRow['duration'], (7 * 60) + 59);
        $this->assertEqual($aRow['updated_to'], '2006-10-05 11:59:59');

        // Test 3
        PEAR::pushErrorHandling(null);
        $oDbh = &OA_DB::singleton();
        $oOADalMaintenanceCommon = new OA_Dal_Maintenance_Common();
        $result = $oOADalMaintenanceCommon->setProcessLastRunInfo(
            $oStartDate,
            $oEndDate,
            $oUpdateToDate,
            'foo',
            true
        );
        $this->assertFalse($result);
        $result = $oOADalMaintenanceCommon->setProcessLastRunInfo(
            $oStartDate,
            $oEndDate,
            $oUpdateToDate,
            $log_maintenance_priority,
            true,
            'foo',
            1
        );
        $this->assertFalse($result);
        PEAR::popErrorHandling();

        // Test 4
        $result = $oOADalMaintenanceCommon->setProcessLastRunInfo(
            $oStartDate,
            $oEndDate,
            $oUpdateToDate,
            $log_maintenance_priority,
            true,
            'run_type',
            1
        );
        $this->assertTrue($result);
        $query = "
            SELECT
                log_maintenance_priority_id,
                start_run,
                end_run,
                operation_interval,
                duration,
                updated_to,
                run_type
            FROM
                $log_maintenance_priority
            WHERE
                log_maintenance_priority_id = 1";
        $rc = $oDbh->query($query);
        $aRow = $rc->fetchRow();
        $this->assertEqual($aRow['log_maintenance_priority_id'], 1);
        $this->assertEqual($aRow['start_run'], '2006-10-05 12:07:01');
        $this->assertEqual($aRow['end_run'], '2006-10-05 12:15:00');
        $this->assertEqual($aRow['operation_interval'], $conf['maintenance']['operationInterval']);
        $this->assertEqual($aRow['duration'], (7 * 60) + 59);
        $this->assertEqual($aRow['updated_to'], '2006-10-05 11:59:59');
        $this->assertEqual($aRow['run_type'], 0);
        $query = "
            SELECT
                log_maintenance_priority_id,
                start_run,
                end_run,
                operation_interval,
                duration,
                updated_to,
                run_type
            FROM
                $log_maintenance_priority
            WHERE
                log_maintenance_priority_id = 2";
        $rc = $oDbh->query($query);
        $aRow = $rc->fetchRow();
        $this->assertEqual($aRow['log_maintenance_priority_id'], 2);
        $this->assertEqual($aRow['start_run'], '2006-10-05 12:07:01');
        $this->assertEqual($aRow['end_run'], '2006-10-05 12:15:00');
        $this->assertEqual($aRow['operation_interval'], $conf['maintenance']['operationInterval']);
        $this->assertEqual($aRow['duration'], (7 * 60) + 59);
        $this->assertEqual($aRow['updated_to'], '2006-10-05 11:59:59');
        $this->assertEqual($aRow['run_type'], 1);
        TestEnv::restoreEnv();
    }

    /**
     * A method to test the getProcessLastRunInfo() method.
     *
     * Requirements:
     * Test 1: Test with invalid data, and ensure false is returned.
     * Test 2: Test with no data in the database and ensure null is returned.
     * Test 3: Test with bad table and column names, and ensure false is returned.
     * Test 4: Test that the correct values are returned from data_ tables.
     * Test 5: Test that the correct values are returned from log_ tables.
     */
    function testGetProcessLastRunInfo()
    {
        $conf = &$GLOBALS['_MAX']['CONF'];
        $oDbh = &OA_DB::singleton();

        $log_maintenance_priority = $conf['table']['prefix'] . $conf['table']['log_maintenance_priority'];
        $data_raw_ad_impression = $conf['table']['prefix'] . $conf['table']['data_raw_ad_impression'];

        $oOADalMaintenanceCommon = new OA_Dal_Maintenance_Common();

        // Test 1
        $result = $oOADalMaintenanceCommon->getProcessLastRunInfo(
            $log_maintenance_priority,
            'foo',
            null,
            'start_run',
            array()
        );
        $this->assertFalse($result);
        $result = $oOADalMaintenanceCommon->getProcessLastRunInfo(
            $log_maintenance_priority,
            array(),
            null,
            'start_run',
            'foo'
        );
        $this->assertFalse($result);

        // Test 2
        $result = $oOADalMaintenanceCommon->getProcessLastRunInfo(
            $log_maintenance_priority
        );
        $this->assertNull($result);
        $result = $oOADalMaintenanceCommon->getProcessLastRunInfo(
            $log_maintenance_priority,
            array(),
            null,
            'start_run',
            array(
                'tableName' => $data_raw_ad_impression,
                'type'      => 'hour'
            )
        );
        $this->assertNull($result);

        // Test 3
        PEAR::pushErrorHandling(null);
        $result = $oOADalMaintenanceCommon->getProcessLastRunInfo(
            'foo',
            array(),
            null,
            'start_run',
            array()
        );
        $this->assertFalse($result);
        $result = $oOADalMaintenanceCommon->getProcessLastRunInfo(
            $log_maintenance_priority,
            array('foo'),
            null,
            'start_run',
            array()
        );
        $this->assertFalse($result);
        $result = $oOADalMaintenanceCommon->getProcessLastRunInfo(
            $log_maintenance_priority,
            array(),
            null,
            'start_run',
            array(
                'tableName' => 'foo',
                'type'      => 'hour'
            )
        );
        $this->assertFalse($result);
        PEAR::popErrorHandling();

        // Test 4
        TestEnv::startTransaction();
        $query = "
            INSERT INTO
                $data_raw_ad_impression
                (
                    date_time,
                    ad_id,
                    creative_id,
                    zone_id
                )
            VALUES
                (
                    '2006-10-06 08:53:42',
                    1,
                    1,
                    1
                )";
        $rows = $oDbh->exec($query);
        $aResult = $oOADalMaintenanceCommon->getProcessLastRunInfo(
            $log_maintenance_priority,
            array('operation_interval'),
            null,
            'start_run',
            array(
                'tableName' => $data_raw_ad_impression,
                'type'      => 'hour'
            )
        );
        $this->assertTrue(is_array($aResult));
        $this->assertEqual(count($aResult), 1);
        $this->assertEqual($aResult['updated_to'], '2006-10-06 07:59:59');
        $query = "
            INSERT INTO
                $data_raw_ad_impression
                (
                    date_time,
                    ad_id,
                    creative_id,
                    zone_id
                )
            VALUES
                (
                    '2006-10-06 09:53:42',
                    1,
                    1,
                    1
                )";
        $rows = $oDbh->exec($query);
        $aResult = $oOADalMaintenanceCommon->getProcessLastRunInfo(
            $log_maintenance_priority,
            array('operation_interval'),
            null,
            'start_run',
            array(
                'tableName' => $data_raw_ad_impression,
                'type'      => 'hour'
            )
        );
        $this->assertTrue(is_array($aResult));
        $this->assertEqual(count($aResult), 1);
        $this->assertEqual($aResult['updated_to'], '2006-10-06 07:59:59');

        $conf['maintenance']['operationInterval'] = 60;
        $aResult = $oOADalMaintenanceCommon->getProcessLastRunInfo(
            $log_maintenance_priority,
            array('operation_interval'),
            null,
            'start_run',
            array(
                'tableName' => $data_raw_ad_impression,
                'type'      => 'hour'
            )
        );
        $this->assertTrue(is_array($aResult));
        $this->assertEqual(count($aResult), 1);
        $this->assertEqual($aResult['updated_to'], '2006-10-06 07:59:59');
        $aResult = $oOADalMaintenanceCommon->getProcessLastRunInfo(
            $log_maintenance_priority,
            array('operation_interval'),
            null,
            'start_run',
            array(
                'tableName' => $data_raw_ad_impression,
                'type'      => 'oi'
            )
        );
        $this->assertTrue(is_array($aResult));
        $this->assertEqual(count($aResult), 1);
        $this->assertEqual($aResult['updated_to'], '2006-10-06 07:59:59');

        $conf['maintenance']['operationInterval'] = 30;
        $aResult = $oOADalMaintenanceCommon->getProcessLastRunInfo(
            $log_maintenance_priority,
            array('operation_interval'),
            null,
            'start_run',
            array(
                'tableName' => $data_raw_ad_impression,
                'type'      => 'hour'
            )
        );
        $this->assertTrue(is_array($aResult));
        $this->assertEqual(count($aResult), 1);
        $this->assertEqual($aResult['updated_to'], '2006-10-06 07:59:59');
        $aResult = $oOADalMaintenanceCommon->getProcessLastRunInfo(
            $log_maintenance_priority,
            array('operation_interval'),
            null,
            'start_run',
            array(
                'tableName' => $data_raw_ad_impression,
                'type'      => 'oi'
            )
        );
        $this->assertTrue(is_array($aResult));
        $this->assertEqual(count($aResult), 1);
        $this->assertEqual($aResult['updated_to'], '2006-10-06 08:29:59');
        TestEnv::restoreConfig();
        TestEnv::rollbackTransaction();

        // Test 5
        TestEnv::startTransaction();
        $query = "
            INSERT INTO
                $log_maintenance_priority
                (
                    start_run,
                    end_run,
                    operation_interval,
                    duration,
                    run_type,
                    updated_to
                )
            VALUES
                (
                    '2006-10-06 12:07:01',
                    '2006-10-06 12:10:01',
                    60,
                    180,
                    1,
                    '2006-10-06 11:59:59'
                )";
        $rows = $oDbh->exec($query);
        $aResult = $oOADalMaintenanceCommon->getProcessLastRunInfo(
            $log_maintenance_priority,
            array('operation_interval', 'run_type'),
            null,
            'start_run',
            array(
                'tableName' => $data_raw_ad_impression,
                'type'      => 'hour'
            )
        );
        $this->assertTrue(is_array($aResult));
        $this->assertEqual(count($aResult), 3);
        $this->assertEqual($aResult['updated_to'], '2006-10-06 11:59:59');
        $this->assertEqual($aResult['operation_interval'], 60);
        $this->assertEqual($aResult['run_type'], 1);
        $query = "
            INSERT INTO
                $log_maintenance_priority
                (
                    start_run,
                    end_run,
                    operation_interval,
                    duration,
                    run_type,
                    updated_to
                )
            VALUES
                (
                    '2006-10-06 11:07:01',
                    '2006-10-06 11:10:01',
                    60,
                    180,
                    0,
                    '2006-10-06 20:59:59'
                )";
        $rows = $oDbh->exec($query);
        $aResult = $oOADalMaintenanceCommon->getProcessLastRunInfo(
            $log_maintenance_priority,
            array('operation_interval', 'run_type'),
            null,
            'start_run',
            array(
                'tableName' => $data_raw_ad_impression,
                'type'      => 'hour'
            )
        );
        $this->assertTrue(is_array($aResult));
        $this->assertEqual(count($aResult), 3);
        $this->assertEqual($aResult['updated_to'], '2006-10-06 11:59:59');
        $this->assertEqual($aResult['operation_interval'], 60);
        $this->assertEqual($aResult['run_type'], 1);
        $aResult = $oOADalMaintenanceCommon->getProcessLastRunInfo(
            $log_maintenance_priority,
            array('operation_interval', 'run_type'),
            null,
            'updated_to',
            array(
                'tableName' => $data_raw_ad_impression,
                'type'      => 'hour'
            )
        );
        $this->assertTrue(is_array($aResult));
        $this->assertEqual(count($aResult), 3);
        $this->assertEqual($aResult['updated_to'], '2006-10-06 20:59:59');
        $this->assertEqual($aResult['operation_interval'], 60);
        $this->assertEqual($aResult['run_type'], 0);
        $aResult = $oOADalMaintenanceCommon->getProcessLastRunInfo(
            $log_maintenance_priority,
            array('operation_interval', 'run_type'),
            'WHERE run_type = 0',
            'start_run',
            array(
                'tableName' => $data_raw_ad_impression,
                'type'      => 'hour'
            )
        );
        $this->assertTrue(is_array($aResult));
        $this->assertEqual(count($aResult), 3);
        $this->assertEqual($aResult['updated_to'], '2006-10-06 20:59:59');
        $this->assertEqual($aResult['operation_interval'], 60);
        $this->assertEqual($aResult['run_type'], 0);
        TestEnv::rollbackTransaction();
    }

    /**
     * A method to test the getMaintenanceStatisticsLastRunInfo method.
     *
     * @TODO Not implemented.
     */
    function testGetMaintenanceStatisticsLastRunInfo() {}

    /**
     * A method to test the getAllDeliveryLimitationsByTypeId() method.
     *
     * Requirements:
     * Test 1:  Test for ad limitations with no data, and ensure null returned
     * Test 2:  Test for channel limitations with no data, and ensure null returned
     * Test 3:  Test with an ad limitation for an ad, but with a different ad id, and
     *          ensure null returned
     * Test 4:  Test with an ad limitation, but with a channel id, and ensure null
     *          returned
     * Test 5:  Test with an ad limitation, but with a bad $type, and ensure null
     *          returned
     * Test 6:  Test with an ad limitation, and ensure values returned
     * Test 7:  Test with a channel limitation, but with an ad id, and ensure null
     *          returned
     * Test 8:  Test with a channel limitation, but with a different channel id, and
     *          ensure null returned
     * Test 9:  Test with a channel limitation, but with a bad $type, and ensure null
     *          returned
     * Test 10: Test with a channel limitation, and ensure values returned
     */
    function testGetAllDeliveryLimitationsByTypeId()
    {
        $conf = $GLOBALS['_MAX']['CONF'];
        $oDbh = &OA_DB::singleton();

        $oOADalMaintenanceCommon = new OA_Dal_Maintenance_Common();

        // Test 1
        $aResult = $oOADalMaintenanceCommon->getAllDeliveryLimitationsByTypeId(1, 'ad');
        $this->assertNull($aResult);

        // Test 2
        $aResult = $oOADalMaintenanceCommon->getAllDeliveryLimitationsByTypeId(1, 'channel');
        $this->assertNull($aResult);

        TestEnv::startTransaction();
        $table = $conf['table']['prefix'] . $conf['table']['acls'];
        $query = "
            INSERT INTO
                $table
                (
                    bannerid,
                    logical,
                    type,
                    comparison,
                    data,
                    executionorder
                )
            VALUES
                (
                    3,
                    'and',
                    'Time:Date',
                    '!=',
                    '2005-05-25',
                    0
                ),
                (
                    3,
                    'and',
                    'Geo:Country',
                    '==',
                    'GB',
                    1
                )";
        $rows = $oDbh->exec($query);

        // Test 3
        $aResult = $oOADalMaintenanceCommon->getAllDeliveryLimitationsByTypeId(1, 'ad');
        $this->assertNull($aResult);

        // Test 4
        $aResult = $oOADalMaintenanceCommon->getAllDeliveryLimitationsByTypeId(1, 'channel');
        $this->assertNull($aResult);

        // Test 5
        $aResult = $oOADalMaintenanceCommon->getAllDeliveryLimitationsByTypeId(3, 'foo');
        $this->assertNull($aResult);

        // Test 6
        $aResult = $oOADalMaintenanceCommon->getAllDeliveryLimitationsByTypeId(3, 'ad');
        $this->assertTrue(is_array($aResult));
        $this->assertEqual(count($aResult), 2);
        $this->assertEqual(count($aResult[0]), 6);
        $this->assertEqual($aResult[0]['ad_id'], 3);
        $this->assertEqual($aResult[0]['logical'], 'and');
        $this->assertEqual($aResult[0]['type'], 'Time:Date');
        $this->assertEqual($aResult[0]['comparison'], '!=');
        $this->assertEqual($aResult[0]['data'], '2005-05-25');
        $this->assertEqual($aResult[0]['executionorder'], 0);
        $this->assertEqual(count($aResult[1]), 6);
        $this->assertEqual($aResult[1]['ad_id'], 3);
        $this->assertEqual($aResult[1]['logical'], 'and');
        $this->assertEqual($aResult[1]['type'], 'Geo:Country');
        $this->assertEqual($aResult[1]['comparison'], '==');
        $this->assertEqual($aResult[1]['data'], 'GB');
        $this->assertEqual($aResult[1]['executionorder'], 1);

        TestEnv::rollbackTransaction();

        TestEnv::startTransaction();
        $table = $conf['table']['prefix'] . $conf['table']['acls_channel'];
        $query = "
            INSERT INTO
                $table
                (
                    channelid,
                    logical,
                    type,
                    comparison,
                    data,
                    executionorder
                )
            VALUES
                (
                    3,
                    'and',
                    'Time:Date',
                    '!=',
                    '2005-05-25',
                    0
                ),
                (
                    3,
                    'and',
                    'Geo:Country',
                    '==',
                    'GB',
                    1
                )";
        $rows = $oDbh->exec($query);

        // Test 7
        $aResult = $oOADalMaintenanceCommon->getAllDeliveryLimitationsByTypeId(1, 'ad');
        $this->assertNull($aResult);

        // Test 8
        $aResult = $oOADalMaintenanceCommon->getAllDeliveryLimitationsByTypeId(1, 'channel');
        $this->assertNull($aResult);

        // Test 9
        $aResult = $oOADalMaintenanceCommon->getAllDeliveryLimitationsByTypeId(3, 'foo');
        $this->assertNull($aResult);

        // Test 10
        $aResult = $oOADalMaintenanceCommon->getAllDeliveryLimitationsByTypeId(3, 'channel');
        $this->assertTrue(is_array($aResult));
        $this->assertEqual(count($aResult), 2);
        $this->assertEqual(count($aResult[0]), 6);
        $this->assertEqual($aResult[0]['ad_id'], 3);
        $this->assertEqual($aResult[0]['logical'], 'and');
        $this->assertEqual($aResult[0]['type'], 'Time:Date');
        $this->assertEqual($aResult[0]['comparison'], '!=');
        $this->assertEqual($aResult[0]['data'], '2005-05-25');
        $this->assertEqual($aResult[0]['executionorder'], 0);
        $this->assertEqual(count($aResult[1]), 6);
        $this->assertEqual($aResult[1]['ad_id'], 3);
        $this->assertEqual($aResult[1]['logical'], 'and');
        $this->assertEqual($aResult[1]['type'], 'Geo:Country');
        $this->assertEqual($aResult[1]['comparison'], '==');
        $this->assertEqual($aResult[1]['data'], 'GB');
        $this->assertEqual($aResult[1]['executionorder'], 1);

        TestEnv::rollbackTransaction();
    }

    /**
     * A method to test the maxConnectionWindow() method.
     *
     * Requirements:
     * Test 1: Test without data in the table, ensure a value of zero returned
     * Test 2: Test with data in the table, but windows of zero, ensure a
     *         value of zero returned
     * Test 3: Test with data in the table, only the impression window > 0,
     *         ensure correct values returned.
     * Test 4: Test with data in the table, ensure the correct value returned
     */
    function testMaxConnectionWindow()
    {
        $conf = $GLOBALS['_MAX']['CONF'];
        $oDbh = &OA_DB::singleton();

        $oOADalMaintenanceCommon = new OA_Dal_Maintenance_Common();

        // Test 1
        $max = $oOADalMaintenanceCommon->maxConnectionWindow('impression');
        $this->assertEqual($max, 0);
        $max = $oOADalMaintenanceCommon->maxConnectionWindow('click');
        $this->assertEqual($max, 0);

        TestEnv::startTransaction();

        // Test 2
        $query = "
            INSERT INTO
                {$conf['table']['prefix']}{$conf['table']['campaigns_trackers']}
                (
                    viewwindow,
                    clickwindow
                )
            VALUES
                (
                    0,
                    0
                )";
        $rows = $oDbh->exec($query);
        $max = $oOADalMaintenanceCommon->maxConnectionWindow('impression');
        $this->assertEqual($max, 0);
        $max = $oOADalMaintenanceCommon->maxConnectionWindow('click');
        $this->assertEqual($max, 0);

        // Test 3
        $query = "
            INSERT INTO
                {$conf['table']['prefix']}{$conf['table']['campaigns_trackers']}
                (
                    viewwindow,
                    clickwindow
                )
            VALUES
                (
                    60,
                    0
                )";
        $rows = $oDbh->exec($query);
        $max = $oOADalMaintenanceCommon->maxConnectionWindow('impression');
        $this->assertEqual($max, 60);
        $max = $oOADalMaintenanceCommon->maxConnectionWindow('click');
        $this->assertEqual($max, 0);

        // Test 4
        $query = "
            INSERT INTO
                {$conf['table']['prefix']}{$conf['table']['campaigns_trackers']}
                (
                    viewwindow,
                    clickwindow
                )
            VALUES
                (
                    180,
                    70
                )";
        $rows = $oDbh->exec($query);
        $max = $oOADalMaintenanceCommon->maxConnectionWindow('impression');
        $this->assertEqual($max, 180);
        $max = $oOADalMaintenanceCommon->maxConnectionWindow('click');
        $this->assertEqual($max, 70);

        TestEnv::rollbackTransaction();
    }

    /**
     * A method to test the maxConnectionWindows() method.
     */
    function testMaxConnectionWindows()
    {
        $conf = $GLOBALS['_MAX']['CONF'];
        $oDbh = &OA_DB::singleton();

        $oOADalMaintenanceCommon = new OA_Dal_Maintenance_Common();

        // Test 1
        list($impression, $click) = $oOADalMaintenanceCommon->maxConnectionWindows();
        $this->assertEqual($impression, 0);
        $this->assertEqual($click, 0);

        TestEnv::startTransaction();

        // Test 2
        $query = "
            INSERT INTO
                {$conf['table']['prefix']}{$conf['table']['campaigns_trackers']}
                (
                    viewwindow,
                    clickwindow
                )
            VALUES
                (
                    0,
                    0
                )";
        $rows = $oDbh->exec($query);
        list($impression, $click) = $oOADalMaintenanceCommon->maxConnectionWindows();
        $this->assertEqual($impression, 0);
        $this->assertEqual($click, 0);

        // Test 3
        $query = "
            INSERT INTO
                {$conf['table']['prefix']}{$conf['table']['campaigns_trackers']}
                (
                    viewwindow,
                    clickwindow
                )
            VALUES
                (
                    60,
                    0
                )";
        $rows = $oDbh->exec($query);
        list($impression, $click) = $oOADalMaintenanceCommon->maxConnectionWindows();
        $this->assertEqual($impression, 60);
        $this->assertEqual($click, 0);

        // Test 4
        $query = "
            INSERT INTO
                {$conf['table']['prefix']}{$conf['table']['campaigns_trackers']}
                (
                    viewwindow,
                    clickwindow
                )
            VALUES
                (
                    180,
                    70
                )";
        $rows = $oDbh->exec($query);
        list($impression, $click) = $oOADalMaintenanceCommon->maxConnectionWindows();
        $this->assertEqual($impression, 180);
        $this->assertEqual($click, 70);

        TestEnv::rollbackTransaction();
    }

}

?>
