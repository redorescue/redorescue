<?php
include('phpqrcode/phpqrcode.php');

// Get posted data
$data = $_REQUEST['q'];

// Output SVG stream
echo QRcode::svg($data);
?>
