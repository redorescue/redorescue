<?php
#
# Redo Rescue: Backup and Recovery Made Easy <redorescue.com>
# Copyright (C) 2010-2023 Zebradots Software
#
# This program is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 3 of the License, or
# (at your option) any later version.
#
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this program. If not, see <http://www.gnu.org/licenses/>.
#

require_once('../functions.inc.php');

// Load status
$status = get_status();

// Prepare return array with basic information
$return = array(
	'status'	=> TRUE,
);

// Begin the verification if we haven't already
if (!property_exists($status, 'progress')) {
	$error = verify_init();
	if ($error) ajax_abort($error);
	$status = get_status();
}

// Cast parts and progress lists as arrays
$status->parts = (array) $status->parts;
foreach ($status->progress as &$x) $x = (array) $x;

// Is a partition currently marked for processing?
if (sizeof($status->progress->exec) > 0) {
	// Parse log output
	$part_name = $status->progress->exec[0];
	$part_bytes = $status->image->parts->{$status->progress->exec[0]}->bytes;
	$part_count = sizeof($status->parts);
	$part_num = sizeof($status->progress->done) + 1;
	if ($part_num > $part_count) $part_num = $part_count;
	$return['part_num'] = $part_num.' of '.$part_count;
	$log_lines = get_log_lines();
	if (sizeof($log_lines) > 0) $return['log_msg'] = implode("\n", $log_lines);
	$part_pct = 0;
	foreach ($log_lines as $l) {
		$matches = array();
		if (preg_match('/^(Starting|Reading|Calculating|Syncing)/', $l))
			$return['details'] = $l;
		if (preg_match('/^Elapsed.*%$/', $l))
			$return['details'] = 'Finding areas with data... '.trim($l, ',');
		if (preg_match('/^File system:\s+(.*)$/', $l, $matches))
			$return['part_mode'] = $matches[1];
		if (preg_match('/^Device size:\s+(.*) =/', $l, $matches))
			$return['part_size'] = str_replace(' ', '', $matches[1]);
		if (preg_match('/^Space in use:\s+(.*) =/', $l, $matches))
			$return['part_used'] = str_replace(' ', '', $matches[1]);
		if (preg_match('/^Elapsed.*min,$/', $l)) {
			$secs = time() - $status->start_time;
			$elapsed = floor($secs/3600).gmdate(":i:s", $secs%3600);
			$elapsed = preg_replace('/^0+\:/', '', $elapsed);
			$return['details'] = 'Verifying image of device '.$part_name.'... Total time elapsed: '.$elapsed;
			list($e, $r, $p, $s) = explode(', ', $l);
			$return['time_elapsed'] = trim(preg_replace('/[^0-9\:]/', '', $e), ':');
			$return['time_remaining'] = trim(preg_replace('/[^0-9\:]/', '', $r), ':');
			$return['speed'] = preg_replace('/Rate\:\s+|,/', '', $s);
			$return['part_pct'] = preg_replace('/[^0-9\.\%]/', '', $p);
			// Ignore partclone's initial report of 1.00% complete when speed is "0.00byte/min"
			if ( (preg_match('/0\.00byte/', $return['speed'])) && ($return['part_pct']=='1.00%') )
				$return['part_pct'] = '0.00%';
			$part_pct = $return['part_pct'];
		}
		if (preg_match('/ERROR|WARNING/i', $l))
			ajax_abort($l);
		if (preg_match('/Input\/output error/i', $l))
			ajax_abort('Read/write error. Common causes: Drive failure, bad sectors, network disconnection, filesystem errors');
		if (preg_match('/broken pipe/i', $l))
			ajax_abort('Error reading image: Source drive may have disconnected');
		if (preg_match('/^Checked successfully\.$/', $l)) {
			$return['details'] = "Finished verifying $part_name";
			$part_pct = 100;
			// Move partition to the "done" bucket
			$status->progress->done[] = array_shift($status->progress->exec);
			set_status($status);
		}
		// Return overall progress, if known
		if ( (sizeof($status->progress->exec) > 0) && (!is_null($part_pct)) ) {
			$status->bytes_done = 0;
			foreach ($status->progress->done as $d) $status->bytes_done += $status->image->parts->$d->bytes;
			$status->bytes_done += intval(($part_pct/100) * $part_bytes);
			$overall_pct = round((100 * $status->bytes_done) / $status->bytes_total, 2);
			$return['overall_pct'] = $overall_pct;
			set_status($status);
		
		}
	}
} else {
	// No partitions currently processing
	if (sizeof($status->progress->wait) > 0) {
		// At least one is waiting to be processed
		$return['details'] = 'Preparing to verify image of '.$status->progress->wait[0];
		// Move a partition to the "exec" bucket
		$status->progress->exec[] = array_shift($status->progress->wait);
		set_status($status);
		$cmd = verify_part($status->progress->exec[0]);
		$return['part_pct'] = '0.00%';
	} else {
		// No partitions waiting; see if we're finished
		if (sizeof($status->progress->done) == sizeof($status->parts)) {
			$secs = time() - $status->start_time;
			$elapsed = floor($secs/3600).gmdate(":i:s", $secs%3600);
			$elapsed = preg_replace('/^0+\:/', '', $elapsed);
			beep('done');
			$return['details'] = "Verification completed successfully in $elapsed.";
			$return['done'] = "Verification completed successfully in $elapsed.";
			$return['overall_pct'] = "100.00";
		}
	}
}

// Print return data to be parsed by AJAX script
print json_encode($return);
?>
