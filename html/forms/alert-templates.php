<?
/* Copyright (C) 2014 Daniel Preussker <f0o@devilcode.org>
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>. */

/**
 * Alert Templates
 * @author f0o <f0o@devilcode.org>
 * @copyright 2014 f0o, LibreNMS
 * @license GPL
 * @package LibreNMS
 * @subpackage Alerts
 */

$ret = false;
if( is_numeric($_REQUEST['template_id']) && $_REQUEST['rule_id'] ) {
	//Update the template/rule mapping

	if( is_array($_REQUEST['rule_id']) ) {
		$_REQUEST['rule_id'] = implode(",",$_REQUEST['rule_id']);
	}
	if( substr($_REQUEST['rule_id'], 0,1) != "," ){
		$_REQUEST['rule_id'] = ",".$_REQUEST['rule_id'];
	}
	if( substr($_REQUEST['rule_id'],-1,1) != "," ){
		$_REQUEST['rule_id'] .= ",";
	}
	$ret = dbUpdate(array('rule_id' => mres($_REQUEST['rule_id'])), "alert_templates", "id = ?", array($_REQUEST['template_id']));
	echo "Updating tempalte: ";
} elseif( $_REQUEST['template'] && is_numeric($_REQUEST['template_id']) ) {
	//Update template-text

	$ret = dbUpdate(array('template' => $_REQUEST['template']), "alert_templates", "id = ?", array($_REQUEST['template_id']));
	echo "Updating tempalte: ";
} elseif( $_REQUEST['template'] ) {
	//Create new template

	$ret = dbInsert(array('template' => $_REQUEST['template']), "alert_templates");
	echo "Creating template: ";
}

if( $ret ) {
	die("SUCCESS");
} else {
	die("ERROR");
}
?>
