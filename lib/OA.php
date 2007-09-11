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

require_once 'Log.php';
require_once 'PEAR.php';
/**
 * this is a method to capture select queries and write them to a logfile
 * for analysis purposes
 * to trigger set $GLOBALS['_MAX']['CONF']['debug']['logSQL'] = 1
 *
 * @param mdb2 connecction $oDbh
 */
function logSQL($oDbh)
{
    if (substr_count($oDbh->last_query, 'EXPLAIN')==0)
    {
        $i = strpos($oDbh->last_query, 'SELECT');
        if ($i === false)
        {
            return;
        }
        else if ($i === 0)
        {
            $select = $oDbh->last_query;
        }
        else if ($i > 0)
        {
            $select = substr($oDbh->last_query,$i, strlen($oDbh->last_query));
        }
        $log = fopen(MAX_PATH."/var/sql.log", 'a');
        fwrite($log, '['.date('Y-m-d h:i:s').'] <<'.$select.">>\n");
        fclose($log);
    }
}


/**
 * The core Openads class, providing handy methods that are useful everywhere!
 *
 * @package    Openads
 * @author     Andrew Hill <andrew.hill@openads.org>
 */
class OA
{

    /**
     * A method to log debugging messages to the location configured by the user.
     *
     * @static
     * @param mixed $message     Either a string or a PEAR_Error object.
     * @param integer $priority  The priority of the message. One of:
     *                           PEAR_LOG_EMERG, PEAR_LOG_ALERT, PEAR_LOG_CRIT
     *                           PEAR_LOG_ERR, PEAR_LOG_WARNING, PEAR_LOG_NOTICE
     *                           PEAR_LOG_INFO, PEAR_LOG_DEBUG
     * @return boolean           True on success or false on failure.
     *
     * @TODO Logging to anything other than a file is probably broken - test!
     */
    function debug($message = null, $priority = PEAR_LOG_INFO)
    {
        $aConf = $GLOBALS['_MAX']['CONF'];
        global $tempDebugPrefix;
        // Logging is not activated
        if ($aConf['log']['enabled'] == false) {
            unset($GLOBALS['tempDebugPrefix']);
            return true;
        }
        // Is this a "no message" log?
        if (is_null($message) && $aConf['log']['type'] == 'file') {
            // Set the priority to the highest level, so it is always logged
            $priority = PEAR_LOG_EMERG;
        }
        // Deal with the config file containing the log level by
        // name or by number
        $priorityLevel = is_numeric($aConf['log']['priority']) ? $aConf['log']['priority'] :
            @constant($aConf['log']['priority']);
        if (is_null($priorityLevel)) {
            $priorityLevel = $aConf['log']['priority'];
        }
        if ($priority > $priorityLevel) {
            unset($GLOBALS['tempDebugPrefix']);
            return true;
        }
        // Grab DSN if we are logging to a database
        $dsn = ($aConf['log']['type'] == 'sql') ? Base::getDsn() : '';
        // Instantiate a logger object based on logging options
        $aLoggerConf = array(
            $aConf['log']['paramsUsername'],
            $aConf['log']['paramsPassword'],
            'dsn' => $dsn,
            'mode' => octdec($aConf['log']['fileMode']),
        );
        if (is_null($message) && $aConf['log']['type'] == 'file') {
            $aLoggerConf['lineFormat'] = '%4$s';
        } else if ($aConf['log']['type'] == 'file') {
            $aLoggerConf['lineFormat'] = '%1$s %2$s [%3$9s]  %4$s';
        }
        $oLogger = &Log::singleton(
            $aConf['log']['type'],
            MAX_PATH . '/var/' . $aConf['log']['name'],
            $aConf['log']['ident'],
            $aLoggerConf
        );
        // If log message is an error object, extract info
        if (PEAR::isError($message)) {
            $userinfo = $message->getUserInfo();
            $message = $message->getMessage();
            if (!empty($userinfo)) {
                if (is_array($userinfo)) {
                    $userinfo = implode(', ', $userinfo);
                }
            $message .= ' : ' . $userinfo;
            }
        }
        // Obtain backtrace information, if supported by PHP
        if (version_compare(phpversion(), '4.3.0') >= 0) {
            $aBacktrace = debug_backtrace();
            if ($aConf['log']['methodNames']) {
                // Show from four calls up the stack, to avoid the
                // showing the PEAR error call info itself
                $aErrorBacktrace = $aBacktrace[4];
                if (isset($aErrorBacktrace['class']) && $aErrorBacktrace['type'] && isset($aErrorBacktrace['function'])) {
                    $callInfo = $aErrorBacktrace['class'] . $aErrorBacktrace['type'] . $aErrorBacktrace['function'] . ': ';
                    $message = $callInfo . $message;
                }
            }
            // Show entire stack, line-by-line
            if ($aConf['log']['lineNumbers']) {
                foreach($aBacktrace as $aErrorBacktrace) {
                    if (isset($aErrorBacktrace['file']) && isset($aErrorBacktrace['line'])) {
                        $message .=  "\n" . str_repeat(' ', 20 + strlen($aConf['log']['ident']) + strlen($oLogger->priorityToString($priority)));
                        $message .= 'on line ' . $aErrorBacktrace['line'] . ' of "' . $aErrorBacktrace['file'] . '"';
                    }
                }
            }
        }
        // Log the message
        if (is_null($message) && $aConf['log']['type'] == 'file') {
            $message = ' ';
        } else if (!is_null($tempDebugPrefix) && $aConf['log']['type'] == 'file') {
            $message = $tempDebugPrefix . $message;
        }
        $result = $oLogger->log($message, $priority);
        unset($GLOBALS['tempDebugPrefix']);
        return $result;
    }

