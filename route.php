<?php
include_once("includes/defaults.inc.php");
include_once("config.php");
include_once($config['install_dir']."/includes/definitions.inc.php");
include_once($config['install_dir']."/includes/functions.php");
$debug=true;
require 'includes/object-cache.inc.php';

$alerts = array(
	array(
		'id' => 123,
		'device_id' => 3,
		'rule' => 11,
		'state' => 1,
		'name' => 'Test1 - Expected to be escalated',
		'time_logged' => 1434820366,
		'count' => 2,
		'transports' => array(
			'mail',
		),
//		'alerts' => new LazyLoad('alerts:3','select state from alerts where device_id = 3 and rule_id = ?'),
	),
	array(
		'device_id' => 4,
		'id' => 124,
		'rule' => 11,
		'state' => 2,
		'name' => 'Test2 - already issued ACK-notification Expected to be discarded',
		'time_logged' => 1434820366,
		'count' => 2,
		'transports' => array(),
//		'alerts' => new LazyLoad('alerts:4','select state from alerts where device_id = 4 and rule_id = ?'),
	),
	array(
		'device_id' => 4,
		'id' => 125,
		'rule' => 8,
		'state' => 2,
		'name' => 'Test3 - No Route aplies - Expected to be accepted',
		'time_logged' => 1434821000,
		'count' => 0,
		'transports' => array(),
//		'alerts' => new LazyLoad('alerts:4','select state from alerts where device_id = 4 and rule_id = ?'),
	),
);


$json = '{"id":"109","rule_id":"16","device_id":"3","state":"2","details":{"contacts":{"root@localhost":"NOC"},"rule":[{"device_id":"3","hostname":"foo.bar.baz","sysName":"foo","community":"public","authlevel":null,"authname":null,"authpass":null,"authalgo":null,"cryptopass":null,"cryptoalgo":null,"snmpver":"v2c","port":"161","transport":"udp","timeout":null,"retries":null,"bgpLocalAs":null,"sysObjectID":null,"sysDescr":"Linux foo 3.2.0-4-amd64 #1 SMP Debian 3.2.68-1+deb7u1 x86_64","sysContact":"root@localhost","version":"3.2.0-4-amd64","hardware":"Generic x86 64-bit","features":"Debian 7.8","location":"Slot, Rack, Room, DC, City, Country, Continent, Earth, Solar System, Milkyway, Known Universe","os":"linux","status":"1","ignore":"0","disabled":"0","uptime":"123456","agent_uptime":"0","last_polled":"2015-06-08 16:40:10","last_polled_timetaken":"9.87","last_discovered_timetaken":"1.17","last_discovered":"2015-06-08 13:07:23","last_ping":"2015-06-08 16:40:10","last_ping_timetaken":"0.03","purpose":null,"type":"server","serial":null,"icon":null,"poller_group":"0","service_id":"6","service_ip":"127.0.0.1","service_type":"ssl_cert","service_desc":"SSL","service_param":"","service_ignore":"0","service_status":"0","service_checked":"1433781604","service_changed":"0","service_message":"SSL_CERT CRITICAL: Error: verify depth is 6\n","service_disabled":"0"}],"delay":1434894360},"time_logged":"2015-06-08 16:40:10","rule":"%macros.device = \"1\" && %services.service_ignore = \"0\" && %services.service_disabled = \"0\" && %services.service_status != \"1\"     ","severity":"critical","extra":"{\"mute\":false,\"count\":\"-1\",\"delay\":\"300\",\"invert\":false}","name":"Service-Checks"}';
//$alerts = array( json_decode($json,true) );

$config['alert']['macros']['route'] = array(
	'past_15m' => time()-900
);

$devices = array(
	5 => array(
		'alerts' => array(
			7 => array(
				'state' => 0,
			)
		)
	)
);

