<?php
/* ====================
[BEGIN_COT_EXT]
Hooks=standalone
[END_COT_EXT]
==================== */

/**
 * Thanks main script
 *
 * @package thanks
 * @copyright Copyright (c) Vladimir Sibirov
 * @license BSD
 */

defined('COT_CODE') or die('Wrong URL');

$ext = cot_import('ext', 'G', 'ALP');
$item = cot_import('item', 'G', 'INT');
$user = cot_import('user', 'G', 'INT');
$d = cot_import('d', 'G', 'INT'); // pagenum
$days = cot_import('days', 'G', 'INT'); // limit top rating to last N days statistic
$s = strtolower(cot_import('s', 'G', 'ALP')); // order field name without 'th_'
$w = strtoupper(cot_import('w', 'G', 'ALP', 4)); // order way (asc, desc)

$count_last_days = $days ? $days : $cfg['plugin']['thanks']['count_last_days'];
$count_last_days = is_numeric($count_last_days) ? ceil($count_last_days) : 0;

$maxrowsperpage = cot_import($cfg['plugin']['thanks']['maxrowsperpage'], 'D', 'INT');
$maxrowsperpage = $maxrowsperpage ? $maxrowsperpage : $cfg['maxrowsperpage'];

if ($a == 'thank' && !empty($ext) && $item > 0)
{
	if ($ext == 'page')
	{
		require_once cot_incfile('page', 'module');
		$res = cot::$db->query("SELECT page_ownerid FROM $db_pages WHERE page_id = $item");
	}
	else if ($ext == 'forums')
	{
		require_once cot_incfile('forums', 'module');
		$res = cot::$db->query("SELECT fp_posterid FROM $db_forum_posts WHERE fp_id = $item");
	}
	elseif ($ext == 'comments')
	{
		require_once cot_incfile('comments', 'plug');
		$res = cot::$db->query("SELECT com_authorid FROM $db_com WHERE com_id = $item");
	}
	else
	{
		$res = false;
	}
	if ($res && $res->rowCount() == 1 && $usr['auth_write'])
	{
		$user = $res->fetchColumn();
	}
	else
	{
		$ext['status'] = '400 Bad Request';
		cot_die();
	}

	$status = thanks_check($user, $usr['id'], $ext, $item);
	switch ($status)
	{
		case THANKS_ERR_MAXDAY:
			header('403 Forbidden');
			cot_error('thanks_err_maxday');
			break;
		case THANKS_ERR_MAXUSER:
			header('403 Forbidden');
			cot_error('thanks_err_maxuser');
			break;
		case THANKS_ERR_ITEM:
			header('403 Forbidden');
			cot_error('thanks_err_item');
			break;
		case THANKS_ERR_NONE:
			thanks_add($user, $usr['id'], $ext, $item);
			cot_message('thanks_done');
			break;
	}
	$t = new XTemplate(cot_tplfile('thanks.add', 'plug'));
	$t->assign(array(
		'THANKS_BACK_URL' => $_SERVER['HTTP_REFERER']
	));
//	cot_display_messages($t);
	cot_redirect($_SERVER['HTTP_REFERER']);
}
elseif ($user > 0)
{
	// List all user's thanks here
	require_once cot_incfile('page', 'module');
	require_once cot_incfile('forums', 'module');
	if (cot_plugin_active('comments'))
	{
		require_once cot_incfile('comments', 'plug');
		$thanks_join_columns = ", com.*, pag2.page_alias AS p2_alias, pag2.page_id AS p2_id, pag2.page_cat AS p2_cat, pag2.page_title AS p2_title";
		$thanks_join_tables = "LEFT JOIN $db_com AS com ON t.th_ext = 'comments' AND t.th_item = com.com_id
			LEFT JOIN $db_pages AS pag2 ON com.com_area = 'page' AND com.com_code = pag2.page_id";
	}

	list($pg_thanks, $d_thanks, $durl_thanks) = cot_import_pagenav('d', $maxrowsperpage);

	$totalitems = cot::$db->query("SELECT COUNT(*) FROM $db_thanks WHERE th_touser = $user")->fetchColumn();

	$res = cot::$db->query("SELECT t.*, pag.page_alias, pag.page_title, pag.page_cat, ft.ft_title, p.fp_cat, u.user_name $thanks_join_columns
		FROM $db_thanks AS t
			LEFT JOIN $db_users AS u ON t.th_fromuser = u.user_id
			LEFT JOIN $db_pages AS pag ON t.th_ext = 'page' AND t.th_item = pag.page_id
			LEFT JOIN $db_forum_posts AS p ON t.th_ext = 'forums' AND t.th_item = p.fp_id
				LEFT JOIN $db_forum_topics AS ft ON p.fp_id > 0 AND p.fp_topicid = ft.ft_id
			$thanks_join_tables
		WHERE th_touser = $user
		ORDER BY th_date DESC
		LIMIT $d_thanks, {$maxrowsperpage}");
	foreach ($res->fetchAll() as $row)
	{
		$t->assign(array(
				'THANKS_ROW_ID' => $row['th_id'],
				'THANKS_ROW_DATE' => cot_date('datetime_medium', cot_date2stamp($row['th_date'], 'Y-m-d H:i:s')),
				'THANKS_ROW_FROM_URL' => cot_url('users', 'm=details&id='.$row['th_fromuser'].'&u='.urlencode($row['user_name'])),
				'THANKS_ROW_FROM_NAME' => htmlspecialchars($row['user_name'])
			));
		if (!empty($row['com_author']))
		{
			// For a comment
			$urlp = empty($row['p2_alias']) ? array('c' => $row['p2_cat'], 'id' => $row['p2_id']) : array('c' => $row['p2_cat'], 'al' => $row['p2_alias']);
			$t->assign(array(
				'THANKS_ROW_URL' => cot_url($row['com_area'], $urlp, '#c' . $row['th_item']),
				'THANKS_ROW_CAT_TITLE' => htmlspecialchars($structure['page'][$row['p2_cat']]['title']),
				'THANKS_ROW_CAT_URL' => cot_url('page', 'c='.$row['p2_cat']),
				'THANKS_ROW_TITLE' => $L['comments_comment'] . ': ' . htmlspecialchars($row['p2_title'])
			));
		}
		elseif (!empty($row['page_title']))
		{
			// For a page
			$t->assign(array(
				'THANKS_ROW_URL' => empty($row['page_alias']) ? cot_url('page', 'c='.$row['page_cat'].'&id='.$row['th_item']) : cot_url('page', 'c='.$row['page_cat'].'&al='.$row['page_alias']),
				'THANKS_ROW_CAT_TITLE' => htmlspecialchars($structure['page'][$row['page_cat']]['title']),
				'THANKS_ROW_CAT_URL' => cot_url('page', 'c='.$row['page_cat']),
				'THANKS_ROW_TITLE' => htmlspecialchars($row['page_title'])
			));
		}
		elseif (!empty($row['ft_title']))
		{
			// For a post
			$t->assign(array(
				'THANKS_ROW_URL' => cot_url('forums', 'm=posts&id='.$row['th_item']),
				'THANKS_ROW_CAT_TITLE' => htmlspecialchars($structure['forums'][$row['fp_cat']]['title']),
				'THANKS_ROW_CAT_URL' => cot_url('forums', 'm=topics&s='.$row['fp_cat']),
				'THANKS_ROW_TITLE' => htmlspecialchars($row['ft_title'])
			));
		}
		$t->parse('MAIN.THANKS_ROW');
	}

	$name = $user == $usr['id'] ?  $usr['name'] : cot::$db->query("SELECT user_name FROM $db_users WHERE user_id = $user")->fetchColumn();

	$t->assign(array(
		'THANKS_USER_NAME' => htmlspecialchars($name),
		'THANKS_USER_URL' => cot_url('users', 'm=details&id='.$user.'&u='.$name),
		'THANKS_USER_TOTAL' => $L['thanks_thanked'] . ' ' .cot_declension($totalitems, 'Times')
	));

	$pagenav = cot_pagenav('plug','e=thanks&user='.$user, $d_thanks, $totalitems, $maxrowsperpage);
	$t->assign(array(
		'PAGEPREV' => $pagenav['prev'],
		'PAGENEXT' => $pagenav['next'],
		'PAGENAV' => $pagenav['main']
	));
}
else
{
	// Top thanked users
	list($pg_thanks, $d_thanks, $durl_thanks) = cot_import_pagenav('d', $maxrowsperpage);

	$t = new XTemplate(cot_tplfile('thanks.top', 'plug'));

	$filters = array();

	// cuts rating to last N days
	if ($count_last_days) $filters[] = "t.th_date >= DATE_SUB(CURRENT_DATE, INTERVAL $count_last_days DAY)";
	$where = (sizeof($filters)) ? 'WHERE ' . implode(' AND ', $filters) : '';

	// gets «thanks» totals for user
	if ($cfg['plugin']['thanks']['show_totals'])
	{
		$show_totals = true;
		$add_totals_column = 'th.total AS th_total,';
		$add_totals_data = "LEFT JOIN (SELECT
			COUNT(jt.th_touser) AS total,
			jt.th_touser
		FROM $db_thanks AS jt GROUP BY jt.th_touser) AS th
		ON t.th_touser = th.th_touser";
	}
	$totalitems = cot::$db->query("SELECT COUNT(DISTINCT t.th_touser) FROM $db_thanks as t $where")->fetchColumn();

	$order_col = $s ? $s : 'count';
	$order_mode = $w ? $w : 'DESC';
	$res = cot::$db->query("SELECT COUNT(t.th_touser) AS th_count,
		t.th_touser,
		$add_totals_column
		u.*
		FROM $db_thanks AS t
		LEFT JOIN $db_users AS u ON t.th_touser = u.user_id
		$add_totals_data
		$where
		GROUP BY th_touser
		ORDER BY th_$order_col $order_mode
		LIMIT $d_thanks, $maxrowsperpage");
	$num = $d_thanks + 1;

	// make column header title with sorting mode links if needed
	$rated_msg = $count_last_days ? 'thanks_rated_for_period' : 'thanks_rated';
	$t->assign(array(
		'THANKS_COUNT' => $show_totals ? thanks_rating_sort_link($L[$rated_msg], 'count') : $L[$rated_msg],
		'THANKS_TOTAL' => $show_totals ? thanks_rating_sort_link($L['Total'], 'total') : $L['Total']
	));

	$t->assign(array(
		'THANKS_TOTAL_USERS' => cot_rc('thanks_totalusers', array('users' => cot_declension($totalitems, 'Members'))),
		'THANKS_THANKED_USERS' =>  cot_rc('thanks_users_thanked', array('users' => cot_declension($totalitems, 'Members'))),
		'THANKS_RATING_INFO' => cot_rc('thanks_shortlist', array('days' => cot_declension($count_last_days, 'Days')))
	));

	foreach ($res->fetchAll() as $row)
	{
		$t->assign(cot_generate_usertags($row, 'THANKS_ROW_'));
		$t->assign(array(
			'THANKS_ROW_NUM' => $num,
			'THANKS_ROW_TOTALCOUNT' => $row['th_count'],
			'THANKS_ROW_URL' => cot_url('plug', 'e=thanks&user='.$row['user_id']),
		));
		if ($show_totals) $t->assign('THANKS_ROW_USERTOTAL', $row['th_total']);
		$t->parse('MAIN.THANKS_ROW');
		$num++;
	}

	$pagenav = cot_pagenav('thanks', array('s' => $s, 'w' => $w), $d_thanks, $totalitems, $maxrowsperpage);
	$t->assign(array(
		'PAGEPREV' => $pagenav['prev'],
		'PAGENEXT' => $pagenav['next'],
		'PAGENAV' => $pagenav['main']
	));
}