    /**
     * A method to temporarily set the debug message prefix string. The string
     * is un-set when debug() is called.
     *
     * @param string $prefix The prefix to add to a message logged when the
     *                       debug() method is next called, in the event that
     *                       the logging is to a file.
     */
    function setTempDebugPrefix($prefix)
    {
        global $tempDebugPrefix;
        $tempDebugPrefix = $prefix;
    }


    /**
     * A method to obtain the current date/time, offset if required by the
     * user configured timezone.
     *
     * @static
     * @param string $format A PHP date() compatible formatting string, if
     *                       required. Default is "Y-m-d H:i:s".
     * @return string An appropriately formatted date/time string, representing
     *                the "current" date/time, offset if required.
     */
    function getNow($format = null)
    {
        if (is_null($format)) {
            $format = 'Y-m-d H:i:s';
        }
        return date($format, time());
    }

    /**
     * A method to strip unwanted ending tags from an Openads version string.
     *
     * @static
     * @param string $version The original version string.
     * @param array  $aAllow  An array of allowed tags
     * @return string The stripped version string.
     */
    function stripVersion($version, $aAllow = null)
    {
        $allow = is_null($aAllow) ? '' : '|'.join('|', $aAllow);
        return preg_replace('/^v?(\d+.\d+.\d+(?:-(?:beta(?:-rc\d+)?|rc\d+'.$allow.'))?).*$/', '$1', $version);
    }

    /**
     * A method to temporarily disable PEAR error handling by
     * pushing a null error handler onto the top of the stack.
     *
     * @static
     */
    function disableErrorHandling()
    {
        PEAR::pushErrorHandling(null);
    }

    /**
     * A method to re-enable PEAR error handling by popping
     * a null error handler off the top of the stack.
     *
     * @static
     */
    function enableErrorHandling()
    {
        // Ensure this method only acts when a null error handler exists
        $stack = &$GLOBALS['_PEAR_error_handler_stack'];
        list($mode, $options) = $stack[sizeof($stack) - 1];
        if (is_null($mode) && is_null($options)) {
            PEAR::popErrorHandling();
        }
    }
}

?>