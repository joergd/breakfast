<?php
	if(!function_exists('rcfg')) {
		include_once './refer.php';
	}
	$step = postSet('step');
	$thisdir = dirname($_SERVER['SCRIPT_FILENAME']);

	extract(rcfg());
	if (!rdbConnect($db,$usr,$pw,$host)) exit ('<p>Can&#8217;t connect with the config settings in refer.php</p>');

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
        "http://www.w3.org/TR/1999/REC-html401-19991224/loose.dtd">
<html lang="en">
<head>
	<meta http-equiv="content-type" content="text/html; utf-8">
	<title>Set up Refer</title>
	<link rel=stylesheet href="refer.css" type="text/css" media=screen>
</head>
<body style="padding:2em">
<?php
	
	if ($step == "upgrade"){
	
	mysql_query("ALTER TABLE `$table` MODIFY `$table` varchar (255 )NOT NULL DEFAULT '' ,ADD INDEX `refer` (`refer`) ,ADD INDEX `page` (`page`)");

	echo '<p>table updated</p>';
	ob_start();
	header("Location: index.php");
	
	
	} elseif($step == "create"){
		mysql_query("DROP TABLE IF EXISTS $table");

		mysql_query("CREATE TABLE $table (
			  `id` int(12) NOT NULL auto_increment,
			  `time` timestamp(14) NOT NULL,
			  `host` varchar(255) NOT NULL default '',
			  `page` varchar(255) NOT NULL default '',
			  `refer` varchar(255) NOT NULL default '',
			  PRIMARY KEY  (`id`),
			  KEY `time` (`time`),
			  KEY `refer` (`refer`),
			  KEY `page` (`page`)
			) TYPE=MyISAM");
		
    $result = mysql_list_tables($db);
    while ($row = mysql_fetch_row($result)) {
		$arr[] = $row[0];
    }   
    
    if (in_array($table,$arr)) {
    
    print "<p>New table <strong>$table</strong> created.</p>\n";
?>
	<p>That went well.</p>
	<p>Read the instructions below, and then <strong>delete this file</strong>, located at <code><?php echo $thisdir ?>/setupdb.php</code></p>
	<p style="margin-top:3em"><strong>Recording Referrers</strong></p>
	<p>The easiest method is to use a <code>.htaccess</code> file. If you use <code>*.html</code> or <code>*.htm</code> files to serve pages on your site, chances are you'll need to use <code>.htaccess</code> anyway, to instruct your server to run PHP code on those pages.</p>

	<p>Simply create a file called <code>.htaccess</code> (if one already exists, open it) in the topmost directory that serves pages on your site. Add the following code, and save:
</p>
	<p>
	<code>
	DirectoryIndex index.html index.htm index.php<br>
	AddType application/x-httpd-php .html .htm <br>
	php_value auto_prepend_file <?php echo $thisdir ?>/refer.php<br>
	</code>
	</p>
<p style="margin-top:2em">If on the other hand you want to monitor only specific files, or if your webhost doesn&#8217;t support <code>.htaccess</code> overrides, put the following PHP code in each html document you&#8217;d like to monitor:</p>
	<p>
	<code>
		&#60;?php<br>
		include '<?php echo $thisdir?>/refer.php';<br>
		?&#62;
	</code>
	</p>
	<p>A good place would be at the end of the page, after &#60;/html&#62;</p>
	<p>Once some referrers have been recorded, you will see them by loading http://<em>yoursite</em>/refer/index.php</p>
	<p>Hey, delete this file. <strong>Do it now</strong>.</p>
<?php
	} else {
		print 'could not create table '.$table;
	}
		} elseif($step=='') {

		$tbl_exists = @mysql_query("DESCRIBE $table");
			if (!$tbl_exists) {
	?>
		<form action="setupdb.php" method="post">
		<p>Database connection OK.</p>
		<p>Create refer table?</p>
		<input type=submit name="step" value="create">
		</form>
<?php


	} else {
		while($a = mysql_fetch_assoc($tbl_exists)){
			$tableinfo[] = $a;
		}
		if($tableinfo[4]['Type']!='varchar(255)'){
?>
		<form action="setupdb.php" method="post">
		<p>table <strong><?php echo $table ?></strong> exists, but is out of date. Shall I upgrade it?</p>
		<input type=submit name="step" value="upgrade">
		</form>
<?php

		
		} else {
?>
		<form action="setupdb.php" method="post">
		<p>table <strong><?php echo $table ?></strong> already exists. Do you want to delete and recreate it? <strong>This cannot be undone</strong>.</p>
		<input type=submit name="step" value="create">
		</form>
<?php	
		}
	
	}

	}
	

	function postSet($thing) 
	{
		return (isset($_POST[$thing])) ? $_POST[$thing] : '';
	}

?>
</body>
</html>
