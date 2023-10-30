<?php
// Load status
$status = get_status();

// Unmount destination drive, if mounted
if (!unmount()) crash('Mountpoint busy or unable to be released', 'backup-2');

// Set partition list
$err = "No partitions selected. Go back and select the parts to save.";
$err_link = "backup-2";

// Check if parts are selected; if so, assign them and then use $_REQUEST to override
if (array_key_exists('parts', $_REQUEST)) {
	$status->parts = array();
	foreach ($_REQUEST['parts'] as $p) {
		if ($p=='false') continue;
		$status->parts[] = preg_replace('/[^A-Za-z0-9_\-]/', '', $p);
	}
}
if (sizeof($status->parts)==0) crash($err, $err_link);

// Save status
set_status($status);

// Load cached list of disks
$disks = get_disks();
foreach ($disks->blockdevices as $d) if ($d->name==$status->drive) $disk = $d;
if (!isset($disk)) crash('Unable to read information for selected drive.');

// Get partition options
$options = get_part_options($disks, $status->parts);
?>

<h1>Backup</h1>
<h3>Step 3: Select destination drive</h3>
<p>Choose a location to save the backup to:</p>

<form id="redo_form" class="form-horizontal">

  <ul id="redo_tabs" class="nav nav-tabs" style="margin-bottom: 1em;">
    <li class="active"><a href="#local" data-toggle="tab">This computer</a></li>
    <li><a href="#cifs" data-toggle="tab">Network drive</a></li>
    <li><a href="#nfs" data-toggle="tab">NFS</a></li>
    <li><a href="#ssh" data-toggle="tab">SSH</a></li>
    <li style="display: none;"><a href="#ftp" data-toggle="tab">FTP</a></li>
  </ul>
  <div id="myTabContent" class="tab-content">

    <div class="tab-pane fade active in" id="local">
      <div class="form-group">
        <label class="col-sm-2 control-label">Local disk <a data-toggle="tooltip" title="Save to a drive connected directly to this computer"><i class="fas fa-info-circle text-info"></i></a></label>
        <div class="col-sm-10">
