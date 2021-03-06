<?php
/**
 *   https://09source.kicks-ass.net:8443/svn/installer09/
 *   Licence Info: GPL
 *   Copyright (C) 2010 Installer09 v.2
 *   A bittorrent tracker source based on TBDev.net/tbsource/bytemonsoon.
 *   Project Leaders: Mindless,putyn,kidvision.
 **/
/*
+------------------------------------------------
|   $Date$ 110111
|   $Revision$ 2.0
|   $Author$ putyn,Bigjoos
|   $URL$
|   $warned
|   
+------------------------------------------------
*/
if ( ! defined( 'IN_INSTALLER09_ADMIN' ) )
{
	$HTMLOUT='';
	$HTMLOUT .= "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\"
		\"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">
		<html xmlns='http://www.w3.org/1999/xhtml'>
		<head>
		<title>Error!</title>
		</head>
		<body>
	<div style='font-size:33px;color:white;background-color:red;text-align:center;'>Incorrect access<br />You cannot access this file directly.</div>
	</body></html>";
	echo $HTMLOUT;
	exit();
}

require_once(INCL_DIR.'user_functions.php');
require_once(INCL_DIR.'html_functions.php');
require_once(CLASS_DIR.'class_check.php');
class_check(UC_STAFF);

$lang = array_merge( $lang );
$HTMLOUT="";
 
function mkint($x) {
	return (int)$x;
}

