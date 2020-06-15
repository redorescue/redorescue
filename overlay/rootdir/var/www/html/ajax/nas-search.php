<?php
require_once('../functions.inc.php');

$sharelist = search_network_shares();
$found = sizeof($sharelist);
if ($found==0) {
	print "<h2>No shared drives found</h2>";
	print "<p>You will need to enter the network share details manually.</p>";
	die();
}
?>

<h3>Found <?php print "$found shared drive".($found==1?'':'s'); ?></h3>
<p>Select a network share from the list below:</p>
<div class="list-group">
	<?php
	foreach ($sharelist as $s) {
		print "  <button type='button' onClick='loadShare(\"".addslashes($s['location'])."\", \"".$s['domain']."\");' class='list-group-item'>".$s['location']."</button>";
	}
	?>
</div>

<script>
function loadShare(loc, dom) {
	$('#cifs_location').val(loc);
	$('#cifs_domain').val(dom);
	bootbox.hideAll();
}
</script>
