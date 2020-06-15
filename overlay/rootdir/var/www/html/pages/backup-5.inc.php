<?php
// Load status
$status = get_status();

// Save the posted directory
if (array_key_exists('dir', $_REQUEST)) $status->dir = $_REQUEST['dir'];

// Make sure the path exists
if (!is_dir(sane_path($status->dir))) crash('Not a valid path: '.sane_path($status->dir), 'backup-4');

// Save status
set_status($status);

$suggested_name = date('Ymd');
if (!empty($status->hostname)) $suggested_name .= '-'.$status->hostname;
?>

<h1>Backup</h1>
<h3>Step 5: Name the backup</h3>
<p>Enter a name to identify this backup image:</p>

<form id="redo_form" class="form-horizontal">

  <div class="form-group">
    <label class="col-sm-2 control-label">Name <a data-toggle="tooltip" title="Your name should contain only letters, numbers, dashes and underscores"><i class="fas fa-info-circle text-info"></i></a></label>
    <div class="col-sm-10">
      <input class="form-control" id="name" name="name" placeholder="<?php print $suggested_name; ?>" value="<?php print $suggested_name; ?>" type="text">
    </div>
  </div>

  <div class="form-group">
    <label class="col-sm-2 control-label">Notes <a data-toggle="tooltip" title="Optionally add a note describing your backup image"><i class="fas fa-info-circle text-info"></i></a></label>
    <div class="col-sm-10">
      <input class="form-control" id="notes" name="notes" placeholder="Optional description of this backup" value="" type="text">
    </div>
  </div>

  <div class="form-group">
    <div class="col-sm-12 text-right">
      <button type="reset" class="btn btn-default" onClick="$('#content').load('action.php?page=backup-4');">&lt; Back</button>
      <button type="submit" class="btn btn-warning">Next &gt;</button>
    </div>
  </div>

</form>

<script>
$("#redo_form").submit(function(event) {
	event.preventDefault();
	var name = $('#name').val();
	var notes = $('#notes').val();
	$.ajax({
		'url': '/ajax/save-id.php',
       		'type': 'POST',
		data: { type: 'backup', name: name, notes: notes },
	})
	.done(function(data) {
		r = $.parseJSON(data);
		if (r['status']) {
			// Success: Proceed to next page
			$('#content').load('action.php?page=backup-progress');
		} else {
			// Failure: Notify user of error
			bootbox.alert('<h3>Invalid backup name</h3><div class="alert alert-danger"><p><i class="fas fa-exclamation-triangle"></i> <b>Error: ' + r['error'] + '</b></p></div><p>Check your settings and try again.</p>');
		}
	});
});
</script>
