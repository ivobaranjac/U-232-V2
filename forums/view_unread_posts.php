<?php
/**********************************************************
New 2010 forums that don't suck for TB based sites....
pretty much coded page by page, but coming from a 
history ot TBsourse and TBDev and the many many 
coders who helped develop them over time.
proper credits to follow :)

beta mon aug 2 2010 v0.1
view unread posts

Powered by Bunnies!!!
**********************************************************/

if (!defined('BUNNY_FORUMS')) 
{
	$HTMLOUT ='';
	$HTMLOUT .= '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
        <html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
        <head>
        <meta http-equiv="content-type" content="text/html; charset=iso-8859-1" />
        <title>ERROR</title>
        </head><body>
        <h1 style="text-align:center;">ERROR</h1>
        <p style="text-align:center;">How did you get here? silly rabbit Trix are for kids!.</p>
        </body></html>';
	echo $HTMLOUT;
	exit();
}

//=== start page
$colour = $topicpoll = $topic_status_image = '';
$links = '<span style="text-align: center;"><a class="altlink" href="forums.php">Main Forums</a> |  '.$mini_menu.'<br /><br /></span>';

	$HTMLOUT .= '<h1>Unread posts since your last visit</h1>'.$links;

	$time = $readpost_expiry;
	
	$res_count = sql_query('SELECT t.id, t.last_post
	FROM topics AS t
	LEFT JOIN posts AS p ON t.last_post = p.id
	LEFT JOIN forums as f ON f.id = t.forum_id
	WHERE '.($CURUSER['class'] < UC_STAFF ? 'p.status = \'ok\' AND t.status = \'ok\' AND' : 
	($CURUSER['class'] < $min_delete_view_class ? 'p.status != \'deleted\' AND t.status != \'deleted\'  AND' : '')).' f.min_class_read <= '.$CURUSER['class'].' AND p.added > '.$time);

	//=== lets do the loop / Check if post is read / get count there must be a beter way to do this lol
	$count = 0;
	while ($arr_count = mysql_fetch_assoc($res_count))
    {
      $res_post_read = sql_query('SELECT last_post_read FROM read_posts WHERE user_id='.$CURUSER['id'].' AND topic_id='.$arr_count['id']);
      $arr_post_read = mysql_fetch_row($res_post_read);
		if ($arr_post_read[0] < $arr_count['last_post'])
		{
		++$count;
		}
	} 

	//=== nothing here? kill the page
	if ($count == 0)
	{
	$HTMLOUT .='<br /><br /><table border="0" cellspacing="10" cellpadding="10" width="400px">
		<tr><td class="forum_head_dark"align="center">
		No unread posts
		</td></tr>
		<tr><td class="three"align="center">
		You are up to date on all topics.<br /><br />
		</td></tr></table><br /><br />';
	
	$HTMLOUT .= $links.'<br />';	
	}	
	else
	{

	 //=== get stuff for the pager
	$page = isset($_GET['page']) ? (int)$_GET['page'] : 0;
	$perpage = isset($_GET['perpage']) ? (int)$_GET['perpage'] : 20;

	list($menu, $LIMIT) = pager_new($count, $perpage, $page, 'forums.php?action=view_unread_posts'.(isset($_GET['perpage']) ? '&amp;perpage='.$perpage : '')); 
	
	//=== top and bottom stuff
	$the_top_and_bottom =  '<br /><table border="0" cellspacing="0" cellpadding="0" width="90%">
		<tr><td class="three" align="center" valign="middle">'.(($count > $perpage) ? $menu : '').'</td>
		</tr></table>';
			

//=== main huge query: 
$res_unread = sql_query('SELECT t.id AS topic_id, t.topic_name AS topic_name, t.last_post, t.post_count, 
t.views, t.topic_desc, t.locked, t.sticky, t.poll_id, t.forum_id, t.rating_sum, t.num_ratings, t.status, 
f.name AS forum_name, f.description AS forum_desc,
p.post_title, p.body, p.icon, 
u.id, u.username, u.class, u.donor, u.suspended, u.warned, u.enabled, u.chatpost, u.leechwarn, u.pirate, u.king
FROM topics AS t
LEFT JOIN posts AS p ON t.last_post = p.id
LEFT JOIN forums as f ON f.id = t.forum_id
LEFT JOIN users AS u on u.id = t.user_id
WHERE '.($CURUSER['class'] < UC_STAFF ? 'p.status = \'ok\' AND t.status = \'ok\' AND' : 
($CURUSER['class'] < $min_delete_view_class ? 'p.status != \'deleted\' AND t.status != \'deleted\'  AND' : '')).' f.min_class_read <= '.$CURUSER['class'].' AND p.added > '.$time.'
ORDER BY t.last_post DESC '.$LIMIT);

$HTMLOUT .= $the_top_and_bottom.'<table border="0" cellspacing="5" cellpadding="10" width="90%">
		<tr>
		<td align="center" valign="middle" class="forum_head_dark" width="10"><img src="pic/forums/topic.gif" alt="topic" title="topic" /></td>
		<td align="center" valign="middle" class="forum_head_dark" width="10"><img src="pic/forums/topic_normal.gif" alt="Thread Icon" title="Thread Icon" /></td>
		<td align="left" class="forum_head_dark">New Posts!</td>

		<td class="forum_head_dark" align="center" width="10">Replies</td>
		<td class="forum_head_dark" align="center" width="10">Views</td>
		<td align="center" class="forum_head_dark">Started By</td>
		</tr>';
		
	//=== ok let's show the posts...
	while ($arr_unread = mysql_fetch_assoc($res_unread))
    {
      $res_post_read = sql_query('SELECT last_post_read FROM read_posts WHERE user_id='.$CURUSER['id'].' AND topic_id='.$arr_unread['topic_id']);
      $arr_post_read = mysql_fetch_row($res_post_read);
	
		if ($arr_post_read[0] < $arr_unread['last_post'])
		{
		//=== change colors
		$colour = (++$colour)%2;
		$class = ($colour == 0 ? 'one' : 'two');

		//=== topic status
		$topic_status = $arr_unread['status'];
		
		switch ($topic_status)
		{
		case 'ok':
		$topic_status_image = '';
		break;
		case 'recycled':
		$topic_status_image = '<img src="pic/forums/recycle_bin.gif" alt="Recycled" title="this thread is currently in the recycle-bin" />';
		break;
		case 'deleted':
		$topic_status_image = '<img src="pic/forums/delete_icon.gif" alt="Deleted" title="this thread is currently deleted" />';
		break;		
		}
		
        $locked = $arr_unread['locked'] == 'yes';
        $sticky = $arr_unread['sticky'] == 'yes';
        $topic_poll = $arr_unread['poll_id'] > 0;		
		
		$first_unread_poster = sql_query('SELECT added FROM posts WHERE topic_id='.$arr_unread['topic_id'].' ORDER BY id ASC LIMIT 1');
        $first_unread_poster_arr = mysql_fetch_row($first_unread_poster);
		
	$thread_starter = ($arr_unread['username'] !== '' ? print_user_stuff($arr_unread) : 'Lost ['.$arr_unread['id'].']').'<br />'.get_date($first_unread_poster_arr[0],'');
	
        $topicpic = ($arr_unread['post_count']  < 30 ? ($locked ? 'lockednew'  : 'topicnew') : ($locked ? 'lockednew'  : 'hot_topic_new'));
		$rpic = ($arr_unread['num_ratings'] != 0 ? ratingpic_forums(ROUND($arr_unread['rating_sum'] / $arr_unread['num_ratings'], 1)) :  '');
		
		$did_i_post_here = sql_query('SELECT user_id FROM posts WHERE user_id='.$CURUSER['id'].' AND topic_id='.$arr_unread['topic_id']);
        $posted = (mysql_num_rows($did_i_post_here) > 0 ? 1 : 0);
		
		$sub = sql_query('SELECT user_id FROM subscriptions WHERE user_id='.$CURUSER['id'].' AND topic_id='.$arr_unread['topic_id']);
        $subscriptions = (mysql_num_rows($sub) > 0 ? 1 : 0);
	
        $icon = ($arr_unread['icon'] == '' ? '<img src="pic/forums/topic_normal.gif" alt="Topic" title="Topic" />' : '<img src="pic/smilies/'.$arr_unread['icon'].'.gif" alt="'.$arr_unread['icon'].'" title="'.$arr_unread['icon'].'" />');
        $first_post_text = tool_tip(' <img src="pic/forums/mg.gif" height="14" alt="Preview" title="Preview" />', format_comment($arr_unread['body']), 'Last Post Preview');
        
        $topic_name = ($sticky ? '<img src="pic/forums/pinned.gif" alt="Pinned" title="Pinned" /> ' : ' ').($topicpoll ? '<img src="pic/forums/poll.gif" alt="Poll" title="Poll" /> ' : ' '). '
        		<a class="altlink" href="?action=view_topic&amp;topic_id='.$arr_unread['topic_id'].'" title="First post in thread">'.htmlentities($arr_unread['topic_name'], ENT_QUOTES).'</a> 
				<a class="altlink" href="forums.php?action=view_topic&amp;topic_id='.$arr_unread['topic_id'].'&amp;page=0#'.$arr_post_read[0].'" title="First unread post in this thread"><img src="pic/forums/last_post.gif" alt="last post" title="last post" /></a> 
        		'.($posted ? '<img src="pic/forums/posted.gif" alt="Posted" title="Posted" /> ' : ' ').($subscriptions ? '<img src="pic/forums/subscriptions.gif" alt="subscribed" title="subscribed" /> ' : ' ').
				' <img src="pic/forums/new.gif" alt="New post in topic!" title="New post in topic!" />';
		
		//=== print here
		$HTMLOUT .= '<tr>
		<td class="'.$class.'" align="center"><img src="pic/forums/'.$topicpic.'.gif" alt="topic" title="topic" /></td>
		<td class="'.$class.'" align="center">'.$icon.'</td>
		<td align="left" valign="middle" class="'.$class.'">



		<table border="0" cellspacing="0" cellpadding="0">
		<tr>
		<td  class="'.$class.'" align="left">'.$topic_name.$first_post_text.' 
		 [ <a class="altlink" href="?action=clear_unread_post&amp;topic_id='.$arr_unread['topic_id'].'&amp;last_post='.$arr_unread['last_post'].'" title="Remove this topic from your unread list. To remove all, use the: Mark All As Read link above.">Remove</a> ] '.$topic_status_image.'</td>
		<td class="'.$class.'" align="right">'.$rpic.'</td>
		</tr>
		</table>
		'.($arr_unread['topic_desc'] !== '' ? '&#9658; <span style="font-size: x-small;">'.htmlentities($arr_unread['topic_desc'], ENT_QUOTES).'</span>' : '').'  
		<hr />in: <a class="altlink" href="forums.php?action=view_forum&amp;forum_id='.$arr_unread['forum_id'].'">'.htmlentities($arr_unread['forum_name'], ENT_QUOTES).'</a>
		'.($arr_unread['topic_desc'] !== '' ? ' [ <span style="font-size: x-small;">'.htmlentities($arr_unread['forum_desc'], ENT_QUOTES).'</span> ]' : '').'

</td>
		
		<td align="center" class="'.$class.'">'.number_format($arr_unread['post_count'] - 1).'</td>
		<td align="center" class="'.$class.'">'.number_format($arr_unread['views']).'</td>
		<td align="center" class="'.$class.'">'.$thread_starter.'</td>
		</tr>';
		}
		
	} 
	
	$HTMLOUT .= '</table>'.$the_top_and_bottom.'<br /><br />'.$links.'<br />';
	}
?>