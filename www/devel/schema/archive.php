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
/**
* Openads Schema Management Utility
*
* @author Monique Szpak <monique.szpak@openads.org>
*
* $Id$
*
*/

function getLastChangeset()
{
    $dh = opendir(MAX_CHG);
    if ($dh) {
        while (false !== ($file = readdir($dh)))
        {
            $aMatches = array();
            if (preg_match('/changes_[\w\W]+_[\d]+\.xml/', $file, $aMatches))
            {
                $aFiles[] = $file;
            }
        }
        krsort($aFiles);
        if (count($aFiles)>0)
        {
            $result = $aFiles[0];
        }
        else
        {
            $result = false;
        }
    }
    closedir($dh);
    return $result;
}

function getChangesFile()
{
    $changesFile = $_COOKIE['changesetFile'];
    if ((!$changesFile) || (!file_exists(MAX_CHG.$changesFile)))
    {
        $changesFile = MAX_PATH.'/var/changes_tables_core.xml';
    }
    return $changesFile;
}

function getSchemaFile($changesFile)
{
    $schemaFile = $_COOKIE['schemaFile'];
    if (!$schemaFile)
    {
        $schemaFile = MAX_PATH.'/etc/tables_core.xml';
    }
    if ($changesFile)
    {
        $schemaFile = 'changes/'.str_replace('changes_', 'schema_', $changesFile);
    }
    return $schemaFile;
}

require_once '../../../init.php';
define('MAX_DEV', MAX_PATH.'/www/devel');
define('MAX_CHG', MAX_PATH.'/etc/changes/');

if (array_key_exists('select_changesets', $_POST))
{
    $file = $_POST['select_changesets'];
    if (empty($file))
    {
        $file = getLastChangeset();
    }
    setcookie('changesetFile', $file);
    $file = MAX_CHG.$file;
}
else if (array_key_exists('xajax', $_POST))
{
    $file = $_COOKIE['changesetFile'];
}
else if (in_array('exit', $_POST))
{
    $file = MAX_CHG.$_COOKIE['changesetFile'];
}
else if (array_key_exists('btn_migration_create', $_POST))
{
    $schemaFile = $_COOKIE['schemaFile'];
    if (!$schemaFile)
    {
        $schemaFile = MAX_PATH.'/etc/tables_core.xml';
    }
    $changesFile = $_COOKIE['changesetFile'];
    if ($changesFile)
    {
        $schemaFile = 'changes/'.str_replace('changes_', 'schema_', $changesFile);

        require_once 'oaSchema.php';
        $oaSchema = & new Openads_Schema_Manager($schemaFile);

        if (($aErrs = $oaSchema->checkPermissions()) !== true) {
            die(join("<br />\n", $aErrs));
        }

        $oaSchema->writeMigrationClass(MAX_CHG.$changesFile, MAX_CHG);
        $file = MAX_CHG.$changesFile;
    }
}
else if (array_key_exists('btn_field_save', $_POST))
{
    $schemaFile = $_COOKIE['schemaFile'];
    if (!$schemaFile)
    {
        $schemaFile = 'tables_core.xml';
        $file = MAX_PATH.'/etc/tables_core.xml';
    }
    $changesFile = $_COOKIE['changesetFile'];
    if (!$changesFile)
    {
        $changesFile = MAX_PATH.'/var/changes_tables_core.xml';
    }
    else
    {
        $schemaFile = str_replace('changes_', 'schema_', $changesFile);
        $file = MAX_CHG.$schemaFile;
        $schemaFile = 'changes/'.$schemaFile;
    }

    require_once 'oaSchema.php';
    $oaSchema = & new Openads_Schema_Manager($schemaFile, $changesFile);

    if (($aErrs = $oaSchema->checkPermissions()) !== true) {
        die(join("<br />\n", $aErrs));
    }

    $table_name = $_POST['table_name'];
    $field_name = $_POST['fld_old_name'];
    $field_name_was = $_POST['fld_new_name'];
    $oaSchema->fieldWasSave(MAX_CHG.$changesFile, $table_name, $field_name, $field_name_was);
}
else if (array_key_exists('btn_table_save', $_POST))
{
    $schemaFile = $_COOKIE['schemaFile'];
    if (!$schemaFile)
    {
        $schemaFile = MAX_PATH.'/etc/tables_core.xml';
    }
    $changesFile = $_COOKIE['changesetFile'];
    if (!$changesFile)
    {
        $changesFile = MAX_PATH.'/var/changes_tables_core.xml';
    }
    else
    {
        //$schemaFile = MAX_CHG.str_replace('changes_', 'schema_', $changesFile);
//        $schemaFile = MAX_PATH.'/etc/'.$schemaFile;
//        $schemaFile = MAX_PATH.'/etc/'.$schemaFile;
    }

    require_once 'oaSchema.php';
    $oaSchema = & new Openads_Schema_Manager($schemaFile, $changesFile);

    if (($aErrs = $oaSchema->checkPermissions()) !== true) {
        die(join("<br />\n", $aErrs));
    }

    //$table_name = $_POST['table_name'];
    $table_name = $_POST['tbl_old_name'];
    $table_name_was = $_POST['tbl_new_name'];
    $oaSchema->tableWasSave(MAX_CHG.$changesFile, $table_name, $table_name_was);

    $file = MAX_CHG.$changesFile;
}
else
{
    $file = getLastChangeset();
    if ($file)
    {
        setcookie('changesetFile', $file);
        $file = MAX_CHG.$file;
    }
}

require_once MAX_DEV.'/lib/xajax.inc.php';

if ($file && file_exists($file))
{
    header('Content-Type: application/xhtml+xml; charset=ISO-8859-1');
    readfile($file);
    exit();
}
else
{
    if ($file)
    {
        echo 'archive.php: error reading '.$file;
    }
    else
    {
        echo '<h2 style="font-family: Arial, Helvetica, sans-serif;text-align:center;">no changesets in archive</h2>';
    }

//    header('Location: index.php');
//    exit;
}

?>
