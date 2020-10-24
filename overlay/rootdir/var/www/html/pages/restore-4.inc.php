<?php
// Load status
$status = get_status();

// Set drive name and size
if (isset($status->drive)) $_REQUEST['drive'] = $status->drive;
$status->drive = preg_replace('/[^A-Za-z0-9_\-]/', '', $_REQUEST['drive']);
$status->drive_bytes = get_dev_bytes($status->drive);

// Save status
set_status($status);

// Force refresh the list of disks
$all_disks = get_disks(TRUE);

// Only show parts of the selected target drive
$disks = new stdClass();
foreach ($all_disks->blockdevices as $e) if ($e->name==$status->drive)
	foreach ($e->children as $c)
		// Skip extended partitions
		if ($c->parttype!=='0x5') $disks->blockdevices[] = $c;
$options = get_part_options($disks, array(), '/.*/');
$options = array(''=>'(None)') + $options;

// Load image details
$image = get_image_info();
if (is_string($image)) crash($image, 'restore-3');
?>

<h1>Restore</h1>
<h3>Step 4: Choose restore options</h3>
<p>Select which parts of the backup image to restore:</p>

<form id="redo_form" class="form-horizontal">

  <ul id="redo_tabs" class="nav nav-tabs" style="margin-bottom: 1em;">
    <li class="active"><a href="#baremetal" data-toggle="tab">Full system recovery <i class="fas fa-info-circle text-info" data-toggle="tooltip" title="Restores backup image even if the target is blank. Master boot record and partition table will be completely overwritten."></i></a></li>
    <li><a href="#selective" data-toggle="tab">Restore data only <i class="fas fa-info-circle text-info" data-toggle="tooltip" title="Preserves and does not alter the current master boot record or partition table. Only writes data into existing selected partitions."></i></a></li>
  </ul>
  <div id="myTabContent" class="tab-content">

    <div class="tab-pane fade active in" id="baremetal">
      <table class="table table-striped table-hover">
        <thead>
          <tr>
            <th><input type="checkbox" id="toggle" onClick="$('input:checkbox').prop('checked', $(this).prop('checked'));"></th>
            <th>Part</th>
            <th>Size</th>
            <th>Type</th>
            <th>Filesystem</th>
            <th>Details</th>
            <th></th>
	    <th>Target</th>
	  </tr>
        </thead>
        <tbody>
	<?php
	foreach ($image->parts as $name=>$p) {
		// Must also accommodate NVMe-style partition IDs
		$part_pre = '';
		preg_match('/(.+\D+)(\d+)$/', $name, $m);  // $m[2] contains the part_num
		$part_num = $m[2];
		if (preg_match('/^nvme/', $status->drive)) $part_pre = 'p';
		$status->parts[$part] = $status->drive.$part_pre.$part_num;
		$checked = 'checked';
		print "<tr>";
		print "  <td><input type='checkbox' $checked name='baremetal_parts[]' id='baremetal_$name' value='$name'></td>";
		print "  <td>$name</td>";
		print "  <td>$p->size</td>";
		print "  <td nowrap>$p->type</td>";
		print "  <td nowrap>$p->fs</td>";
		print "  <td>$p->desc</td>";
		print "  <td><i class='fas fa-arrow-right text-muted'></i></td>";
		print "  <td>$status->drive$part_pre$part_num</td>";
		print "</tr>";
	}
	?>
        </tbody>
      </table>
    </div>

    <div class="tab-pane fade" id="selective">
      <table class="table table-striped table-hover">
        <thead>
          <tr>
            <th><input type="checkbox" id="toggle" onClick="toggleAll($(this));"></th>
            <th>Part</th>
            <th>Size</th>
            <th>Type</th>
            <th>Filesystem</th>
            <th>Details</th>
            <th></th>
	    <th width="20%">Target</th>
	  </tr>
        </thead>
        <tbody>
	<?php
	foreach ($image->parts as $name=>$p) {
		$checked = '';
		print "<tr>";
		print "  <td><p class='form-control-static'><input type='checkbox' $checked name='selective_parts[]' id='selective_$name' value='$name' onClick='toggleEnabled(\"$name\");'></p></td>";
		print "  <td><p class='form-control-static'>$name</p></td>";
		print "  <td><p class='form-control-static'>$p->size</p></td>";
		print "  <td><p class='form-control-static text-nowrap'>$p->type</p></td>";
		print "  <td><p class='form-control-static text-nowrap'>$p->fs</p></td>";
		print "  <td><p class='form-control-static'>$p->desc</p></td>";
		print "  <td><p class='form-control-static'><i class='fas fa-arrow-right text-muted'></i></p></td>";
		print "  <td><select disabled name='map_$name' id='map_$name' class='form-control' onChange='updateOptions(\"$name\");'>";
		foreach ($options as $ov=>$od) print "<option value='$ov'>$od</option>";
		print "  </select></td>";
		print "</tr>";
	}
	?>
        </tbody>
      </table>
      <div class="alert alert-warning">
        <p><i class="fas fa-exclamation-circle"></i> Remapping partitions to new targets will render a restored operating system unbootable. This option is for advanced users only.</p>
      </div>
   </div>

  </div>

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
	$size_diff = $status->drive_bytes - $image->drive_bytes;
	$size_diff_h = round($size_diff / 1024**3, 1);
	if ($size_diff < 0)
		$notes[] = array(
			'class'	=> 'warning',
			'icon'	=> 'exclamation-triangle',
			'msg'	=> 'Target drive is '.($size_diff_h * -1).'G smaller than original &mdash; some parts may not fit',
		);
	if ($size_diff > 1024**2 * 100)
		$notes[] = array(
			'class'	=> 'info',
			'icon'	=> 'info-circle',
			'msg'	=> 'Target drive is '.$size_diff_h.'G larger than original &mdash; <b>GParted</b> can be used to enlarge partitions after restore',
		);
	if ($size_diff == 0)
		$notes[] = array(
			'class'	=> 'info',
			'icon'	=> 'info-circle',
			'msg'	=> 'Target drive is same size as original',
		);
	foreach ($fields as $k=>$v) {
	?>
          <div class="row">
            <div class="col-sm-2"><p class="text-right"><b><?php print $k; ?></b></p></div>
              <div class="col-sm-10"><p>
		<?php
		print $v;
		if ($k=='Drive size') foreach ($notes as $n) print " <i class='fas fa-".$n['icon']." text-".$n['class']."' data-toggle='tooltip' title='".$n['msg']."'></i>";
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
      <button type="reset" class="btn btn-default" onClick="$('#content').load('action.php?page=restore-3');">&lt; Back</button>
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
		// Switch tab
		$('.nav-tabs a[href="#<?php print $status->type; ?>"]').tab('show');
		<?php
		if (isset($status->parts)) foreach ($status->parts as $s=>$d) {
			print '$("#baremetal_'.$s.'").prop("checked", true);';
			print '$("#selective_'.$s.'").prop("checked", true);';
			print '$("#map_'.$s.'").prop("disabled", false).val("'.$d.'");';
			print 'updateOptions("'.$s.'");';
		}
		?>
	});
