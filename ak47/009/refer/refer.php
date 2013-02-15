<?php

/*             _________________________________
      ________|                                 |________
      \       |              Refer              |       /
       \      |                                 |      /
       /      |_________________________________|      \
      /___________)                         (___________\

	Refer 2.1 - 27 November, 2003
	Copyright 2003 by Dean Allen - http://textism.com
	All rights reserved
*/

// BEGIN CONFIGURATION ------------------------------------------ //

	function rcfg() 
	{

//  edit these (must be inside 'quotes'):

	$rcfg['usr']   = 'ak47';		// db username
	$rcfg['pw']    = 'p@33word';		// db password
	$rcfg['host']  = 'localhost';	// MySQL server (frequently 'localhost')
	$rcfg['db']    = 'ak47';		// database name
	$rcfg['table'] = 'refer';		// any name you like - Refer will create the table


/*	Fill these in to halt the recording of unwanted referrals   
  	(e.g., an overly frequent google search, or a robot that
  	inserts a referrer for every page it visits) by matching a
  	distinct phrase. To add more, just duplicate a line and put
 	a different match phrase inside 'quotes'.                    */

	$rcfg['exclude'][] = 'viagra';

//	Filenames or directoriesthat match those in the following
//  list won't be recorded

	$rcfg['pexclude'][] = 'rss';
	$rcfg['pexclude'][] = 'rdf';
	$rcfg['pexclude'][] = 'css';

//	Directory names in the following list won't be recorded
//  Directories should be relative to the www root of your site,
//  e.g., 'about' for a directory reached at 'http://yoursite.com/about'

	$rcfg['dexclude'][] = 'refer';

//	Your web domain, without 'www.', e.g., 'textism.com'

	$rcfg['mydomain'] = 'stopthehippies.com';	


// 	Display visitor addresses in the list? true or false

	$rcfg['showhost'] = true;


// 	If displaying visitor addresses, trim down to top-level domain? 
// 	(i.e., mindspring.com, tiscali.co.uk)? true or false

	$rcfg['trimhost'] = true;


//  How many referrers do you want to view per page?

	$rcfg['count'] = 50;


//  Purge old referrers after how many days?

	$rcfg['expire'] = 7;


//	'Site name' (for RSS feed)

	$rcfg['sitename'] = 'stopthehippies.com';


// 	Enter the time difference in hours (i.e., +6 or -3)
// 	if any, between you and your webserver, otherwise use 0

	$rcfg['tzoffset'] = 0;


// 	Delete the # at your preferred time format

	$rcfg['tformat'] = "G:i";			//  12:00 (24hr clock)
#	$rcfg['tformat'] = "g:i a";			//  12:00 am (12hr clock)
#  	$rcfg['tformat'] = "j M g:i a";		//  1 Jan 12:00 am
#	$rcfg['tformat'] = "M j - G:i a";	//  Jan 1 12:00 am
#	$rcfg['tformat'] = "n/j G:i a";		//  1/1 12:00 am
	
		return $rcfg;
	}

//  END CONFIGURATION ---------------------------------------- //


	refer();

	function refer()
	{
		extract(rcfg());
		$mydomain = strtolower($mydomain);

		rdbConnect($db,$usr,$pw,$host);
	
		$httpHost = dbPrep('HTTP_HOST'); /* **** JOERG **** */
		$uri = dbPrep('REQUEST_URI');
		$ip = ipPrep('REMOTE_ADDR');
		$ref = dbPrep('HTTP_REFERER');
		$ref = (preg_match("/^http:\/\/[^\.]*\.?$mydomain/", $ref))?'':$ref;
		
		$ref = str_replace("www.","", $ref);

		if(is_array($exclude)) {
			foreach($exclude as $a) {
				$ref=preg_match("/".preg_quote($a)."/i",$ref)?'':$ref;
			}
		}

		if(is_array($pexclude)) {
			foreach($pexclude as $a) {
				$uri=preg_match("/".preg_quote($a)."/i",$uri)?'':$uri;
			}
		}

		
		/* **** JOERG **** */
		if(is_array($dexclude)) {
			foreach($dexclude as $a) {
				$uri=preg_match("/".preg_quote($a)."/i",$uri)?'':$uri;
			}
		}
		/* **** JOERG **** */

		$ref = preg_replace("/\/(index\.html?|index\.php)?$/",'',$ref);

		if ($uri!='' and preg_match ("/".$mydomain."/i", $httpHost)) {	/* **** JOERG **** */
			$ref = str_replace("http://","",$ref);
			@mysql_query("insert into $table set 
				time=NOW(), page='$uri', host='$ip', refer='$ref'");
		}
	}

	function dbPrep($var)
	{
		return (isset($_SERVER[$var])) ? strtolower(addslashes(trim($_SERVER[$var]))) : '';
	}
	
	function ipPrep($var)
	{
		return (isset($_SERVER[$var])) 
		?	strtolower(addslashes(trim(gethostbyaddr($_SERVER[$var])))) 
		:	'';
	}
	
	function rdbConnect($db,$usr,$pw,$host) 
	{
		if (!$linked = @mysql_connect($host,$usr,$pw)) {
			echo '<!--Refer failed to connect to mysql-->';
			return false;
		}
		@mysql_select_db($db);
		return true;
	}
?>
