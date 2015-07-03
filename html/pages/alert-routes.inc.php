<?php
function a2t($a) {
	$r = "<table class='table table-condensed table-hover'><tbody>";
	foreach( $a as $k=>$v ) {
		if( !empty($v) ) {
			$r .= "<tr><td class='col-md-2'><i><b>".$k."</b></i></td><td class='col-md-10'>".(is_array($v)?a2t($v):"<code>".wordwrap($v,75,"<br/>")."</code>")."</td></tr>";
		}
	}
	$r .= '</tbody></table>';
	return $r;
}
if( isset($_REQUEST['link_name']) && isset($_REQUEST['link_condition']) && isset($_REQUEST['link_action']) ) {

	var_dump(dbInsert(array('name'=>$_REQUEST['link_name'],'condition'=>$_REQUEST['link_condition'],'action'=>$_REQUEST['link_action']),'alert_route_links'));

} elseif( isset($_REQUEST['chain_name']) && isset($_REQUEST['chain_action']) ) {

	var_dump(dbInsert(array('name'=>$_REQUEST['chain_name'],'action'=>$_REQUEST['chain_action']),'alert_route_chains'));

} elseif( isset($_REQUEST['map_chainid']) && isset($_REQUEST['map_linkid']) &&isset($_REQUEST['map_position']) ) {

	var_dump(dbInsert(array('link_id'=>$_REQUEST['map_linkid'],'chain_id'=>$_REQUEST['map_chainid'],'position'=>$_REQUEST['map_position']),'alert_route_maps'));

} elseif( isset($_REQUEST['route_chainid']) && isset($_REQUEST['route_target']) &&isset($_REQUEST['route_position']) ) {

	var_dump(dbInsert(array('target'=>$_REQUEST['route_target'],'chain_id'=>$_REQUEST['route_chainid'],'position'=>$_REQUEST['route_position']),'alert_route'));

}
?>
<h1>Add Link</h1>
<form method="post">
<input type='text' name='link_name' placeholder='Name'>
<input type='text' name='link_condition' placeholder='Condition'>
<input type='text' name='link_action' placeholder='Action'>
<input type='submit'>
</form>
<h1>All Links</h1>
<?php
foreach( dbFetchRows('select * from alert_route_links') as $link ) {
	echo a2t($link);
}
?>
<hr>
<h1>Add Chain</h1>
<form method="post">
<input type='text' name='chain_name' placeholder='Name'>
<input type='text' name='chain_action' placeholder='Default Action'>
<input type='submit'>
</form>
<h1>All Chains</h1>
<?php
foreach( dbFetchRows('select * from alert_route_chains') as $chain ) {
	$chain['links'] = dbFetchRows('select * from alert_route_links inner join alert_route_maps on alert_route_maps.link_id = alert_route_links.id where alert_route_maps.chain_id = ? order by alert_route_maps.position asc',array($chain['id']));
	echo a2t($chain);
}
?>
<hr>
<h1>Add Link to Chain</h1>
<form method="post">
<input type='text' name='map_linkid' placeholder='Link-ID'>
<input type='text' name='map_chainid' placeholder='Chain-ID'>
<input type='text' name='map_position' placeholder='Position'>
<input type='submit'>
</form>
<hr>
<h1>Add Chain to Target</h1>
<form method="post">
<input type='text' name='route_target' placeholder='Target'>
<input type='text' name='route_chainid' placeholder='Chain-ID'>
<input type='text' name='route_position' placeholder='Position'>
<input type='submit'>
</form>
<h1>Notification-Flow <?php echo $_REQUEST['ui_target'] ? ' for target: '.$_REQUEST['ui_target'] : ''; ?></h1>
<form method="get">
<input type='text' name='ui_target' placeholder='Target'>
<input type='submit'>
</form>
<div class="container-fluid">
	<div class="row">
		<div class="col-md-1" style='font-size:120px'>
			<i class='fa fa-bell'></i>
		</div>
		<div class="col-md-1" style='font-size:100px'>
			<i class='fa fa-level-down'></i>
		</div>
	</div>
	<div class="row">
		<div class="col-md-7 col-md-offset-1">
			<div style="visibility:none">
				<div style="visibility:none">
<? if( isset($_REQUEST['ui_target']) ) { ?>
<?php
$tmp = array();
foreach(dbFetchRows('
select
	*,
	alert_route_chains.name as chain_name,
	alert_route_maps.chain_id as chain_id
from
	alert_route
	inner join alert_route_maps
		on alert_route.chain_id = alert_route_maps.chain_id
	inner join alert_route_chains
		on alert_route.chain_id = alert_route_chains.id
	inner join alert_route_links
		on alert_route_maps.link_id = alert_route_links.id
where
	alert_route.target = ?
order by
	alert_route_maps.chain_id asc,
	alert_route_maps.position asc',array($_REQUEST['ui_target']) ) as $route ) {
	if( $tmp['chain_name'] != $route['chain_name'] ) {
		echo "
</div></div>
<div class='container'>
	<span style='font-size:25px;'>
		&nbsp;&nbsp;<i class='fa fa-code-fork fa-flip-vertical' style='font-size:100px'></i>&nbsp;<a onclick='".'$("#container_chain_'.$route['chain_id'].'").collapse("toggle")'."'>".$route['chain_name']."</a>
	</span>
	<div class='well collapse' id='container_chain_".$route['chain_id']."'>";
		$tmp = array();
	}
	if( !empty($tmp) ) { echo "<center><i class='fa fa-eject fa-flip-vertical' style='font-size:35px'></i></center>"; }
	echo a2t($route);
	$tmp = $route;
}
?>
<? } ?>
			</div></div>
		</div>
	</div>
	<div class="row">
		<div class="col-md-1 col-md-offset-1" style='font-size:100px'>
			<span style='font-size:25px;'>&nbsp;</span>&nbsp;<i class='fa fa-level-up fa-rotate-90'></i>
		</div>
		<div class="col-md-1" style='font-size:120px'>
			<i class='fa fa-envelope'></i>
		</div>
	</div>
</div>
