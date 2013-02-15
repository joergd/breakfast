<?php

while (list($key, $val) = @each($_REQUEST)) { $GLOBALS[$key] = $val; }
while (list($key, $val) = @each($HTTP_POST_FILES)) { $GLOBALS[$key] = $val; }
while (list($key, $val) = @each($HTTP_SESSION_VARS)) { $GLOBALS[$key] = $val; }

if($categoryID) {

	include "connection.php";
	
	
	$sql = 'SELECT entry_id, entry_title AS title, entry_excerpt AS image, entry_text as caption'
	    . ' FROM mt_placement p'
	    . ' INNER JOIN mt_entry e ON e.entry_id = p.placement_entry_id'
	    . ' WHERE ( p.placement_category_id = ' . $categoryID . ' ) AND ( e.entry_status = 2 ) AND ( e.entry_title <> \'Details\' )'
	    . ' ORDER BY e.entry_created_on ASC LIMIT 0, 30';
	
	$conn = Connect();
	$result = mysql_query($sql) or die("Query Failed");
	
	print("<?xml version=\"1.0\" ?>");
	print("<images>");
	
	while ($line = mysql_fetch_row ($result)) {
		print ("<image id=\"$line[0]\">");
		print ("<title>$line[1]</title>");
		print ("<filename>$line[2]</filename>");
		print ("<caption>$line[3]</caption>");
		print ("</image>");
	}
	
	print("</images>");
	
	mysql_close();

}
?>