<?php if (sizeof($options)>0) { ?>
	  <select class="form-control" name="local_part" id="local_part">
	<?php
	foreach ($options as $ov=>$od) print "<option value='$ov'>$od</option>";
	?>
	  </select>
<?php } else { ?>
          <p class="form-control-static text-muted"><i>There are no available local partitions to save to.</i></p>
<?php } ?>
	</div>
      </div>
    </div>

    <div class="tab-pane fade" id="cifs">
      <div class="form-group">
        <label class="col-sm-2 control-label">Location <a data-toggle="tooltip" title="Location of the shared network folder (SMB/CIFS)"><i class="fas fa-info-circle text-info"></i></a></label>
        <div class="col-sm-10">
          <div class="input-group">
            <input class="form-control" id="cifs_location" name="cifs_location" placeholder="\\host\folder" type="text">
            <span class="input-group-btn">
              <button class="btn btn-info" type="button" onClick="shareSearch();" data-toggle="tooltip" title="Attempt to find shared drives or folders in your network"><i class="fas fa-search"></i> Search</button>
            </span>
          </div>
        </div>
      </div>
      <div class="form-group">
        <label class="col-sm-2 control-label">Domain <a data-toggle="tooltip" title="Optional domain name to access this network share"><i class="fas fa-info-circle text-info"></i></a></label>
        <div class="col-sm-10">
          <input class="form-control" id="cifs_domain" name="cifs_domain" placeholder="WORKGROUP" type="text">
        </div>
      </div>
      <div class="form-group">
        <label class="col-sm-2 control-label">Username <a data-toggle="tooltip" title="Optional network share username"><i class="fas fa-info-circle text-info"></i></a></label>
        <div class="col-sm-4">
          <input class="form-control" id="cifs_username" name="cifs_username" placeholder="user" type="text">
        </div>
        <label class="col-sm-2 control-label">Password <a data-toggle="tooltip" title="Optional network share password"><i class="fas fa-info-circle text-info"></i></a></label>
        <div class="col-sm-4">
          <div class="input-group">
            <input type="password" class="form-control" id="cifs_password" name="cifs_password" placeholder="pass">
              <span class="input-group-btn">
                <button class="btn btn-info" type="button" onClick="togglePassword($('#cifs_password'), $(this));" data-toggle="tooltip" title="Toggle visibility"><i class="fas fa-eye"></i></button>
              </span>
          </div>
        </div>
      </div>
    </div>

    <div class="tab-pane fade" id="nfs">
      <div class="form-group">
        <label class="col-sm-2 control-label">Host <a data-toggle="tooltip" title="Hostname or IP address of the NFSv3 share"><i class="fas fa-info-circle text-info"></i></a></label>
        <div class="col-sm-10">
          <input class="form-control" id="nfs_host" name="nfs_host" placeholder="192.168.0.100" type="text">
        </div>
      </div>
      <div class="form-group">
        <label class="col-sm-2 control-label">Share <a data-toggle="tooltip" title="Exported NFS directory path"><i class="fas fa-info-circle text-info"></i></a></label>
        <div class="col-sm-10">
          <input class="form-control" id="nfs_share" name="nfs_share" placeholder="/home/user" type="text">
        </div>
      </div>
    </div>

    <div class="tab-pane fade" id="ssh">
      <div class="form-group">
        <label class="col-sm-2 control-label">Host <a data-toggle="tooltip" title="Hostname or IP address of the SSH server"><i class="fas fa-info-circle text-info"></i></a></label>
        <div class="col-sm-10">
          <input class="form-control" id="ssh_host" name="ssh_host" placeholder="192.168.0.100" type="text">
        </div>
      </div>
      <div class="form-group">
        <label class="col-sm-2 control-label">Folder <a data-toggle="tooltip" title="Optional folder to change to after connecting"><i class="fas fa-info-circle text-info"></i></a></label>
        <div class="col-sm-10">
          <input class="form-control" id="ssh_folder" name="ssh_folder" placeholder="/home/user" type="text">
        </div>
      </div>
      <div class="form-group">
        <label class="col-sm-2 control-label">Username <a data-toggle="tooltip" title="SSH username"><i class="fas fa-info-circle text-info"></i></a></label>
        <div class="col-sm-4">
          <input class="form-control" id="ssh_username" name="ssh_username" placeholder="user" type="text">
        </div>
        <label class="col-sm-2 control-label">Password <a data-toggle="tooltip" title="SSH password"><i class="fas fa-info-circle text-info"></i></a></label>
        <div class="col-sm-4">
          <div class="input-group">
            <input type="password" class="form-control" id="ssh_password" name="ssh_password" placeholder="pass">
              <span class="input-group-btn">
                <button class="btn btn-info" type="button" onClick="togglePassword($('#ssh_password'), $(this));" data-toggle="tooltip" title="Toggle visibility"><i class="fas fa-eye"></i></button>
              </span>
          </div>
        </div>
      </div>
    </div>

    <div class="tab-pane fade" id="ftp">
      <div class="form-group">
        <label class="col-sm-2 control-label">Host <a data-toggle="tooltip" title="Hostname or IP address of FTP server"><i class="fas fa-info-circle text-info"></i></a></label>
        <div class="col-sm-10">
          <input class="form-control" id="ftp_host" name="ftp_host" placeholder="ftp.hostname.com" type="text">
        </div>
      </div>
      <div class="form-group">
        <label class="col-sm-2 control-label">Username <a data-toggle="tooltip" title="Optional FTP username"><i class="fas fa-info-circle text-info"></i></a></label>
        <div class="col-sm-4">
          <input class="form-control" id="ftp_username" name="ftp_username" placeholder="user" type="text">
        </div>
	<label class="col-sm-2 control-label">Password <a data-toggle="tooltip" title="Optional FTP password"><i class="fas fa-info-circle text-info"></i></a></label>
        <div class="col-sm-4">
          <div class="input-group">
            <input type="password" class="form-control" id="ftp_password" name="ftp_password" placeholder="pass">
              <span class="input-group-btn">
                <button class="btn btn-info" type="button" onClick="togglePassword($('#ftp_password'), $(this));" data-toggle="tooltip" title="Toggle visibility"><i class="fas fa-eye"></i></button>
              </span>
          </div>
	</div>
      </div>
    </div>
  </div>

  <div class="form-group">
    <div class="col-sm-12 text-right">
      <button type="reset" class="btn btn-default" onClick="$('#content').load('action.php?page=backup-2');">&lt; Back</button>
      <button type="submit" class="btn btn-warning">Next &gt;</button>
    </div>
  </div>

</form>

<script>

function togglePassword($e, $b) {
	var newtype = $e.prop('type')=='password'?'text':'password';
	$e.prop('type', newtype);
	$("i", $b).toggleClass("fa-eye fa-eye-slash");
}

function shareSearch() {
	bootbox.dialog({
		message: '<div class="text-center"><i class="fas fa-spin fa-circle-notch text-primary"></i><br>Scanning network for shared drives...</div>',
		closeButton: false
	});
	$.post("/ajax/nas-search.php", { type: "cifs" })
		.done(function(data) {
			bootbox.hideAll();
			bootbox.alert(data);
		});
}

$("#redo_form").submit(function(event) {
	event.preventDefault();
	bootbox.dialog({
		message: '<div class="text-center"><i class="fas fa-spin fa-circle-notch text-primary"></i><br>Checking destination drive...</div>',
		closeButton: false
	});
	var vars = $('#redo_form').serialize();
	var type = $('ul#redo_tabs li.active a').attr('href');
	$.ajax({
		'url': '/ajax/mount-drive.php',
       		'type': 'POST',
		data: { type: type, vars: vars },
	})
	.done(function(data) {
		bootbox.hideAll();
		r = $.parseJSON(data);
		if (r['status']) {
			// Success: Proceed to next page
			$('#content').load('action.php?page=backup-4');
		} else {
			// Failure: Notify user of error
			bootbox.alert('<h3>Failed to access drive</h3><div class="alert alert-danger"><p><i class="fas fa-exclamation-triangle"></i> <b>Error: ' + r['error'] + '</b></p></div><p>Check your settings and try again.</p>');
		}
	});
});

</script>
