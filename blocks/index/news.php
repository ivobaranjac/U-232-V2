<?php
    //==Installer09 MemCached News
    $news2  = '';
    $adminbutton = '';
    if ($CURUSER['class'] >= UC_STAFF){
    $adminbutton = "<a href='staffpanel.php?tool=news&amp;action=news'>Add / Edit</a>\n";
    }
    $HTMLOUT.="
    <div class='headline'>{$lang['news_title']}<span class='news'>{$adminbutton}</span></div><div class='headbody'>";
    $prefix = 'min5l3ss';
    $news = $mc1->get_value('latest_news_');
    if($news === false ) {
    $res = sql_query("SELECT ".$prefix.".id, ".$prefix.".userid, ".$prefix.".added, ".$prefix.".title, ".$prefix.".body, ".$prefix.".sticky, u.username FROM news AS ".$prefix." LEFT JOIN users AS u ON u.id = ".$prefix.".userid WHERE ".$prefix.".added + ( 3600 *24 *45 ) > ".time()." ORDER BY sticky, ".$prefix.".added DESC LIMIT 10") or sqlerr(__FILE__, __LINE__);
    while ($array = mysql_fetch_assoc($res) ) 
    $news[] = $array;
    $mc1->cache_value('latest_news_', $news, $INSTALLER09['expires']['latest_news']);
    }
    $news_flag = 0;
    if ($news)
    {
    foreach ($news as $array)
    {
    $button='';
    if ($CURUSER['class'] >= UC_STAFF) {
    $hash = md5('the@@saltto66??' . $array['id']. 'add' . '@##mu55y==');
    $button = "
    <div class='news_button'>
    <a href='staffpanel.php?tool=news&amp;action=news&amp;mode=edit&amp;newsid={$array['id']}&amp;returnto=".urlencode($_SERVER['PHP_SELF'])."'>
    <img src='{$INSTALLER09['pic_base_url']}button_edit2.gif' border='0' alt=\"Edit news\"  title=\"Edit news\" /></a>&nbsp;
    <a href='staffpanel.php?tool=news&amp;action=news&amp;mode=delete&amp;newsid={$array['id']}&amp;h=$hash&amp;returnto=".urlencode($_SERVER['PHP_SELF'])."'>
    <img src='{$INSTALLER09['pic_base_url']}del.png' border='0' alt=\"Delete news\" title=\"Delete news\" /></a>
    </div>";
    }
    $HTMLOUT .= "
    <div class='news_space'>";
    if ($news_flag < 2) {
    $HTMLOUT .="
    <div class='newshead'>
    <a href=\"javascript: klappe_news('a".$array['id']."')\">
    <img border=\"0\" src='pic/plus.gif' id=\"pica".$array['id']."\" alt=\"Show/Hide\" />" . " - " .get_date( $array['added'],'DATE') . " - " ."{$array['title']}</a>
    {$button}
    </div></div>";
    $HTMLOUT .="
    <div id=\"ka".$array['id']."\" style=\"display:".($array["sticky"] == "yes" ? "" : "none").";margin-left:20px;margin-top:10px;\"> ".format_comment($array["body"],0)." 
    </div><br /> ";
    $news_flag = ($news_flag + 1);
    }
    else {
    $HTMLOUT .="
    <div class='newshead'>
    <a href=\"javascript: klappe_news('a".$array['id']."')\">
    <img border=\"0\" src='pic/plus.gif' id=\"pica".$array['id']."\" alt=\"Show/Hide\" />" . " - " .get_date( $array['added'],'DATE') . " - " ."{$array['title']}</a>
    {$button}
    </div></div>";
    $HTMLOUT .="
    <div id=\"ka".$array['id']."\" style=\"display:".($array["sticky"] == "yes" ? "" : "none").";margin-left:20px;margin-top:10px;\"> ".format_comment($array["body"],0)." 
    </div><br /> ";
    }
    }
    $HTMLOUT .= "
    </div><br />\n";
    }
    if (empty($news))
    $HTMLOUT .= "We currently have fuck all to say :-P
    </div><br />\n";
    //==End
?>