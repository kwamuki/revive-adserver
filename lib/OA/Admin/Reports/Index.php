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
$Id:IndexModule.php 4204 2006-02-10 09:55:36Z roh@m3.net $
*/

// Include required files
require_once MAX_PATH . '/lib/max/Admin/Redirect.php';
require_once MAX_PATH . '/lib/max/Admin/UI/FieldFactory.php';
require_once MAX_PATH . '/lib/max/language/Report.php';
require_once MAX_PATH . '/lib/max/Plugin.php';
require_once MAX_PATH . '/www/admin/config.php';

/**
 * A class for displaying the list of report plugins that a user can run,
 * as well as for displaying the report generation pages for the report
 * plugins.
 *
 * @package    OpenadsAdmin
 * @subpackage Reports
 * @author     Andrew Hill <andrew.hill@openads.org>
 */
class OA_Admin_Reports_Index
{

    /**
     * @var FieldFactory
     */
    var $oFieldFactory;

    /**
     * The constructor method.
     *
     * @return OA_Admin_Reports_Index
     */
    function OA_Admin_Reports_Index()
    {
        $this->oFieldFactory = new FieldFactory();
        $this->tabindex = 1;
    }

    /**
     * A method to display all reports that the user has permissions
     * to run to the UI.
     */
    function displayReports()
    {
        $aDisplayablePlugins = $this->_findDisplayableReports();
        $aGroupedPlugins = $this->_groupReportPlugins($aDisplayablePlugins);
        if (!empty($aDisplayablePlugins)) {
            $this->_displayPluginList($aGroupedPlugins);
        }
    }

    /**
     * A method to display a report's generation screen to the UI.
     *
     * @param string $report_identifier
     */
    function displayReportGeneration($reportIdentifier)
    {
        $aDisplayablePlugins = $this->_findDisplayableReports();
        $oPlugin = $aDisplayablePlugins[$reportIdentifier];
        if (is_null($oPlugin)) {
            // Cannot use this plugin! display the reports instead
            $this->displayReports();
            return;
        }
        $this->_groupReportPluginGeneration($oPlugin, $reportIdentifier);
    }

    /**
     * A private method to find all report plugins with can be executed
     * by the current user.
     *
     * @access private
     * @return array An array of all the plugins that the user has
     *               access to excute, indexed by the plugin type.
     */
    function _findDisplayableReports()
    {
        $aDisplayablePlugins = array();
        // Get all the report plugins.
        $aPlugins = &MAX_Plugin::getPlugins('reports', null, false);
        // Check the user's authorization level
        foreach ($aPlugins as $pluginType => $oPlugin) {
            if (!$oPlugin->isAllowedToExecute()) {
                continue;
            }
            $aDisplayablePlugins[$pluginType] = $oPlugin;
        }
        return $aDisplayablePlugins;
    }

    /**
     * A private method to group an array of report plugins according
     * to their category information.
     *
     * @access private
     * @param array $aPlugins An array of plugins that the user has
     *               access to excute, indexed by the plugin type.
     * @return array An array of all the plugins that the user has
     *               access to excute, indexed by category, then plugin
     *               type.
     */
    function _groupReportPlugins($aPlugins)
    {
        $aGroupedPlugins = array();
        foreach ($aPlugins as $pluginType => $oPlugin)
        {
            $aInfo = $oPlugin->info();
            $groupName = $aInfo['plugin-category-name'];
            $aGroupedPlugins[$groupName][$pluginType] = $oPlugin;
        }
        return $aGroupedPlugins;
    }

    /**
     * A private method to display a groupd array of plugins reports
     * to the UI.
     *
     * @access private
     * @param array An array plugins that the user has access to excute,
     *              indexed by category, then plugin type.
     */
    function _displayPluginList($aGroupedPlugins)
    {
        // Print the table start
        echo "<table border='0' width='100%' cellpadding='0' cellspacing='0'>";
        foreach ($aGroupedPlugins as $groupName => $aPluginsInGroup) {
            // Print the plugin category
            $this->_printCategoryTitle($groupName);
            // Print all the plugins in the category
            foreach ($aPluginsInGroup as $pluginType => $oPlugin) {
                $this->_printPluginSummary($oPlugin, $pluginType);
            }
            // Print a spacer row
            echo "<tr><td colspan='3'>&nbsp;</td></tr>";
        }
        // Print the table end
        echo "</table>";
    }

    /**
     * A private method to print the table row required for a report
     * category heading.
     *
     * @access private
     * @param string $pluginCategoryName The report plugin category name.
     */
    function _printCategoryTitle($groupName)
    {
        echo "<tr><td height='25' colspan='3'><b>{$groupName}</b></td></tr>
              <tr height='1'>
                <td width='30'><img src='images/break.gif' height='1' width='30'></td>
                <td width='200'><img src='images/break.gif' height='1' width='200'></td>
                <td width='100%'><img src='images/break.gif' height='1' width='100%'></td>
              </tr>";
    }

