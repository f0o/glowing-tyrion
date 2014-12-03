<?php
if( isset($_POST['rule-submit']) ) {
	$rule = trim(($_POST['rule_partial']?$_POST['rule_partial']." ":"")."%".mres($_POST['entity'])." ".mres($_POST['condition'])." '".mres($_POST['value'])."'");
	if( dbInsert(array('device_id'=>$device['device_id'],'rule'=>$rule,'severity'=>mres($_POST['severity'])),'alert_rules') ) {
		$updated = 1;
		$update_message = "Added Rule: <i>".$rule."</i>";
	} else {
		$update_message = "Failed to add Rule: <i>".$rule."</i>";
	}
} elseif( isset($_POST['rule-glue']) ) {
	$rule_partial = $_POST['rule_partial']." %".mres($_POST['entity'])." ".mres($_POST['condition'])." '".mres($_POST['value'])."' ".mres($_POST['rule-glue']);
} elseif ($_POST['editing'])
{
  if ($_SESSION['userlevel'] > "7")
  {
    $override_sysContact_bool = mres($_POST['override_sysContact']);
    if (isset($_POST['sysContact'])) { $override_sysContact_string  = mres($_POST['sysContact']); }
    $disable_notify  = mres($_POST['disable_notify']);

    if ($override_sysContact_bool) { set_dev_attrib($device, 'override_sysContact_bool', '1'); } else { del_dev_attrib($device, 'override_sysContact_bool'); }
    if (isset($override_sysContact_string)) { set_dev_attrib($device, 'override_sysContact_string', $override_sysContact_string); };
    if ($disable_notify) { set_dev_attrib($device, 'disable_notify', '1'); } else { del_dev_attrib($device, 'disable_notify'); }

    $update_message = "Device alert settings updated.";
    $updated = 1;
  }
  else
  {
    include("includes/error-no-perm.inc.php");
  }
}

if ($updated && $update_message)
{
  print_message($update_message);
} elseif ($update_message) {
  print_error($update_message);
}

$override_sysContact_bool = get_dev_attrib($device,'override_sysContact_bool');
$override_sysContact_string = get_dev_attrib($device,'override_sysContact_string');
$disable_notify = get_dev_attrib($device,'disable_notify');
?>

<h3>Alert settings</h3>

<form id="edit" name="edit" method="post" action="" role="form" class="form-horizontal">
  <input type="hidden" name="editing" value="yes">
  <div class="form-group">
    <label for="override_sysContact" class="col-sm-3 control-label">Override sysContact:</label>
    <div class="col-sm-6">
      <input onclick="edit.sysContact.disabled=!edit.override_sysContact.checked" type="checkbox" id="override_sysContact" name="override_sysContact"<?php if ($override_sysContact_bool) { echo(' checked="1"'); } ?> />
    </div>
  </div>
  <div class="form-group">
    <div class="col-sm-3">
    </div>
    <div class="col-sm-6">
      <input id="sysContact" class="form-control" name="sysContact" size="32"<?php if (!$override_sysContact_bool) { echo(' disabled="1"'); } ?> value="<?php echo($override_sysContact_string); ?>" />
    </div>
  </div>
  <div class="form-group">
    <label for="disable_notify" class="col-sm-3 control-label">Disable all alerting for this host: </label>
    <div class="col-sm-6">
      <input id="disable_notify" type="checkbox" name="disable_notify"<?php if ($disable_notify) { echo(' checked="1"'); } ?> />
    </div>
  </div>
  <button class="btn btn-default btn-sm" type="submit" name="Submit">Save</button>
</form>

<hr>

<h3>Alert Rules</h3>

<?=($rule_partial ? "<pre>".$rule_partial."</pre>" : "");?>
<form method="post" role="form" id="rules" class="form-horizontal">
	<input type="hidden" name="rule_partial" value="<?=$rule_partial;?>">
	<div class="form-group">
		<label for='entity' class='col-sm-3 control-label'>Entity: </label>
		<div class="col-sm-6">
			<input id='suggest' name='entity'/>
			<p>Start typing for suggestions, use '.' for indepth selection</p>
		</div>
	</div>
	<div class="form-group">
		<label for='condition' class='col-sm-3 control-label'>Condition: </label>
		<div class="col-sm-6">
			<select name='condition' placeholder='Condition'>
				<option value='='>Equals</option>
				<option value='!='>Not Equals</option>
				<option value='>'>Larger than</option>
				<option value='>='>Larger than or Equals</option>
				<option value='<'>Smaller than</option>
				<option value='<='>Smaller than or Equals</option>
			</select>
		</div>
	</div>
	<div class="form-group">
		<label for='value' class='col-sm-3 control-label'>Value: </label>
		<div class="col-sm-6">
			<input name='value'/>
		</div>
	</div>
	<div class="form-group">
		<label for='rule-glue' class='col-sm-3 control-label'>Connection: </label>
		<div class="col-sm-6">
			<button class="btn btn-default btn-sm" type="submit" name="rule-glue" value="&&">And</button>
			<button class="btn btn-default btn-sm" type="submit" name="rule-glue" value="||">Or</button>
		</div>
	</div>
	<div class="form-group">
		<label for='severity' class='col-sm-3 control-label'>Severity: </label>
		<div class="col-sm-6">
			<select name='severity' placeholder='Severity'>
				<option value='ok'>OK</option>
				<option value='warning'>Warning</option>
				<option value='critical' selected>Critical</option>
			</select>
		</div>
	</div>
	<br/><button class="btn btn-default btn-sm" type="submit" name="rule-submit" value="save">Save Rule</button>
</form>
<script src="/js/jquery-ui.min.js"></script>
<script>
var cache = {};
$( "#suggest" ).autocomplete({
	source: function( request, response ) {
		var term = request.term;
		if ( term in cache ) {
			response( cache[ term ] );
		} else {
			$.getJSON("/ajax_rulesuggest.php?dev=<?php echo $device['device_id']; ?>", request, function( data, status, xhr ) {
				cache[ term ] = data;
				response( data );
			});
		}
		return;
	},
	minLength: 1,
});
</script>
<script>
var head = document.getElementsByTagName('head')[0];
var link = document.createElement('link'); 
link.rel = 'stylesheet';
link.type = 'text/css';
link.href = "/css/jquery-ui.min.css";
link.media = 'all';
head.appendChild(link);
</script>
<style>
	.ui-autocomplete {
		max-height: 100px;
		overflow-y: auto;
		overflow-x: hidden;
	}
	/* IE 6 doesn't support max-height
	 * we use height instead, but this forces the menu to always be this tall
	 */
	* html .ui-autocomplete {
		height: 100px;
	}
</style>
