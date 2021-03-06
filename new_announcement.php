<?php
/**
 *   https://09source.kicks-ass.net:8443/svn/installer09/
 *   Licence Info: GPL
 *   Copyright (C) 2010 Installer09 v.2
 *   A bittorrent tracker source based on TBDev.net/tbsource/bytemonsoon.
 *   Project Leaders: Mindless,putyn,kidvision.
 **/
require_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'include'.DIRECTORY_SEPARATOR.'bittorrent.php');
require_once(INCL_DIR.'user_functions.php');
require_once INCL_DIR.'bbcode_functions.php';
dbconn(false);
loggedinorreturn();

$lang = array_merge( load_language('global') );

if ($CURUSER['class'] < UC_ADMINISTRATOR) 
stderr('Error','Your not authorised');

$stdfoot = array(/** include js **/
                         'js' => array(
                         'shout')
                         );

if ($_SERVER['REQUEST_METHOD'] == 'POST')
{
	//== The expiry days.
	$days = array(
 	 array(7,'7 Days'),
 	array(14,'14 Days'),
 	array(21,'21 Days'),
 	array(28,'28 Days'),
 	array(56,'2 Months'));

  //== Usersearch POST data...
	$n_pms = (isset($_POST['n_pms']) ? $_POST['n_pms'] : 0);
	$ann_query = (isset($_POST['ann_query']) ? trim($_POST['ann_query']) : '');
	$ann_hash = (isset($_POST['ann_hash']) ? trim($_POST['ann_hash']) : '');

	if (hashit($ann_query,$n_pms) != $ann_hash) die(); // Validate POST...

	if (!preg_match('/\\ASELECT.+?FROM.+?WHERE.+?\\z/', $ann_query)) stderr('Error','Misformed Query');
	if (!$n_pms) stderr('Error','No recipients');

	//== Preview POST data ...
	$body = trim((isset($_POST['msg']) ? $_POST['msg'] : ''));
	$subject = trim((isset($_POST['subject']) ? $_POST['subject'] : ''));
	$expiry = 0 + (isset($_POST['expiry']) ? $_POST['expiry'] : 0);

	if ((isset($_POST['buttonval']) AND $_POST['buttonval'] == 'Submit'))
  {
 	//== Check values before inserting into row...
 	if (empty($body)) stderr('Error','No body to announcement');
 	if (empty($subject)) stderr('Error','No subject to announcement');

 	unset($flag);
 	reset($days);
 	foreach($days as $x)
 		 if ($expiry == $x[0]) $flag = 1;

 	if (!isset($flag)) stderr('Error','Invalid expiry selection');
 	$expires = time() + (86400 * $expiry); // 86400 seconds in one day.
 	$created = time();



 	$query = sprintf('INSERT INTO announcement_main '.
 		 '(owner_id, created, expires, sql_query, subject, body) '.
 	'VALUES (%s, %s, %s, %s, %s, %s)',
 	sqlesc($CURUSER['id']),
 	sqlesc($created),
 		 sqlesc($expires),
 		 sqlesc($ann_query),
 		 sqlesc($subject),
 	sqlesc($body));

 	sql_query($query);

 	if (mysql_affected_rows())
 	stderr('Success','Announcement was successfully created');
 	stderr('Error','Contact an administrator');
  }

  print stdhead("Create Announcement", false);
  $HTMLOUT ="";
 	$HTMLOUT.="<table class='main' width='750' border='0' cellspacing='0' cellpadding='0'>
 	<tr>
 	<td class='embedded'><div align='center'>
 	<h1>Create Announcement for ".($n_pms)." user".($n_pms>1 ? 's': '')."&nbsp;!</h1>";
 	$HTMLOUT.="<form name='compose' method='post' action='{$INSTALLER09['baseurl']}/new_announcement.php'>
 	<table border='1' cellspacing='0' cellpadding='5'>
 	<tr>
 	<td colspan='2'><b>Subject: </b>
 	<input name='subject' type='text' size='76' value='".htmlspecialchars($subject)."' /></td>
 	</tr>
 	<tr><td colspan='2'><div align='center'>
  ".textbbcode("compose","msg",$body)."
  </div></td></tr>";
 	$HTMLOUT .="<tr><td colspan='2' align='center'>";

 	$HTMLOUT .="<select name='expiry'>";

  reset($days);
 	foreach($days as $x)
  $HTMLOUT.='<option value="'.$x[0].'"'.(($expiry == $x[0] ? '' : '')).'>'.$x[1].'</option>';

 	$HTMLOUT .="</select>

 	<input type='submit' name='buttonval' value='Preview' class='btn' />
 	<input type='submit' name='buttonval' value='Submit' class='btn' />
 	</td></tr></table>
 	<input type='hidden' name='n_pms' value='".$n_pms."' />
 	<input type='hidden' name='ann_query' value='".$ann_query."' />
 	<input type='hidden' name='ann_hash' value='".$ann_hash."' />
 	</form><br /><br />
 	</div></td></tr></table>";

  if ($body)
	{
 	$newtime = time() + (86400 * $expiry);
 	$HTMLOUT .="<table width='700' class='main' border='0' cellspacing='1' cellpadding='1'>
 	<tr><td bgcolor='#663366' align='center' valign='baseline'><h2><font color='white'>Announcement: 
 	".htmlspecialchars($subject)."</font></h2></td></tr>
 	<tr><td class='text'>
 	".format_comment($body)."<br /><hr />Expires: ".get_date($newtime, 'DATE')."";
 	$HTMLOUT .="</td></tr></table>";
  }

} else { // Shouldn't be here
header("HTTP/1.0 404 Not Found");
$HTMLOUT .="<html><h1>Not Found</h1><p>The requested URL {$_SERVER['PHP_SELF']} was not found on this server.</p><hr /><address>Apache/1.1.11 (xxxxx) Server at ".$_SERVER['SERVER_NAME']." Port 80</address></body></html>\n";
die();
}
print $HTMLOUT . stdfoot($stdfoot);
?>