    /**
     * A private method to print the table row required for a
     * report plugin.
     *
     * @access private
     * @param Plugins_Reports $oPlugin A report plugin.
     * @param string $pluginType The report plugin type.
     */
    function _printPluginSummary($oPlugin, $pluginType)
    {
        $aInfo = $oPlugin->info();
        echo "<tr><td height='10' colspan='3'>&nbsp;</td></tr>
              <tr>
                <td width='30'>&nbsp;</td>
                <td width='200'><a href='report-generation.php?selection=$pluginType'>{$aInfo['plugin-name']}</a></td>
                <td width='100%'>{$aInfo['plugin-description']}</td>
              </tr>";
    }

    /**
     * A private method to display the report generation screen for a
     * report plugin to the UI.
     *
     * @access private
     * @param Plugins_Reports $oPlugin The plugin to display.
     * @param string $reportIdentifier The string identifying the report.
     */
    function _groupReportPluginGeneration($oPlugin, $reportIdentifier)
    {
        $aInfo = $oPlugin->info();
        // Print the report introduction
        $this->_displayReportIntroduction($aInfo['plugin-export'], $aInfo['plugin-name'], $aInfo['plugin-description']);
        // Get the plugins generation parameter details
        if ($aPluginInfo = $aInfo['plugin-import']) {
            // Print the start of the report execution submission form
            $this->_displayParameterListHeader();
            foreach ($aPluginInfo as $key => $aParameters) {
                // Print the report generation parameter
                $oField =& $this->oFieldFactory->newField($aParameters['type']);
                $oField->_name = $key;
                if (!is_null($aParameters['default'])) {
                    $oField->setValue($aParameters['default']);
                }
                $oField->setValueFromArray($aParameters);
                if (!is_null($aParameters['field_selection_names'])) {
                    $oField->_fieldSelectionNames = $aParameters['field_selection_names'];
                }
                if (!is_null($aParameters['size'])) {
                    $oField->_size = $aParameters['size'];
                }
                if (!is_null($aParameters['filter'])) {
                    $oField->setFilter($aParameters['filter']);
                }
                $this->_displayParameterBreak();
                echo "<tr><td width='30'>&nbsp;</td><td>{$aParameters['title']}</td><td>";
                $oField->_tabIndex = $this->tabindex;
                $oField->display();
                $this->tabindex = $oField->_tabIndex;
                echo "</td></tr>";
            }
            // Print a parameter break line
            $this->_displayParameterBreak();
            // Print the end of the report execution submission form
            $this->_displayParameterListFooter($reportIdentifier);
        }
        // Print the closing table info
        $this->_displayReportInformationFooter();
    }

    /**
     * A private method to display the introduction part of a report generation
     * page.
     *
     * @access private
     * @param string $export The export type of the report (eg. "csv").
     * @param string $name   The name of the report.
     * @param string $desc   The report's description.
     */
    function _displayReportIntroduction($export, $name, $desc)
    {
        echo "<table border='0' width='100%' cellpadding='0' cellspacing='0'>";
        echo "<tr><td height='25' colspan='3'>";
        if ($export == 'xls') {
            echo "<img src='images/excel.gif' align='absmiddle'>&nbsp;&nbsp;";
        }
        echo "<b>".$name."</b></td></tr>";
        echo "<tr height='1'><td colspan='3' bgcolor='#888888'><img src='images/break.gif' height='1' width='100%'></td></tr>";
        echo "<tr><td height='10' colspan='3'>&nbsp;</td></tr>";
        echo "<tr><td width='30'>&nbsp;</td>";
        echo "<td height='25' colspan='2'>";
        echo nl2br($desc);
        echo "</td></tr>";
        echo "<tr><td height='10' colspan='3'>&nbsp;</td></tr>";
    }

    /**
     * A private method to close off the table started by the
     * _displayReportIntroduction() method.
     *
     * @access private
     */
    function _displayReportInformationFooter()
    {
        echo "</table>";
    }

    /**
     * A private method to display the start of the form item required
     * for executing a report plugin.
     *
     * @access private
     */
    function _displayParameterListHeader()
    {
        echo "
        <form action='report-generate.php' method='get'>";
    }

    /**
     * A private method to display the end of the form item required
     * for executing a report plugin.
     *
     * @param string $reportIdentifier The string identifying the report.
     */
    function _displayParameterListFooter($reportIdentifier)
    {
        $generateTabIndex = $this->tabindex++;
        echo "
        <tr>
          <td height='25' colspan='3'>
            <br /><br />
            <input type='hidden' name='plugin' value='$reportIdentifier'>
            <input type='button' value='{$GLOBALS['strBackToTheList']}' onClick='javascript:document.location.href=\"report-index.php\"' tabindex='".($this->tabindex++)."'>
            &nbsp;&nbsp;
            <input type='submit' value='{$GLOBALS['strGenerate']}' tabindex='".($generateTabIndex)."'>
          </td>
        </tr>
        </form>";
    }

    /**
     * A private method to display a break line between parameters
     * in the report plugins generation page.
     *
     * @access private
     */
    function _displayParameterBreak()
    {
        echo "
        <tr height='10'>
            <td width='30'><img src='images/spacer.gif' height='1' width='100%'></td>
            <td><img src='images/break-l.gif' height='1' width='200' vspace='6'></td>
        </tr>";
    }

}

?>