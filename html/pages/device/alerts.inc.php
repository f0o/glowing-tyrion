<?php
echo '<table cellspacing="0" cellpadding="5" class="table table-hover sortable" width="100%">
  <tr class="tablehead">
    <th width="10"></th>
    <th class="paddedcell" width="50">#</th>
    <th class="paddedcell">Rule</th>
    <th class="paddedcell" width="150">Timestamp</th>
    <th class="paddedcell" width="50">Severity</th>
    <th class="paddedcell" width="50">Status</th>
    <th width="10"></th>
  </tr>';
$rulei=1;
foreach( dbFetchRows("SELECT * FROM alert_rules WHERE device_id = -1 or device_id = ? ORDER BY device_id,id", array($device['device_id'])) as $rule ) {
	$sub = dbFetchRows("SELECT * FROM alerts WHERE rule_id = ? ORDER BY id DESC LIMIT 1", array($rule['id']));
	$ico = "ok";
	$col = "green";
	$extra = "";
	if( sizeof($sub) == 1 ) {
		$sub = $sub[0];
		if( (int) $sub['state'] === 0 ) {
			$ico = "ok";
			$col = "green";
		} elseif( (int) $sub['state'] === 1 ) {
			$ico = "remove";
			$col = "red";
			$extra = " class='danger'";
		} elseif( (int) $sub['state'] === 2 ) {
			$ico = "time";
			$col = "#800080";
			$extra = " class='warning'";
		}
	}
	if( $rule['disabled'] ) {
		$ico = "pause";
		$col = "";
		$extra = " class='active'";
	}
	echo "<tr".$extra."><td></td>";
	echo "<td><i>#".((int) $rulei++)."</i></td>";
	echo "<td><i>".htmlentities($rule['rule'])."</i></td>";
	echo "<td>".($sub['time_logged'] ? $sub['time_logged'] : "N/A")."</td>";
	echo "<td>".$rule['severity']."</td>";
	echo "<td style='text-align:center'><i class='glyphicon glyphicon-".$ico."' style='color:".$col."; font-size: 24px;' >&nbsp;</i></td>";
	echo "<td></td></tr>\r\n";
}
echo '</table>';
?>
