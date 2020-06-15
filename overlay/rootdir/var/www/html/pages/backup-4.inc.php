<?php
// Load status
$status = get_status();

// Nothing gets posted here. The drive should have been mounted
// in the previous step.

// Warn if bytes free is lower than this threshold:
define('FREE_SPACE_THRESHOLD', 100000000);

$u = get_usage();
?>

<h1>Backup</h1>
<h3>Step 4: Select destination folder</h3>
<p>The selected drive has <?php print $u['free']; ?> of free space available. Choose a folder to save the backup in:</p>

<form id="redo_form" class="form-horizontal">

  <div class="form-group">
    <label class="col-sm-2 control-label">Location <a data-toggle="tooltip" title="Folder to save the backup in"><i class="fas fa-info-circle text-info"></i></a></label>
    <div class="col-sm-10">
      <div class="input-group">
        <input class="form-control" id="dir" name="dir" placeholder="/" type="text" value="<?php if (property_exists($status, 'dir')) print $status->dir; ?>">
        <span class="input-group-btn">
          <button class="btn btn-info" type="button" onClick="chooseDir();"><i class="fas fa-folder-open"></i> Select</button>
        </span>
      </div>
    </div>
  </div>

  <div class="form-group">
    <div class="col-sm-12 text-right">
      <button type="reset" class="btn btn-default" onClick="$('#content').load('action.php?page=backup-3');">&lt; Back</button>
      <button type="submit" class="btn btn-warning">Next &gt;</button>
    </div>
  </div>

</form>

<script>

<?php if ($u['free_bytes'] < FREE_SPACE_THRESHOLD) { ?>

$(document).ready(function(){
	bootbox.alert({
		message: '<h3>Low space warning</h3><p>There is only <?php print $u['free']; ?> free on the selected destination drive.</p>',
		closeButton: true
	});
});

<?php } ?>

function chooseDir() {
	bootbox.dialog({
		message: '<div class="text-center"><i class="fas fa-spin fa-circle-notch text-primary"></i><br>Waiting for folder selection...</div>',
		closeButton: true
	});
	$.post("/ajax/open-dialog.php", { type: "dir", dir: $('#dir').val() })
		.done(function(data) {
			bootbox.hideAll();
			r = $.parseJSON(data);
			$('#dir').val(r['dir']);
			if ((typeof r['error'] !== "undefined") && (r['error'] !== null)) {
				bootbox.alert('<h3>Invalid folder selected</h3><p>'+r['error']+'. A valid folder has been selected for you.</p>');
			}
		});
}

$("#redo_form").submit(function(event) {
	event.preventDefault();
	var dir = $('#dir').val();
	$.ajax({
		'url': 'action.php?page=backup-5',
       		'type': 'POST',
		data: { dir: dir },
	})
	.done(function(data) {
		$('#content').html(data);
	});
});

</script>
