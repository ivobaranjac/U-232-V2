<?php
/**
 *   https://09source.kicks-ass.net:8443/svn/installer09/
 *   Licence Info: GPL
 *   Copyright (C) 2010 Installer09 v.2
 *   A bittorrent tracker source based on TBDev.net/tbsource/bytemonsoon.
 *   Project Leaders: Mindless,putyn,kidvision.
 **/
//made by putyn @tbdev 
require_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'include'.DIRECTORY_SEPARATOR.'bittorrent.php');
require_once(INCL_DIR.'phpzip.php');
dbconn();
loggedinorreturn();
$lang = array_merge( load_language('global'), load_language('messages') );


$INSTALLER09['sub_up_dir'] = "C:/appserv/www/uploadsub";

$action = (isset($_POST["action"]) ? $_POST["action"] : "" );
if ($action == "download"){
$id = isset($_POST["sid"]) ? 0 + $_POST["sid"] : 0; 
if ($id == 0)
stderr("Err","Not a valid id");
else 
{
$res = sql_query("SELECT id, name, filename FROM subtitles WHERE id={$id} ") or sqlerr(__FILE__, __LINE__);
$arr = mysql_fetch_assoc($res);

$ext = (substr($arr["filename"] ,-3));
$fileName = str_replace(array(" ", ".","-") , "_" ,$arr["name"]).'.'.$ext;

$file = $INSTALLER09['sub_up_dir']."/".$arr["filename"]; 
$fileContent = file_get_contents($file);

$newFile = fopen("{$INSTALLER09['sub_up_dir']}/$fileName", "w");
@fwrite($newFile,$fileContent);
@fclose ($newFile); 

$file = array();
$zip = new PHPZip();
$file[] = "{$INSTALLER09['sub_up_dir']}/$fileName";
$fName = "{$INSTALLER09['sub_up_dir']}/".str_replace(array(" ", ".","-") , "_" ,$arr["name"]).".zip" ;
$zip->Zip($file, $fName);
$zip->forceDownload($fName);

@unlink($fName);
@unlink("{$INSTALLER09['sub_up_dir']}/$fileName");
sql_query("UPDATE subtitles SET hits=hits+1 where id={$id}");

}
}else

stderr("Err","No way");
?>