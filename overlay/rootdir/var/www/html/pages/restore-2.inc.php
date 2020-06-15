<?php
// Load status
$status = get_status();

// Nothing gets posted here. The drive should have been mounted
// in the previous step.
?>

<h1>Restore</h1>
<h3>Step 2: Select backup image</h3>
<p>Select the backup file to restore from:</p>

<form id="redo_form" class="form-horizontal">

  <div class="form-group">
    <label class="col-sm-2 control-label">Image file <a data-toggle="tooltip" title="Name of the backup image to restore"><i class="fas fa-info-circle text-info"></i></a></label>
    <div class="col-sm-10">
      <div class="input-group">
        <input class="form-control" id="file" name="file" placeholder="mybackup.redo" type="text" value="<?php if (property_exists($status, 'file')) print $status->file; ?>">
        <span class="input-group-btn">
          <button class="btn btn-info" type="button" onClick="chooseFile();"><i class="fas fa-folder-open"></i> Select</button>
        </span>
      </div>
    </div>
  </div>

  <div class="form-group">
    <div class="col-sm-12 text-right">
      <button type="reset" class="btn btn-default" onClick="$('#content').load('action.php?page=restore-1');">&lt; Back</button>
      <button type="submit" class="btn btn-warning">Next &gt;</button>
    </div>
  </div>

</form>

<script>

function chooseFile() {
	bootbox.dialog({
		message: '<div class="text-center"><i class="fas fa-spin fa-circle-notch text-primary"></i><br>Waiting for file selection...</div>',
		closeButton: true
	});
	$.post("/ajax/open-dialog.php", { type: "file", file: $('#file').val() })
		.done(function(data) {
			bootbox.hideAll();
			r = $.parseJSON(data);
			$('#file').val(r['file']);
			if ((typeof r['error'] !== "undefined") && (r['error'] !== null)) {
				bootbox.alert('<h3>Invalid image file</h3><p>'+r['error']+'. Please select a valid backup image.</p>');
			}
		});
}

$("#redo_form").submit(function(event) {
	event.preventDefault();
	var file = $('#file').val();
	$.ajax({
		'url': '/ajax/save-id.php',
       		'type': 'POST',
		data: { type: 'restore', file: file },
	})
	.done(function(data) {
		r = $.parseJSON(data);
		if (r['status']) {
			// Success: Proceed to next page
			$('#content').load('action.php?page=restore-3');
		} else {
			// Failure: Notify user of error
			bootbox.alert('<h3>Invalid image file</h3><div class="alert alert-danger"><p><i class="fas fa-exclamation-triangle"></i> <b>Error: ' + r['error'] + '</b></p></div><p>Check your settings and try again.</p>');
		}
	});
});

</script>
