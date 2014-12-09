/*
host_perfdata_file_template=
[HOSTPERFDATA]\t
$TIMET$\t
$HOSTNAME$\t
HOST\t
$HOSTSTATE$\t
$HOSTEXECUTIONTIME$\t
$HOSTLATENCY$\t
$HOSTOUTPUT$\t
$HOSTPERFDATA$
*/

$format = '';
$format .= "[HOSTPERFDATA]\t";
$format .= $obj['timestamp']."\t";
$format .= $obj['hostname']."\t";
$format .= md5($obj['rule'])."\t"; //FIXME: Better entity
$format .= ($obj['state'] ? $obj['severity'] : "ok")."\t";
$format .= 0."\t";
$format .= 0."\t";
$format .= str_replace("\n","",nl2br($obj['msg']))."\t";
$format .= "NULL"; //FIXME: What's the HOSTPERFDATA equivalent for LibreNMS? Oo
$format .= "\n";
return file_put_contents($opts, $format);
