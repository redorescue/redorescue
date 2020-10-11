<?php
// Load status
$status = get_status();

// Set drive name and size
if (isset($status->drive)) $_REQUEST['drive'] = $status->drive;
$status->drive = preg_replace('/[^A-Za-z0-9_\-]/', '', $_REQUEST['drive']);
$status->drive_bytes = get_dev_bytes($status->drive);

// Save status
set_status($status);

// Load image details
$image = get_image_info();
if (is_string($image)) crash($image, 'verify-2');
?>

<h1>Verify</h1>
<h3>Step 3: Choose parts to verify</h3>
<p>Select which parts of the backup image to verify:</p>

<form id="redo_form" class="form-horizontal">

  <table class="table table-striped table-hover">
    <thead>
      <tr>
        <th><input type="checkbox" id="toggle" onClick="$('input:checkbox').prop('checked', $(this).prop('checked'));"></th>
        <th>Part</th>
        <th>Size</th>
        <th>Type</th>
        <th>Filesystem</th>
        <th>Details</th>
      </tr>
    </thead>
    <tbody>
	<?php
	foreach ($image->parts as $name=>$p) {
		$part_num = preg_replace('/[^0-9]/', '', $name);
		$checked = 'checked';
		if ($p->fs=='') $checked = 'disabled';
		print "<tr ".(is_null($p->fs)?'class="text-muted"':'').">";
		print "  <td><input type='checkbox' $checked name='verify_parts[]' id='verify_$name' value='$name'></td>";
		print "  <td>$name</td>";
		print "  <td>$p->size</td>";
		print "  <td nowrap>$p->type</td>";
		print "  <td nowrap>".(is_null($p->fs)?'raw <i class="fas fa-info-circle text-danger" data-toggle="tooltip" title="This partition was cloned in raw mode and can be restored, but only valid filesystems can be verified"></i>':$p->fs)."</td>";
		print "  <td>$p->desc</td>";
		print "</tr>";
	}
	?>
    </tbody>
  </table>

  <div class="form-group">
    <div class="col-sm-12">
      <div class="panel panel-info">
	<div id="details-toggle" class="panel-heading" style="cursor: pointer;"><i class="fas fa-angle-down" style="margin-right: 0.5ex;"></i> Image details</div>
	<div id="details" class="panel-body collapse small">
	<?php
	$fields = array(
		'Name'		=> $image->id,
		'Version'	=> $image->version,
		'Created'	=> $image->timestamp,
		'Notes'		=> '<i>'.$image->notes.'</i>',
		'Drive size'	=> round($image->drive_bytes / (1024**3), 2).'G ('.number_format($image->drive_bytes).' bytes)',
	);

	if (is_legacy($image->version)) $fields['Version'] .= ' <i class="fas fa-info-circle text-warning" data-toggle="tooltip" title="Backup created by a previous version of Redo Rescue; compatibility not guaranteed"></i>';
	$notes = array();
	foreach ($fields as $k=>$v) {
	?>
          <div class="row">
            <div class="col-sm-2"><p class="text-right"><b><?php print $k; ?></b></p></div>
              <div class="col-sm-10"><p>
		<?php
		print $v;
		?>
              </p></div>
	  </div>
	<?php
	}
	?>
        </div>
      </div>
    </div>
  </div>

  <div class="form-group">
    <div class="col-sm-12 text-right">
      <button type="reset" class="btn btn-default" onClick="$('#content').load('action.php?page=verify-2');">&lt; Back</button>
      <button type="submit" class="btn btn-warning">Next &gt;</button>
    </div>
  </div>

</form>

<script>

<?php if (isset($status->type)) { ?>
	// Set selection options
	$(document).ready(function() {
		// Uncheck all boxes
		$('input:checkbox').prop('checked', false);
		<?php
		if (isset($status->parts)) foreach ($status->parts as $s=>$d) {
			print '$("#verify_'.$s.'").prop("checked", true);';
		}
		?>
	});
<?php } // End set selection options ?>
	
$("#redo_form").submit(function(event) {
	event.preventDefault();
	var vars = $('#redo_form').serializeArray();
	vars.push({ name: 'type', value: 'verify' });
	var posting = $.ajax({
		'url': '/ajax/save-target.php',
       		'type': 'POST',
		data: vars,
	})
	posting.done(function(data) {
		r = $.parseJSON(data);
		if (r['status']) {
			// Success: Proceed to next page
			$('#content').load('action.php?page=verify-progress');
		} else {
			// Failure: Notify user of error
			bootbox.alert('<h3>Uh-oh!</h3><div class="alert alert-danger"><p><i class="fas fa-exclamation-triangle"></i> <b>Error: ' + r['error'] + '</b></p></div><p>Check your settings and try again.</p>');
		}
	});
});

$('#details-toggle').click(function() {
	$('#details').toggle();
	$('i', this).toggleClass("fa-angle-up fa-angle-down");
});

</script>
