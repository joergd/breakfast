<?php

		// start the clock for runtime
    $microstart = rgetmicrotime();

	function refer_list()
	{
		global $microstart;	
		extract(rcfg());
		rdbConnect($db,$usr,$pw,$host);

		$format = getSet('format');               // is format specified in GET?
		$format = (!$format) ? 'html' : $format;  // if not, default to html

		$start = getSet('start');                 // is paging start specified in GET?
		$start = (!$start) ? 0 : $start;          // if not, it needs to be zero

		$thestart = $start;
		
		$glimpse = getSet('glimpse');             // glimpse?
		$queries = getSet('queries');             // queries?

			// establish the column count
		$cellcount = ($showhost) ? 4 : 3;
		$cellcount = (is_numeric($glimpse)) ? 3 : $cellcount;
		$cellcount = ($queries) ? 5 : $cellcount;
		
			// put together some navigation
		$current = ($start!=0 or is_numeric($glimpse)) 
		?	rHref('current','index.php')
		:	rHref('refresh','index.php');
		$help = rHref('help','','?format=help');
		$g1 = gL(1,'24hr',$glimpse);
		$g2 = gL(2,'48hr',$glimpse);
		$g3 = gL(3,'72hr',$glimpse);
		$g7 = gL(7,'1week',$glimpse);
		$qs = rHref('queries','?queries=1');
		$qs = ($queries) ? tag($qs,'strong') : $qs;


		if ($format == 'html') {
			define('b',' | ');
			echo '<table align="center" cellspacing="0" cellpadding="0">';
			
				// this is a row of navigational aids
			echo tr(td($current.b.'glimpse: '.$g1.$g2.$g3.$g7.b.$qs.b.$help,
				' colspan="'.$cellcount.'" class="noliner"'));

			echo '<tr>';			
			if(is_numeric($glimpse)){
				echo tdh('qty'),
					 tdh('where'),
					 tdh('from');
			} else {
				echo tdh('when'),
					 tdh('where'),
					 ($queries) ? tdh('query') : tdh('from'),
					 ($queries) ? tdh('via') : '',
					 ($showhost) ? tdh('who') : '';
			}
			echo '</tr>';

		} elseif ($format == 'rss') {
		
			echo '<rss version="0.91"><channel>',
			tag($sitename.' referrers','title'),
			tag('http://'.$mydomain,'link'),
			tag($sitename.' referrers','description');

		} else if ($format == 'help') {
			echo '<table align="center">';
		}
				
			// fetch a useful array from the database
		if (is_numeric($glimpse)) {
			$fetched = rFetchGlimpse($start,$count,$glimpse,$table);
		} elseif ($queries) {
			$fetched = rFetchQueries($start,$count,$table);
		} else {
			$fetched = rFetch($start,$count,$expire,$table);
		}
			// it's not useful if it's empty
		if (!$fetched) {
			exit('<tr><td colspan="'.
				$cellcount.'">No referrers recorded.</td></tr></table>');
		}

			// loop through the array and print some rows
		foreach ($fetched as $a=>$b) {

				// create and format row-specific variables
			extract(doSpecial($b));
			if($queries){
				$engine = preg_replace("/^([^\/]+)\/.*$/Ui","$1",$refer);
				$referprint = sortQueries($refer);
			} else {
				$referprint = str_replace("www.","",substr(urldecode($refer),0,50));
			}
			$referprint = rHref($referprint,'http://'.$refer);
			$pageprint = preg_replace("/^\//","",$page);
			
			if(!$glimpse){
				$when = date($tformat,$stamp+($tzoffset*3600));
				$host = ($trimhost) ? trimHost($host) : $host;
			} else $countprint = $tc;
			
			if ($format == "html") {

				$pageprint = (!$pageprint)
				?	"&#160;"
				:	$pageprint = rHref(urldecode($pageprint),$page);

				echo '<tr>';
				if (is_numeric($glimpse)){ 
					echo td($countprint),	// print glimpse row
						 td($pageprint),
						 td($referprint);
				} else {
					echo td($when),         // print refer row
						 td($pageprint),
						 td($referprint),
						 ($queries) ? td($engine) : '',
						 ($showhost) ? td($host) : '';
				}
				echo '</tr>';
					
			} else if ($format == "rss") {

				echo '<item>',
					tag($when.' - '.substr($refer,0,25),'title'),
					tag('http://'.$refer,'link'),
					tag(tag(tag('Where: ','em').$page,'p').
						tag(tag('From: ','em').$refer,'p').
						tag(tag('Who: ','em').$host,'p')
					,'description'),'</item>';
			}
			
		} // end foreach
		
			// see if the current result set is less than the qty per page
		$offset = (isset($fetched[$count-1]['stamp']))
		?	$fetched[$count-1]['stamp']
		:	$fetched[count($fetched)-1]['stamp'];

			// see if there are more referrers than currently displayed
		if ($queries) {
			$counted = nextCountQueries($offset,$table);
		} else {
			$counted = nextCount($offset,$table);
		}

			// if there's more to see, link to them
		if ($format == "html" and !is_numeric($glimpse)) {
			if($counted != 0){
				$amount = ($counted < $count) ? $counted : $count;
				echo '<tr><td align="right" colspan="',$cellcount,
					'"><a href="?start=',($thestart+$count),
					($queries) ? '&#38;queries=1' :'',
					'">Next ',$amount,' &#8594;</a></td></tr>';
			}
				// calculate the runtime & finish the table
			$rt = substr(rgetmicrotime() - $microstart,0,4);
			echo tr(td('runtime '.$rt.' sec',' class="noline" colspan="'.$cellcount.'"')),
				'</table>';

		} else if ($format == "rss") { // finish the rss xml

        	echo "</channel></rss>";	
        	
		} else if ($format == "help") { // finish help page

				echo '<tr><td align="left" width="600">',
				rHelp(),'</td></tr></table>';

		} else echo '</table>'; // finish without link to next

	} // fin

	function rFetchGlimpse($start,$count,$days,$table) 
	{
		$q = "select unix_timestamp(time) as stamp, page, refer, 
			count(refer) as tc from $table
			where time > date_sub(now(),interval $days day) 
			group  by refer, page
			order  by tc desc 
			limit $start, $count";
		return resToArray(mysql_query($q));
	}
	
	function rFetchQueries($start,$count,$table) 
	{
		$q = "select *, unix_timestamp(time) as stamp 
			from $table where refer like '%?q=%' or refer like '%?query=%' or refer like '%?searchfor=%' order by time desc limit $start, $count";
		return resToArray(mysql_query($q));
	}

	function rFetch($start,$count,$expire,$table) 
	{
		tidy($expire,$table);
		$q = "select *, unix_timestamp(time) as stamp 
			from $table order by time desc limit $start, $count";
		return resToArray(mysql_query($q));
	}

	function resToArray($r) 
	{
		if (@mysql_num_rows($r) > 0) {
			while ($a = mysql_fetch_assoc($r)) { $out[] = $a; }
			return $out;
		}
		return '';
	}
	
	function tidy($expire,$table) 
	{
		@mysql_query("delete from $table where time<date_sub(now(),interval $expire day)");
		@mysql_query("optimize $table");
	}

	function nextCount($time,$table) 
	{
		$q = "select count(*) from $table where time < from_unixtime($time)";
		return @mysql_result(mysql_query($q),0);
	}

	function nextCountQueries($time,$table) 
	{
		$q = "select count(*) from $table 
			where time < from_unixtime($time) and refer like '%?q=%'";
		return @mysql_result(mysql_query($q),0);
	}

	function trimHost($host) 
	{
		if (!preg_match("/^[0-9\.]*$/",$host)) {
			if (preg_match("/\.[a-z]{2}\.[a-z]{2}$/",$host)) { 
				return preg_replace("/.*\.([^\.]*\.[^\.]*\.[^\.]*)$/","$1",$host); 
			} else {
				return preg_replace("/.*\.([^\.]*\.[^\.]*)$/","$1",$host); 
			}
		} 
		return $host;
	}

	function gL($vars,$label,$glimpse)
	{
		$out = '<a href="index.php?glimpse='.$vars.'">'.$label.'</a>  ';
		$out = ($glimpse==$vars) ? tag($out,'strong') : $out;
		return $out;
	}

	function rGetMicrotime()
	{ 
    	list($usec, $sec) = explode(" ",microtime()); 
    	return ((float)$usec + (float)$sec); 
	}

	function getSet($thing) 
	{
		return (isset($_GET[$thing])) ? $_GET[$thing] : '';
	}
	
	function tag($content,$tag,$atts='') 
	{
		return '<'.$tag.$atts.'>'.$content.'</'.$tag.'>';
	}

	function td($content,$atts='') 
	{
		return tag($content,'td',$atts);
	}
	
	function tr($content) 
	{
		return tag($content,'tr');
	}

	function tdh($content) 
	{
		return tag(tag($content,'strong'),'td');
	}
	
	function rHref($content,$where='',$param='') 
	{
		return tag($content,'a',' href="'.$where.$param.'"');	
	}
	
	function doArray($in,$function)
	{
		return is_array($in) ? array_map($function,$in) : $function($in); 
	}

	function doSpecial($in)
	{ 
		return doArray($in,'htmlspecialchars'); 
	}

	function sortQueries($refer) 
	{
		parse_str(str_replace("?","&",$refer));
		if (isset($q)) {
			return urldecode($q);			
		} elseif (isset($query)) {
			return urldecode($query);			
		} elseif (isset($searchfor)) {
			return urldecode($searchfor);			
		}
		return '';
	}	
	
	function rHelp() 
	{
		extract(rcfg());	
?>
	<p style="margin-top:3em"><strong>Recording Referrers</strong> / <strong>Setup Help</strong></p>
	<p>The easiest method is to use a <code>.htaccess</code> file. If you use <code>*.html</code> or <code>*.htm</code> files to serve pages on your site, chances are you'll need to use <code>.htaccess</code> anyway, to instruct your server to parse PHP code on those pages.</p>
	<p>Simply create a file called <code>.htaccess</code> (if one already exists, open it) in the topmost directory that serves pages on your site. Add the following code, and save:
</p>
	<p style="margin-top:2em"><code>
	AddType application/x-httpd-php .html .htm <br />
	php_value auto_prepend_file <?php echo dirname($_SERVER['SCRIPT_FILENAME']) ?>/refer.php<br />
	</code></p>
<p style="margin-top:2em">If on the other hand you want to monitor only specific files, or if your webhost doesn&#8217;t support <code>.htaccess</code> overrides, put the following PHP code in each document or template you&#8217;d like to monitor:</p>
	<p><code>
		&#60;?php include '<?php echo dirname($_SERVER['SCRIPT_FILENAME']) ?>/refer.php' ?&#62;
	</code></p>
	<p>A good place would be at the end of the page, after <code>&#60;/html&#62;</code></p>
<?php
	}
?>
