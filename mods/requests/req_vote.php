<?php if (!defined('IN_REQUESTS')) exit('No direct script access allowed');
/**
 *   https://09source.kicks-ass.net:8443/svn/installer09/
 *   Licence Info: GPL
 *   Copyright (C) 2010 Installer09 v.2
 *   A bittorrent tracker source based on TBDev.net/tbsource/bytemonsoon.
 *   Project Leaders: Mindless,putyn,kidvision.
 **/	
$res = sql_query('SELECT * FROM voted_requests WHERE requestid = '.$id.' and userid = '.$CURUSER['id']) or sqlerr(__FILE__,__LINE__);
$arr = mysql_fetch_assoc($res);

if ($arr) {
    $HTMLOUT .= "
<h3>{$lang['reset_success']}</h3>
<p style='text-decoration:underline;'>{$lang['vote_allowed']}</p>
<p><a class='altlink' href='viewrequests.php?id=$id&amp;req_details'><b>{$lang['vote_details']}</b></a> | 
<a class='altlink' href='viewrequests.php'><b>{$lang['vote_all']}</b></a></p>
<br /><br />";
}
else {
    sql_query('UPDATE requests SET hits = hits+1 WHERE id='.$id) or sqlerr(__FILE__,__LINE__);
    if (mysql_affected_rows()) {
        mysql_query('INSERT INTO voted_requests VALUES(0, '.$id.', '.$CURUSER['id'].')') or sqlerr(__FILE__,__LINE__);
        $HTMLOUT .=  "
<h3>Vote accepted</h3>
<p style='text-decoration:underline;'>{$lang['vote_success']}$id</p>
<p><a class='altlink' href='viewrequests.php?id=$id&amp;req_details'><b>{$lang['vote_details']}</b></a> |
<a class='altlink' href='viewrequests.php'><b>{$lang['vote_all']}</b></a></p>
<br /><br />";
    } else {
        $HTMLOUT .=  "
<h3>Error</h3>
<p style='text-decoration:underline;'>{$lang['vote_no_id']}$id</p>
<p><a class='altlink' href='viewrequests.php?id=$id&amp;req_details'><b>{$lang['vote_details']}</b></a> |
<a class='altlink' href='viewrequests.php'><b>{$lang['vote_all']}</b></a></p>
<br /><br />"; 
    }
}

/////////////////////// HTML OUTPUT //////////////////////////////
print stdhead('Vote').$HTMLOUT.stdfoot();
?>