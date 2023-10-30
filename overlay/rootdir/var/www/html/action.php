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

define('CONTENT', 'pages/');
define('LOADING_HTML', '<p class="text-center" style="margin-top: 5em;"><i class="fas fa-spin fa-6x fa-circle-notch text-muted"></i></p>');

require_once('functions.inc.php');

// Determine which page to display
$page = 'welcome';
if (isset($_REQUEST['page']))
	$page = preg_replace('/[^a-z0-9\-]/', '', $_REQUEST['page']);

// Include requested page
require_once(CONTENT.$page.'.inc.php');
activate_tooltips();
?>
