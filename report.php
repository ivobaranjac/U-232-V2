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
dbconn();
loggedinorreturn();
parked();

$lang = array_merge( load_language('global'), load_language('report') );

$HTMLOUT = $id_2 = $id_2b='';
// === now all reports just use a single var $id and a type thanks dokty... again :P
$id = ($_GET["id"] ? $_GET["id"] : $_POST["id"]);
$type = ($_GET["type"] ? $_GET["type"] : $_POST["type"]);
if (!is_valid_id($id))
    stderr("{$lang['report_error']}", "{$lang['report_error1']}");
$typesallowed = array("User", "Comment", "Request_Comment", "Offer_Comment", "Request", "Offer", "Torrent", "Hit_And_Run", "Post");
if (!in_array($type, $typesallowed))
    stderr("{$lang['report_error']}", "{$lang['report_error2']}");
// === still need a second value passed for stuff like hit and run where you need two id's :P
if ((isset($_GET["id_2"])) || (isset($_POST["id_2"]))) {
    $id_2 = ($_GET["id_2"] ? $_GET["id_2"] : $_POST["id_2"]);
    if (!is_valid_id($id_2))
        stderr("{$lang['report_error']}", "{$lang['report_error3']}");
    $id_2b = "&amp;id_2=$id_2";
}

if ((isset($_GET["do_it"])) || (isset($_POST["do_it"]))) {
    $do_it = ($_GET["do_it"] ? $_GET["do_it"] : $_POST["do_it"]);
    if (!is_valid_id($do_it))
     stderr("{$lang['report_error']}", "{$lang['report_error3']}");
    // == make sure the reason is filled out and is set
    $reason = sqlesc($_POST["reason"]);
    if (!$reason)
     stderr("{$lang['report_error']}", "{$lang['report_error4']}");
    // === check if it's been reported already
    $res = sql_query("SELECT id FROM reports WHERE reported_by = $CURUSER[id] AND reporting_what = $id AND reporting_type = '$type'") or sqlerr(__FILE__, __LINE__);
    if (mysql_num_rows($res) != 0)
    stderr("{$lang['report_error5']}", "{$lang['report_error6']} <b>" . str_replace("_" , " ", $type) . "</b> {$lang['report_id']} <b>$id</b>!");
    // === ok it's not been reported yet let's go on
    $dt = sqlesc(time());
    sql_query("INSERT into reports (reported_by, reporting_what, reporting_type, reason, added, 2nd_value) VALUES ($CURUSER[id], '$id', '$type', $reason, $dt, '$id_2')") or sqlerr(__FILE__, __LINE__);
    $mc1->delete_value('new_report_');
    $HTMLOUT .="<table width='650'><tr><td class='colhead'><h1>{$lang['report_success']}</h1></td></tr>" . "<tr><td class='clearalt6' align='center'>{$lang['report_success1']} <b>" . str_replace("_" , " ", $type) . "</b> {$lang['report_id']} <b>$id</b>!<br /><b>{$lang['report_reason']}</b> $reason</td></tr></table>";
    echo stdhead("Reports") . $HTMLOUT . stdfoot();
    die();
    }//=== end do_it
    // === starting main page for reporting all...
    $HTMLOUT .="<form method='post' action='report.php?type=$type$id_2b&amp;id=$id&amp;do_it=1'>
    <table width='650'>
    <tr><td class='colhead' colspan='2'>
    <h1>Report: " . str_replace("_" , " ", $type) . "</h1></td></tr>" . "
    <tr><td class='clearalt6' colspan='2' align='center'>
    <img src='{$INSTALLER09['pic_base_url']}warned.gif' alt='warned' title='Warned' border='0' /> {$lang['report_report']} <b>" . str_replace("_" , " ", $type) . "</b> {$lang['report_id']} <b>$id</b>" . "
    <img src='{$INSTALLER09['pic_base_url']}warned.gif' alt='warned' title='Warned' border='0' /><br />{$lang['report_report1']} <a class='altlink' href='rules.php' target='_blank'>{$lang['report_rules']}</a>?</td></tr>" . "
    <tr><td class='clearalt6' align='right'><b>{$lang['report_reason']}</b></td><td class='clearalt6'><textarea name='reason' cols='70' rows='5'></textarea><br /> [ {$lang['report_req']} ]<br /></td></tr>" . "
    <tr><td class='clearalt6' colspan='2' align='center'><input type='submit' class='button' value='{$lang['report_confirm']}' /></td></tr></table></form>";
echo stdhead("Report") . $HTMLOUT . stdfoot();
die;
?>