<?php
/* Copyright (C) 2014  <f0o@devilcode.org>
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
 * Alerting CronJob
 * @author f0o <f0o@devilcode.org>
 * @copyright 2014 f0o, LibreNMS
 * @license GPL
 * @package LibreNMS
 */

/**
 * Generate SQL from Rule
 * @param string $rule Rule to generate SQL for
 * @return string
 */
function gensql($rule) {
	$tmp = explode(" ",$rule);
	$tables = array();
	foreach( $tmp as $opt ) {
		if( strstr($opt,'%') ) {
			$tmpp = explode(".",$opt,2);
			$tables[] = mres(str_replace("%","",$tmpp[0]));
		}
	}
	$tables = array_unique($tables);
	$x = sizeof($tables);
	$i = 0;
	$join = "";
	while( $i < $x ) {
		if( isset($tables[$i+1]) ) {
			$join .= $tables[$i].".device_id = ".$tables[$i+1].".device_id && ";
		}
		$i++;
	}
	$sql = "SELECT ".$tables[0].".device_id FROM ".implode(",",$tables)." WHERE (".$join."".$tables[0].".device_id = ?) && (".str_replace("%","",$rule).")";
	return $sql;
}


/**
 * Run all rules for a device
 * @param int $device Device-ID
 * @return void
 */
function runrules($device) {
	foreach( dbFetchRows("SELECT * FROM alert_rules WHERE alert_rules.disabled = 0 && ( alert_rules.device_id = -1 || alert_rules.device_id = ? ) ORDER BY device_id,id",array($device)) as $rule ) {
		echo " #".$rule['id'].":";
		$chk = dbFetchRows("SELECT state FROM alerts WHERE rule_id = ? && device_id = ? ORDER BY id DESC LIMIT 1", array($rule['id'], $device));
		if( $chk[0]['state'] === "2" ) {
			echo " SKIP  ";
		}
		$sql = gensql($rule['rule']);
		$qry = dbFetchRows($sql,array($device));
		if( sizeof($qry) > 0 ) {
			if( $chk[0]['state'] === "1" ) {
				echo " NOCHG ";
			} else {
				if( dbInsert(array('state' => 1, 'device_id' => $device, 'rule_id' => $rule['id']),'alerts') ){
					echo " ALERT ";
				}
			}
		} else {
			if( $chk[0]['state'] === "0" ) {
				echo " NOCHG ";
			} else {
				if( dbInsert(array('state' => 0, 'device_id' => $device, 'rule_id' => $rule['id']),'alerts') ){
					echo " OK    ";
				}
			}
		}
	}
}
?>
