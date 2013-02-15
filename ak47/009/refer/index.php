<?php
if(!function_exists('refer')) { include './refer.php'; }
include './display.php';
$format = getSet('format');
if($format == 'rss'){ exit(refer_list()); }
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<meta http-equiv="content-type" content="text/html; charset=utf-8" />
	<title>Referrers</title>
	<link rel="stylesheet" href="refer.css" type="text/css" />
</head>
	<body>			
		<?php refer_list(); ?>
		<p style="margin-top: 2em;text-align:center">This is <a href="http://www.textism.com/tools/refer/">Refer 2.1</a></p>
	</body>
</html>
