<?php
//
// Update the minified bootstrap CSS file
//

define('BASE_URL', '/assets/bootstrap/dist/css/');
define('OUT_FILE', 'dist/css/bootstrap-custom.min.css');

require_once('less.php-2.0.0/lessc.inc.php');
$options = array('compress'=>true, 'cache_dir'=>'/tmp');
$parser = new Less_Parser($options);
//$directories = array('./bootswatch/' => BASE_URL);
//$parser->SetImportDirs($directories);
$parser->parseFile('./less/bootstrap.less', BASE_URL);
$parser->parseFile('./bootswatch-cosmo/bootswatch.less', BASE_URL);
$parser->parseFile('./bootswatch-cosmo/variables.less', BASE_URL);
$parser->parseFile('./bootswatch-cosmo/custom.less', BASE_URL);
$css = $parser->getCss();
file_put_contents(OUT_FILE, $css);
$stats = stat(OUT_FILE);
print "Done: ".$stats['size']." bytes written.\n";

?>
