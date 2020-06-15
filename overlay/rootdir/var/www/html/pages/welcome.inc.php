<h1>Welcome</h1>
<h3>Select an option</h3>

<p>Easily create a snapshot of your system or completely restore from one. Click an option to begin:</p>

<div id="welcome" class="row">
  <div class="col-xs-6">
    <div class="text-right">
      <button onClick="showPage('backup-1');" class="btn btn-lg btn-primary">
        <p><i class="fas fa-upload fa-4x"></i></p>
        <div>Backup</div>
      </button>
    </div>
  </div>
  <div class="col-xs-6">
    <div class="text-left">
      <button onClick="showPage('restore-1');" class="btn btn-lg btn-primary">
        <p><i class="fas fa-download fa-4x"></i></p>
        <div>Restore</div>
      </button>
    </div>
  </div>
</div>

<!-- Load fonts immediately in a hidden container -->
<div id="font-awesome-loader" style="height: 0px; width: 0px; overflow: hidden;">
  <i class="fas fa-asterisk"></i>
</div>

<script>
function showPage(id) {
	$('#content').html('<?php print LOADING_HTML; ?>').load('action.php?page='+id);
}
</script>
