<?php
 //== 09 Donation progress
    $progress='';
    $totalfunds_cache = $mc1->get_value('totalfunds_');
    if ($totalfunds_cache === false) {
    $totalfunds_cache =  mysql_fetch_assoc(sql_query("SELECT sum(cash) as total_funds FROM funds"))/* or sqlerr(__FILE__, __LINE__)*/;
    $totalfunds_cache["total_funds"] = (int)$totalfunds_cache["total_funds"];
    $mc1->cache_value('totalfunds_', $totalfunds_cache, $INSTALLER09['expires']['total_funds']);
    }
    $funds_so_far = (int)$totalfunds_cache["total_funds"];
    $totalneeded = 50;    //=== set this to your monthly wanted amount
    $funds_difference = $totalneeded - $funds_so_far;
    $Progress_so_far = number_format($funds_so_far / $totalneeded * 100, 1);
    if($Progress_so_far >= 100)
    $Progress_so_far = 100;
    $HTMLOUT .="<div class='headline'>{$lang['index_donations']}</div><div class='headbody2'><a href='{$INSTALLER09['baseurl']}/donate.php'>
    <img border='0' src='{$INSTALLER09['pic_base_url']}makedonation.gif' alt='Donate' title='Donate'  /></a><br /><br />
    <table align='center' width='140' style='height: 20%;' border='2'><tr>
    <td bgcolor='transparent' align='center' valign='middle' width='$Progress_so_far%'>$Progress_so_far%</td><td bgcolor='grey' align='center' valign='middle'></td></tr></table></div><br />";
    //end
?>