<?php } // End set selection options ?>
	
$("#redo_form").submit(function(event) {
	event.preventDefault();
	var type = $('ul#redo_tabs li.active a').attr('href');
	var vars = $('#redo_form '+type+' :input').serializeArray();
	vars.push({ name: 'type', value: type });
	var posting = $.ajax({
		'url': '/ajax/save-target.php',
       		'type': 'POST',
		data: vars,
	})
	posting.done(function(data) {
		r = $.parseJSON(data);
		if (r['status']) {
			// Success: Proceed to next page
			$('#content').load('action.php?page=restore-progress');
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

function toggleAll(e) {
	var state = $(e).prop('checked');
	$('#selective select option').prop('disabled', false);
	$('input:checkbox').prop('checked', $(e).prop('checked'));
	$('#selective select').prop('disabled', !$(e).prop('checked'));
	if (!$(e).prop('checked')) $('#selective select').val('');
}

function toggleEnabled(part) {
	$('#map_'+part).prop( 'disabled', !$('#selective_'+part).is(':checked') );
	if (!$('#selective_'+part).is(':checked')) {
		$('#map_'+part).val('');
		updateOptions(part);
	}
}

function updateOptions(part) {
	// Re-enable all options
	$('#selective select option').prop('disabled', false);
	$('#selective select').each(function() {
		// Disable selected options from all dropdowns (except "None")
		if ($(this).val() != '') {
			$('#selective select option[value="'+$(this).val()+'"]').not(":selected").prop('disabled', true);
		}
	});
	$('#selective select').each(function() {
		// Enable all selected options
		$(this).find('option[value="'+$(this).val()+'"]').prop('disabled', false);
	});
}

</script>