$stdfoot = array(/** include js **/'js' => array('wz_tooltip'));

	$this_url = $_SERVER["SCRIPT_NAME"];
	$do = isset($_GET["do"]) && $_GET["do"] == "disabled" ? "disabled" : "leechwarn";
	if($_SERVER["REQUEST_METHOD"] == "POST")
	{
		$r = isset($_POST["ref"]) ? $_POST["ref"] : $this_url;
		$_uids = isset($_POST["users"]) ? array_map('mkint',$_POST["users"]) : 0;
		if($_uids == 0 || count($_uids) == 0)
			stderr("Err","Looks like you didn't select any user !");
			
			$valid = array("unwarn","disable","delete");
			$act = isset($_POST["action"]) && in_array($_POST["action"],$valid) ? $_POST["action"] : false;
			if(!$act)
				stderr("Err","Something went wrong!");
			
			if($act == "delete")
			{
				if(sql_query("DELETE FROM users WHERE id IN (".join(",",$_uids).")"))
				{
					$c = mysql_affected_rows();
					header("Refresh: 2; url=".$r);
					stderr("Success",$c." user".($c > 1 ? "s" : "")." deleted!");
				}
				else
					stderr("Err","Something went wrong 2!");
			}
			
			if($act == "disable")
			{
				if(sql_query("UPDATE users set enabled='no', modcomment=CONCAT(".sqlesc(get_date( time(), 'DATE', 1 ) . " - Disabled by " . $CURUSER['username']."\n").",modcomment) WHERE id IN (".join(",",$_uids).")"))
				{
					$mc1->delete_value('user'.$_uids);
          $mc1->delete_value('MyUser_'.$_uids);
					$d = mysql_affected_rows();
					header("Refresh: 2; url=".$r);
					stderr("Success",$d." user".($d > 1 ? "s" : "")." disabled!");
				}
				else
					stderr("Err","Something went wrong 3!");
			}
			
			elseif($act == "unwarn")
			{
				$sub = "Leech Warn removed";
				$body = "Hey, your Leech warning was removed by ".$CURUSER["username"]."\nPlease keep in your best behaviour from now on.";
				$mc1->delete_value('user'.$_uids);
        $mc1->delete_value('MyUser_'.$_uids);
				$pms = array();
				foreach ($_uids as $id)
					$pms[] = "(0,".$id.",".sqlesc($sub).",".sqlesc($body).",".sqlesc(time()).")";
				
				if(count($pms))
				{
					$g = sql_query("INSERT INTO messages(sender,receiver,subject,msg,added) VALUE ".join(",",$pms)) or ($q_err = mysql_error());
					$q1 = sql_query("UPDATE users set leechwarn='0', modcomment=CONCAT(".sqlesc(get_date( time(), 'DATE', 1 ) . " - Leech Warning removed by " . $CURUSER['username']."\n").",modcomment) WHERE id IN (".join(",",$_uids).")") or ($q2_err = mysql_error());
					if($g && $q1)
					{
						header("Refresh: 2; url=".$r);
						stderr("Success",count($pms)." user".(count($pms) > 1 ? "s" : "")." Leech warning removed");
					}
					else
						stderr("Err","Something went wrong! Q1 - ".$q_err."<br />Q2 - ".$q2_err);
				}
 			}
		exit;
	}
	
	switch($do)
	{
		case "disabled" : $query = "SELECT id,username, class, downloaded, uploaded, IF(downloaded>0, round((uploaded/downloaded),2), '---') as ratio, disable_reason, added, last_access FROM users WHERE enabled='no' ORDER BY last_access DESC ";
    $title = "Disabled users";
	  $link = "<a href=\"staffpanel.php?tool=leechwarn&amp;action=leechwarn&amp;?do=warned\">Leech warned users</a>";
		break;
		case "leechwarn" : $query = "SELECT id, username, class, downloaded, uploaded, IF(downloaded>0, round((uploaded/downloaded),2), '---') as ratio, warn_reason, leechwarn, added, last_access FROM users WHERE leechwarn>='1' ORDER BY last_access DESC, leechwarn DESC ";
	  $title = "Leech Warned users";
	  $link = "<a href=\"staffpanel.php?tool=leechwarn&amp;action=leechwarn&amp;do=disabled\">disabled users</a>";
		break;
	}
	$g = sql_query($query) or print(mysql_error());
	$count  = mysql_num_rows($g);
	
	$HTMLOUT .= begin_main_frame();
	$HTMLOUT .= begin_frame($title."&nbsp;[<font class=\"small\">total - ".$count." user".($count>1 ? "s" : "")."</font>] - ".$link);
	
		if($count == 0)
		$HTMLOUT .= stdmsg("hey", "There is no ".strtolower($title));
		else
		{
		$HTMLOUT .="<form action='staffpanel.php?tool=leechwarn&amp;action=leechwarn' method='post'>
		<table width='600' cellpadding='3' cellspacing='2' style='border-collapse:separate;' align='center'>
		<tr>    	
			<td class='colhead' align='left' width='100%' >User</td>
			<td class='colhead' align='center' nowrap='nowrap'>Ratio</td>
			<td class='colhead' align='center' nowrap='nowrap'>Class</td>
			<td class='colhead' align='center' nowrap='nowrap'>Last access</td>
			<td class='colhead' align='center' nowrap='nowrap'>Joined</td>
			<td class='colhead' align='center' nowrap='nowrap'><input type='checkbox' name='checkall' /></td>
		</tr>";
	
			while($a = mysql_fetch_assoc($g))
			{
			$tip = ($do == "leechwarn" ? "Leech Warned for : ".$a["warn_reason"]."<br />"." Warned till ".get_date($a["leechwarn"], 'DATE',1)." - ".mkprettytime($a['leechwarn']- time()) : "Disabled for ".$a["disable_reason"]);
				$HTMLOUT .="<tr>
				  <td align='left' width='100%'><a href='userdetails.php?id={$a["id"]}' onmouseover=\"Tip('($tip)')\" onmouseout=\"UnTip()\">{$a["username"]}</a></td>
				  <td align='left' nowrap='nowrap'>{$a["ratio"]}<br /><font class='small'><b>D: </b>".mksize($a["downloaded"])."&nbsp;<b>U:</b> ".mksize($a["uploaded"])."</font></td>
				  <td align='center' nowrap='nowrap'>".get_user_class_name($a["class"])."</td>
				  <td align='center' nowrap='nowrap'>".get_date($a["last_access"],'LONG',0,1)."</td>
				  <td align='center' nowrap='nowrap'>".get_date($a["added"],'DATE',1)."</td>
				  <td align='center' nowrap='nowrap'><input type='checkbox' name='users[]' value='{$a["id"]}' /></td>
				</tr>";
			}
			
			$HTMLOUT .="<tr>
			<td colspan='6' class='colhead' align='center'>
				<select name='action'>
					<option value='unwarn'>Unwarn</option>
					<option value='disable'>Disable</option>
					<option value='delete'>Delete</option>
				</select>
				&raquo;
				<input type='submit' value='Apply' />
				<input type='hidden' value='".htmlspecialchars($_SERVER["REQUEST_URI"])."' name='ref' />
			</td>
			</tr>
			</table>
			</form>";
		}
	$HTMLOUT .= end_frame();
	$HTMLOUT .= end_main_frame();
echo stdhead($title) . $HTMLOUT . stdfoot($stdfoot);
?>