$conds = array(
	array(
		'name' => 'Escalate',
		'condition' => '%self.time_logged < %macros.past_15m && %self.state != 2',
		'action' => '%self.transports[] = "sms" && %self.details.contacts[] = "Eve" && accept'
	),
	array(
		'name' => 'Discard if service-checks (#16) is triggered',
		'condition' => '%self.alerts.16 > 0 && %self.rule_id != 16',
		'action' => 'discard',
	),
	array(
		'name' => 'Discard if there is an incident using RuleID#7 on device 5 (UI needs to do Name<>ID convertion)',
		'condition' => '%devices.5.alerts.6 > 0',
		'action' => 'discard',
	),
);


$defaults = array(
	array(
		'name' => 'Send only 1 notification for OK/Ack',
		'condition' => '(%self.state == 0 || %self.state == 2) && %self.count >= 1',
		'action' => 'discard'
	),
);

define(pass,32);
define(accept,64);
define(discard,128);


foreach( $alerts as $alert ){
	echo "================ Testing Alert: ".$alert['name']."\r\n";
//	echo "Alert-Object: "; var_dump($alert);
	$rule = ValidateRoute($alert,$conds);
//	echo "Alert-Object: "; var_dump($alert);
	echo "================ Result: "; var_dump($rule);
	echo "\r\n\r\n";
}

function ValidateRoute(&$alert,$conds) {
	global $defaults, $config, $devices;
	$issue = true;
	$self = $alert;
	$self['time_logged'] = strtotime($self['time_logged']); //Local-Only variable, wont go into $alert
	$self['transports'] = &$alert['details']['transports']; //Shared variable changes go into $alert
	$self['alerts'] = new LazyLoad('alerts:'.$alert['device_id'],'select state from alerts where device_id = '.$alert['device_id'].' and rule_id = ?');
	echo "Routing Chain for ".$self['name']." \r\n";
	foreach( array_merge($conds,$defaults) as $k => $cond ) {
		echo " Link #$k\r\n";
		echo "  Condition: "; echo var_export($cond['name'],true); echo " - "; var_dump($cond['condition']);

		$cond['condition'] = populate($cond['condition'],false);
		echo "  Parsed:    "; var_dump($cond['condition']);
		$route = RunRouteJail('$ret = ('.$cond['condition'].') ? true : false;',$self,$config['alert']['macros']['route'],$devices);

		echo "  Applies?   "; var_dump($route);
		if( $route === true ) {
			echo "  Action:    "; var_dump($cond['action']);
			$cond['action'] = str_replace(array(' && ','pass','accept','discard'),array('; ','$ret = '.pass,'$ret = '.accept,'$ret = '.discard),$cond['action']);
			$cond['action'] = populate($cond['action'],false);
			echo "  Parsed:    "; var_dump($cond['action']);
			$action = RunRouteJail($cond['action'].';',$self,$config['alert']['macros']['route'],$devices);
			if( $action == pass ) {
				continue;
			} elseif( $action == accept ) {
				$issue = true;
				break;
			} elseif( $action == discard ) {
				$issue = false;
				break;
			}
		} else {
			continue;
		}
	}
	return $issue;
}

/**
 * Populate variables
 * @param string $txt Text with variables
 * @param bool $wrap Wrap variable for text-usage (default: true)
 * @return string
 */
function populate($txt,$wrap=true) {
  preg_match_all('/%([\w\.]+)/', $txt, $m);
  foreach( $m[1] as $tmp ) {
    $orig = $tmp;
    $rep = false;
    if( $tmp == "key" || $tmp == "value" ) {
      $rep = '$'.$tmp;
    } else {
      if( strstr($tmp,'.') ) {
        $tmp = explode('.',$tmp,2);
        $pre = '$'.$tmp[0];
        $tmp = $tmp[1];
      } else {
        $pre = '$obj';
      }
      $rep = $pre."['".str_replace('.',"']['",$tmp)."']";
      if( $wrap ) {
        $rep = "{".$rep."}";
      }
    }
    $txt = str_replace("%".$orig,$rep,$txt);
  }
  return $txt;
}
/**
 * "Safely" run eval
 * @param string $code Code to run
 * @param array $obj Object with variables
 * @return string|mixed
 */
function RunRouteJail($code,&$self,$macros,$devices) {
  $ret = "";
  eval($code);
  return $ret;
}
