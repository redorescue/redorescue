<?php
require_once('../functions.inc.php');

// How long to wait
set_time_limit(300);

// Attempt to mount given partition
switch ($_REQUEST['type']) {
case 'dir':
	$result = array('dir'=>'');
	$dir = sane_path($_REQUEST['dir']);
	$sel = trim(choose_dir($dir.'/'));
	if (strpos($sel, MOUNTPOINT)===FALSE) {
		// Oops, a folder outside the mountpoint was selected
		$result['error'] = 'Folder not located on destination drive';
	} else {
		// Remove the mountpoint prefix
		$result['dir'] = preg_replace('#^'.MOUNTPOINT.'#', '', $sel);
	}
	if (!is_dir($sel)) $result['error'] = 'Not a folder';
	break;
case 'file':
	$result = array('file'=>'');
	$file = sane_path($_REQUEST['file']);
	$sel = trim(choose_file($file));
	if (strpos($sel, MOUNTPOINT)===FALSE) {
		// Oops, a folder outside the mountpoint was selected
		$result['error'] = 'File not located on destination drive';
	} else {
		// Remove the mountpoint prefix
		$result['file'] = preg_replace('#^'.MOUNTPOINT.'#', '', $sel);
	}
	if (!is_file($sel)) $result['error'] = 'Not a file';
	break;
default:
	$result['error'] = 'Invalid dialog type';
	break;
}


// Return JSON-formatted result
print json_encode($result);

?>
