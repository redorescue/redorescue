<?php
// Load status
$status = get_status();

// Set operation type
$status->op = 'backup';

// Clear drive selection
unset($status->drive);

// Save status
set_status($status);

// Force refresh the list of disks 
$disks = get_disks(TRUE);

// Get list of disk options
$disk_options = get_disk_options($disks);
if (sizeof($disk_options)==0) crash('FATAL ERROR: No disks found!');
?>

<h1>Backup</h1>
<h3>Step 1: Select source drive</h3>
<p>Select the source drive you want to make a backup image of:</p>

<form id="redo_form" class="form-horizontal">
  <fieldset>

    <div class="form-group">
      <label class="col-sm-2 control-label">Source drive <a data-toggle="tooltip" title="The disk connected to your computer that contains the information you want to backup"><i class="text-info fas fa-info-circle"></i></a></label>
      <div class="col-sm-10">
        <select id="drive" class="form-control">
	<?php
	foreach ($disk_options as $ov=>$od) print "<option value='$ov'>$od</option>";
	?>
        </select>
      </div>
    </div>

    <div class="form-group">
      <div class="col-sm-10 col-sm-offset-2 text-right">
        <button type="reset" class="btn btn-default" onClick="$('#content').load('action.php?page=welcome');">&lt; Back</button>
        <button type="submit" class="btn btn-warning">Next &gt;</button>
      </div>
    </div>

  </fieldset>
</form>

<script>
$("#redo_form").submit(function(event) {
	event.preventDefault();
	var url = 'action.php?page=backup-2';
	var drive = $('#drive').val();
	var posting = $.post(url, { drive: drive });
	posting.done(function(data) {
		$("#content").html($(data));
	});
});
</script>
