<?php
// Load template, confirm restore, and begin making timed AJAX calls

// Load status
$status = get_status();
?>

<h1>Restore</h1>
<h3>Restoring backup image</h3>
<p>Writing system snapshot to the target drive.</p>

<div class="row">
  <div class="col-sm-12">
    <div class="progress progress-striped active">
      <div id="overall_bar" class="progress-bar" style="width: 0%">
        <span id="overall_pct">Loading...</span>
      </div>
    </div>
  </div>
</div>

<table id="progress-details" class="table table-striped">
  <thead>
    <tr>
      <th>Part</th>
      <th>Done</th>
      <th>Size/Used</th>
      <th>Elapsed</th>
      <th>Remaining</th>
      <th>Speed</th>
      <th>Target</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td><span id="part_num"><span class="text-muted">0 of 0</span></span></td>
      <td><span id="part_pct"><span class="text-muted">0.00%</span></span></td>
      <td>
        <span id="part_size"><span class="text-muted">0GB</span></span>
        <span id="part_used"><span class="text-muted">/ 0GB</span></span>
        <span id="part_mode"><span class="text-muted">raw</span></span>
      </td>
      <td><span id="time_elapsed"><span class="text-muted">00:00:00</span></span></td>
      <td><span id="time_remaining"><span class="text-muted">00:00:00</span></span></td>
      <td><span id="speed"><span class="text-muted">0GB/min</span></span></td>
      <td><span id="target"><span class="text-muted"><?php print $status->drive; ?></span></td>
    </tr>
  </tbody>
</table>

<!-- Detailed log box -->
<section id="log">
  <div class="row">
    <div class="col-sm-10 col-sm-offset-1 text-center">
      <p id="details" class="text-muted">Please wait...</p>
    </div>
    <div class="col-sm-1 text-right">
      <span data-toggle="tooltip" title="Toggle details">
        <i id="log-toggle" class="fas fa-ellipsis-h icon-button text-primary" data-toggle="collapse" data-target="#log-area"></i>
      </span>
    </div>
  </div>
  <p id="log-area" class="collapse">
    <textarea class="form-control" rows="4" id="log-box" readonly></textarea>
    <button class="btn btn-default btn-block btn-sm" onClick="copyLog();"><i class="fas fa-clipboard-check"></i> Copy to clipboard</button>
  </p>
</section>
<!-- End of log box -->

<div class="row">
  <div class="col-sm-12 text-center">
    <button id="cancel" type="reset" class="btn btn-danger" onClick="cancel();"><i class="fas fa-times-circle"></i> Cancel</button>
    <button style="display: none;" id="again" type="button" class="btn btn-default" onClick="again();"><i class="fas fa-redo"></i> Start again</button>
    <button style="display: none;" id="exit" type="button" class="btn btn-primary" onClick="exit();"><i class="fas fa-check-circle"></i> Exit</button>
  </div>
</div>

<script>

function exit() {
	$('#content').load('/ajax/exit.php');
}

function again() {
	location.replace('/');
}

function cancel() {
	bootbox.confirm({ 
		message: '<h3>Are you sure?</h3><p>Canceling will abort and the image will be incomplete!</p>',
		buttons: {
			confirm: {
				label: 'Yes, I\'m sure!',
				className: 'btn-danger'
			},
			cancel: {
				label: 'No',
				className: 'btn-default'
			}
		},
		callback: function(result){
			if (result) $('#content').load('/ajax/exit.php');
		}
	});
}

function copyLog() {
	$('#log-box').select();
  	document.execCommand("copy");
}

function start() {
	// Begin timed interval updates
	var updater = setInterval(function() {
		$.ajax({
			'url': '/ajax/execute-restore.php',
	       		'type': 'GET',
		})
		.done(function(data) {
			r = $.parseJSON(data);
			if (r['status']) {
				// Success: Insert content on page
				if (r['overall_pct'] != null) {
					$('#overall_pct').html(r['overall_pct']+'%');
					$('#overall_bar').width(r['overall_pct']+'%');
				}
				if (r['part_pct'] != null) $('#part_pct').html(r['part_pct']);
				if (r['part_num'] != null) $('#part_num').html(r['part_num']);
				if (r['part_size'] != null) $('#part_size').html(r['part_size']);
				if (r['part_used'] != null) $('#part_used').html(' / '+r['part_used']);
				if (r['part_mode'] != null) $('#part_mode').html(' '+r['part_mode']);
				if (r['time_elapsed'] != null) $('#time_elapsed').html(r['time_elapsed']);
				if (r['time_remaining'] != null) $('#time_remaining').html(r['time_remaining']);
				if (r['speed'] != null) $('#speed').html(r['speed']);
				if (r['target'] != null) $('#target').html(r['target']);
				if (r['details'] != null) $('#details').html(r['details']);
				if (r['log_msg'] != null) {
					$('#log-box').scrollTop($('#log-box')[0].scrollHeight).append(r['log_msg']+"\n");
				}
				if (r['done'] != null) {
					// Operation complete
					clearInterval(updater);
					$('#overall_pct').html('100%');
					$('#overall_bar').width('100%').parent().removeClass('active');
					$('#cancel').hide();
					$('#again').show();
					$('#exit').show();
					bootbox.alert("<h3><i class='fas fa-check-circle text-success'></i> Restore complete</h3><p>All done! "+r['done']+"</p>");
				}
			} else {
				// Failure: An error occurred
				clearInterval(updater);
				$('#overall_bar').addClass('progress-bar-danger').parent().removeClass('active');
				bootbox.alert("<h3><i class='fas fa-times-circle text-danger'></i> Fatal error</h3><p>Operation failed and must be canceled. The error given was:</p><p><code>"+r['log_msg']+"</code></p>");
			}
		});
	}, 1 * 1000);
}

$(document).ready(function() {
	bootbox.confirm({ 
		message: '<h3>Are you sure?</h3><p>Restore the backup image? Existing data on the selected target partitions will be <b>permanently overwritten!</b></p>',
		buttons: {
			confirm: {
				label: 'Yes, I\'m sure!',
				className: 'btn-primary'
			},
			cancel: {
				label: 'No',
				className: 'btn-default'
			}
		},
		callback: function(result){
			if (result) {
				start();
			} else {
				$('#content').load('action.php?page=restore-4');
			}
		}
	});
});

</script>
