<?php

/*
 * LibreNMS
 *
 * Copyright (c) 2014 Neil Lathwood <https://github.com/laf/ http://www.lathwood.co.uk/fa>
 * Copyright (c) 2014 Daniel Preussker <f0o@devilcode.org>
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 */

$rule = implode(" ", $_POST['rules']);
$rule = rtrim($rule,'&&');
$rule = rtrim($rule,'||');
if( validate_device_id($_POST['device_id']) ) {
	$device_id = $_POST['device_id'];
} else {
	die("ERROR: Malformed Device-ID.");
}
$tmp = dbInsert(array('device_id'=>$device_id,'rule'=>$rule,'severity'=>mres($_POST['severity'])),'alert_rules');
if( $tmp ) {
    $update_message = "Added Rule: <i>".$rule."</i>";
} else {
    $update_message = "ERROR: Failed to add Rule: <i>".$rule."</i>";
}
echo $update_message;
