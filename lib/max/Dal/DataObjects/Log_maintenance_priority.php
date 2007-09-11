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

/**
 * Table Definition for log_maintenance_priority
 */
require_once 'DB_DataObjectCommon.php';

class DataObjects_Log_maintenance_priority extends DB_DataObjectCommon
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    var $__table = 'log_maintenance_priority';        // table name
    var $log_maintenance_priority_id;     // int(11)  not_null primary_key auto_increment
    var $start_run;                       // datetime(19)  not_null binary
    var $end_run;                         // datetime(19)  not_null binary
    var $operation_interval;              // int(11)  not_null
    var $duration;                        // int(11)  not_null
    var $run_type;                        // int(3)  not_null unsigned
    var $updated_to;                      // datetime(19)  binary

    /* ZE2 compatibility trick*/
    function __clone() { return $this;}

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('DataObjects_Log_maintenance_priority',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
}

?>