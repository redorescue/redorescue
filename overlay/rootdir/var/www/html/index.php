<?php
#
# Redo Rescue: Backup and Recovery Made Easy <redorescue.com>
# Copyright (C) 2010-2023 Zebradots Software
#
# This program is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 3 of the License, or
# (at your option) any later version.
#
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this program. If not, see <http://www.gnu.org/licenses/>.
#

require_once('functions.inc.php');

// Show welcome notice once
if (!file_exists(STATUS_FILE)) {
	system_notice(
		"Welcome to Redo Rescue",
		"Additional tools can be found through the start menu",
		"dialog-information"
	);
}

// Initiate variable storage
$status = new stdClass();

// Set host details
$host_info = get_host_details();
$status->ip = $host_info['ip'];
$status->hostname = $host_info['name'];

// Save status
set_status($status);

// Set QR data
define('QR_DATA', 'QSw3KUU5Jl1JPSQsVjxDQSo6JTUzPEcwVTxVOVYxQy06MTRVSiw2TSY+NyRSCmAK');
?>
<!doctype html>
<html lang="en">
  <head>
    <title>Redo Rescue</title>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/png" href="favicon.png">
    <link rel="stylesheet" href="/assets/bootstrap-3.4.1/dist/css/bootstrap-custom.min.css">
    <link rel="stylesheet" href="/assets/fontawesome-free-5.12.1-web/css/fontawesome.min.css">
    <link rel="stylesheet" href="/assets/fontawesome-free-5.12.1-web/css/solid.min.css">
    <link rel="stylesheet" href="/assets/fontawesome-free-5.12.1-web/css/brands.min.css">
    <link rel="stylesheet" href="/assets/animate.css-4.1.0/animate.min.css">
  </head>
  <body>

    <div id="flex-master">
      <div id="flex-header">
        <div class="logo"></div>
      </div>
      <div id="flex-body">

        <div id="content" class="container">
	</div>

      </div>
      <div id="flex-footer">
        <div class="row">
          <div class="col-xs-6">
	    <span class="small text-muted">Version <?php print get_version(); ?></span>
          </div>
	  <div class="col-xs-6 text-right">
	    <span class="small"><i class="fas fa-pizza-slice icon-button text-primary" data-toggle="popover" title="Donate" data-content="<div class='small text-center'><img src='/images/qr.php?q=<?php print convert_data(QR_DATA); ?>' width='240' height='240'><h4><i class='fab fa-bitcoin text-warning'></i> BTC:</h4><code><?php print convert_data(QR_DATA); ?></code></div>"></i></span>
	    <span class="small"><i class="fas fa-key icon-button text-primary" data-toggle="popover" title="Remote access" data-content="<span class='small'>You may also connect via VNC to <?php print (empty($status->ip)?'this system':'<b>'.$status->ip.'</b>'); ?> with password <code><?php print trim(file_get_contents(VNCPASS_FILE)); ?></code></span>"></i></span>
          </div>
        </div>
      </div>
    </div>

    <!-- Include JS files -->
    <script src="/assets/jquery-1.12.4/dist/jquery.min.js"></script>
    <script src="/assets/bootstrap-3.4.1/dist/js/bootstrap.min.js"></script>
    <script src="/assets/bootstrap-notify-3.1.3/dist/bootstrap-notify.min.js"></script>
    <script src="/assets/bootbox-5.4.0/dist/bootbox.min.js"></script>

    <script>
      $(document).ready(function(){

	// Set up popovers
	$('[data-toggle="popover"]').popover({
		container: "body",
		placement: "auto left",
		trigger: "hover click",
		html: true,
	});

	// Get current step and show page
	$("#content").load("action.php", function(responseTxt, statusTxt, xhr){
		if(statusTxt == "error") {
			bootbox.alert({
			size: "small",
				title: "Error loading content",
				message: xhr.status + ": " + xhr.statusText,
			});
		}
	});

      });
    </script>

  </body>
</html>
