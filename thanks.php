<?php
/**
 *   https://09source.kicks-ass.net:8443/svn/installer09/
 *   Licence Info: GPL
 *   Copyright (C) 2010 Installer09 v.2
 *   A bittorrent tracker source based on TBDev.net/tbsource/bytemonsoon.
 *   Project Leaders: Mindless,putyn,kidvision.
 **/
sleep(1);
require_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'include'.DIRECTORY_SEPARATOR.'bittorrent.php');
dbconn();
loggedinorreturn();

if (!isset($CURUSER))
    stderr("Error","Sorry but you cant add a thank you on your own torrent");

  $uid = $CURUSER['id'];
  $tid = isset($_POST['torrentid']) ? 0 + $_POST['torrentid'] : (isset($_GET['torrentid']) ? 0+$_GET['torrentid'] : 0);
  $do  = isset($_POST['action']) ? $_POST['action'] : (isset($_GET['action']) ? $_GET['action'] : 'list');
  $ajax = isset($_POST['ajax']) && $_POST['ajax']  == 1 ? true : false;
  
  
  function print_list() {
	GLOBAL $uid,$tid,$ajax;
	$target = $ajax ? '_self' : '_parent';
    $qt = sql_query("SELECT th.userid,u.username FROM thanks as th INNER JOIN users as u ON u.id=th.userid WHERE th.torrentid={$tid} ORDER BY u.class DESC") or sqlerr();
	$list = array(); $hadTh = false;
	if(mysql_num_rows($qt)> 0) {
	  while($a = mysql_fetch_assoc($qt)) {
	   $list[] = '<a href=\'userdetails.php?id='.$a['userid'].'\' target=\''.$target.'\'>'.$a['username'].'</a>';
	    $ids[] = $a['userid'];
	  }
	   $hadTh = in_array($uid,$ids) ? true : false;
	}
	if($ajax) 
	  return json_encode(array('list'=>(count($list)>0 ? join(', ',$list) : 'Not yet'),'hadTh'=>$hadTh,'status'=>true));
	else {
	$form = !$hadTh ? "<br/><form action='thanks.php' method='post'><input type='submit' class='btn' name='submit' value='Say thanks' /><input type='hidden' name='torrentid' value='{$tid}' /><input type='hidden' name='action' value='add' /></form>" : "" ;
	$out = (count($list)>0 ? join(', ',$list) : 'Not yet');
	return <<<IFRAME
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
<head>
<style type='text/css'>
body { margin:0;padding:0; 
	   font-size:12px;
	   font-family:arial,sans-serif;
	   color: #FFFFFF;
}
a, a:link, a:visited {
  text-decoration: none;
  color: #FFFFFF;
  font-size:12px;
}
a:hover {
  color: #FFFFFF
  text-decoration:underline;
  
}
.btn {
background-color:#890537;
border:1px solid #000000;
color:#FFFFFF;
font-family:arial,sans-serif;
font-size:12px;
padding:1px 3px;
}
</style>
<title>::</title>
</head>
<body>
{$out}{$form}
</body>
</html>
IFRAME;
	}
}

  switch($do) {
    case 'list':
	print(print_list());
    break;
	case 'add': 
	  if($uid > 0 && $tid > 0) {
	  
		$c = mysql_result(sql_query('SELECT count(id) FROM thanks WHERE userid = '.$uid.' AND torrentid = '.$tid),0);
		if($c == 0) {
		   if(sql_query('INSERT INTO thanks(userid,torrentid) VALUES('.$uid.','.$tid.')'))
	
				print(print_list());
	
		   else {
		  $msg = 'There was an error with the query,contatct the staff. Mysql error '.mysql_error();
		  print($ajax? json_encode(array('status'=>false,'err'=>$msg)) : $msg);
		
		   }
		}
	}
				// ===add karma
        @sql_query("UPDATE users SET seedbonus = seedbonus+5.0 WHERE id =".$uid."") or sqlerr(__FILE__, __LINE__);
        // ===end
  break;
}
?>