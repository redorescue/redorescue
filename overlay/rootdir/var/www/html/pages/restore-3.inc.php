<?php
// Load status
$status = get_status();

// Force refresh the list of disks 
$disks = get_disks(TRUE);

// Get list of disk options
$disk_options = get_disk_options($disks);
if (sizeof($disk_options)==0) crash('FATAL ERROR: No disks found!');
?>

<h1>Restore</h1>
<h3>Step 3: Select destination drive</h3>
<p>Select the target drive to restore to:</p>

<form id="redo_form" class="form-horizontal">
  <fieldset>

    <div class="form-group">
      <label class="col-sm-2 control-label">Target <a data-toggle="tooltip" title="Select the disk connected to your computer you want to restore the image to"><i class="text-info fas fa-info-circle"></i></a></label>
      <div class="col-sm-10">
        <select id="drive" class="form-control">
	<?php
	foreach ($disk_options as $ov=>$od) print "<option value='$ov'>$od</option>";
	?>
        </select>
      </div>
    </div>

    <div class="form-group">
      <div class="col-sm-12 text-right">
        <button type="reset" class="btn btn-default" onClick="$('#content').load('action.php?page=restore-2');">&lt; Back</button>
        <button type="submit" class="btn btn-warning">Next &gt;</button>
      </div>
    </div>

  </fieldset>
</form>

<script>
$("#redo_form").submit(function(event) {
	event.preventDefault();
	var url = 'action.php?page=restore-4';
	var drive = $('#drive').val();
	var posting = $.post(url, { drive: drive });
	posting.done(function(data) {
		$("#content").html($(data));
	});
});
</script>
