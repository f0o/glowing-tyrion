#!/usr/bin/env php
<?php
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
 * Alerts Test-Case
 * @author f0o <f0o@devilcode.org>
 * @copyright 2014 f0o, LibreNMS
 * @license GPL
 * @package LibreNMS
 * @subpackage Alerts
 */

include_once("includes/defaults.inc.php");
include_once("config.php");
include_once("includes/definitions.inc.php");
include_once("includes/functions.php");
include_once("includes/alerts.inc.php");


define("TEST",true);
include("alerts.php");


/////////////////////////////////////////////////////
echo "SQL Generation Test:\r\n";/////////////////////
/////////////////////////////////////////////////////

$rules[] = "%ports.ifDescr !~ 'tun@' && %ports.ifDescr !~ 'tap@' && %ports.ifDescr !~ 'ppp@' && %ports.ifOperStatus != 'up'";
$rules[] = "%devices.hostname ~ '@core@' && %ports.ifDescr ~ 'gbit@' && %ports.ifOperStatus != 'up'";
$rules[] = "((%ports.ifInOctets_rate*8)/%ports.ifSpeed)*100 >= '80'";
$rules[] = "80 >= ((%ports.ifInOctets_rate*8)/%ports.ifSpeed)*100";
$rules[] = '%((%ports.ifInOctets_rate*8)/%ports.ifSpeed)*100 >= "20"';
foreach( $rules as $rule ) {
	echo ' Rule : '.$rule;
	echo "\r\n";
	echo ' SQL  : '.GenSQL($rule);
	echo "\r\n";
	echo " ---\r\n";
}

/////////////////////////////////////////////////////
echo "End.\r\n\r\n";/////////////////////////////////
/////////////////////////////////////////////////////

/////////////////////////////////////////////////////
echo "Alert-Object and Formating Test:\r\n";/////////
/////////////////////////////////////////////////////

$default_tpl = "%title\r\nSeverity: %severity\r\n{if %state == 0}Time elapsed: %elapsed\r\n{/if}Timestamp: %timestamp\r\nUnique-ID: %uid\r\nRule: {if %name}%name{else}%rule{/if}\r\n{if %faults}Faults:\r\n{foreach %faults}  #%key: %value\r\n{/foreach}{/if}Alert sent to: {foreach %contacts}%value <%key> {/foreach}";
$alert = dbFetchRow("SELECT alert_log.id,alert_log.rule_id,alert_log.device_id,alert_log.state,alert_log.details,alert_log.time_logged,alert_rules.rule,alert_rules.severity,alert_rules.extra,alert_rules.name FROM alert_log,alert_rules WHERE alert_log.rule_id = alert_rules.id && alert_log.device_id = ? && alert_log.rule_id = ? ORDER BY alert_log.id DESC LIMIT 1",array(22,1));
$alert['details'] = json_decode(gzuncompress($alert['details']),true);

echo "Raw-Object:\r\n";
var_dump($alert);
echo "----------\r\n";

echo "Post-Processed Object:\r\n";
$obj = DescribeAlert($alert);
var_dump($obj);
echo "----------\r\n";

echo "Formated Object:\r\n";
$msg = FormatAlertTpl($default_tpl,$obj);
var_dump($msg);
echo "----------\r\n";

/////////////////////////////////////////////////////
echo "End.\r\n\r\n";/////////////////////////////////
/////////////////////////////////////////////////////

?>
