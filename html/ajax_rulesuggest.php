<?
session_start();
if( !isset($_SESSION['authenticated']) ) {
	die("Unauthorized.");
}
require_once("../includes/defaults.inc.php");
require_once("../config.php");
require_once("../includes/definitions.inc.php");
require_once("../includes/functions.php");
error_reporting(E_ALL);
if( isset($_GET['term']) ) {
	$chk = array();
   $_GET['term'] = mres($_GET['term']);
   if( strstr($_GET['term'],".") ) {
      $term = explode(".",$_GET['term']);
      $oterm = $term[0];
      if( $config['memcached']['enable'] ) {
      	$chk = $memcache->get('rule-suggest-'.$oterm);
      }
		if( !(sizeof($chk) > 0) || $chk === false ) {
			$tmp = dbFetchRows('SHOW COLUMNS FROM '.$term[0]);
			foreach( $tmp as $tst ) {
				if( isset($tst['Field']) ) {
					$chk[] = $tst['Field'];
				}
			}
		}
      $term = $term[1];
   } else {
   	$term = $_GET['term'];
      if( $config['memcached']['enable'] ) {
      	$chk = $memcache->get('rule-suggest-'.$oterm);
      }
		if( !(sizeof($chk) > 0) || $chk === false ) {
			$tmp = dbFetchRows('SHOW TABLES');
			foreach( $tmp as $tst ) {
				$tbl = array_shift($tst);
				$chhk = dbFetchRows('SELECT device_id FROM '.$tbl.' LIMIT 1');
				if( isset($chhk[0]['device_id']) ) {
					$chk[] = $tbl;
				}
			}
		}
   	$oterm = "";
   }
   if( $config['memcached']['enable'] ) {
   	$memcache->set('rule-suggest-'.$oterm,$chk,86400); //Cache for 24h
   }
	if( sizeof($chk) > 0 ) {
		foreach( $chk as $col ) {
			$lev = levenshtein($term, $col, 1, 10, 10);
			list( $tst ) = explode(".", $col, 2);
			if( $oterm != "" && $tst != $oterm ) {
				$col = $oterm.".".$col;
			}
			while( isset($ret["$lev"]) ) {
				$lev += 0.1;
			}
			$ret["$lev"] = $col;
			if( $lev == 0 ) {
				break;
			}
		}
		ksort($ret);
		die(json_encode($ret));
	} else {
		die();
	}
}
?>
