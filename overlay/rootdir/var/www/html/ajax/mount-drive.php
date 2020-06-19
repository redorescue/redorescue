<?php
require_once('../functions.inc.php');

// Parse form variables
parse_str($_REQUEST['vars'], $vars);
$vars['type'] = preg_replace('/[^a-z]/', '', $_REQUEST['type']);

// Attempt to mount given partition
set_time_limit(10);
$result = mount_drive($vars);

// Return JSON-formatted result
print json_encode($result);

?>
