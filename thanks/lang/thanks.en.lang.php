<?php
/**
 * Thanks / EN Locale
 *
 * @package thanks
 * @author Trustmaster & Cotonti team
 * @copyright Copyright (c) 2011-2015 Vladimir Sibirov, 2016-2025 Cotonti team
 * @license BSD
 */

declare(strict_types=1);

defined('COT_CODE') or die('Wrong URL');

/**
 * Plugin Info
 */
$L['info_name'] = 'Thanks';
$L['info_desc'] = 'Enable thanks for a user';
$L['info_notes'] = '';

$L['thanks_meta_title'] = 'User thanks';
$L['thanks_meta_desc'] = 'List of registered users with the number of thanks (likes)';

/**
 * Plugin Config
 */
$L['cfg_maxday'] = 'Thanks per day';
$L['cfg_maxday_hint'] = 'Max thanks a user can give a day. "0" - unlimited';
$L['cfg_maxuser'] = 'Daily limit of thanks for a user';
$L['cfg_maxuser_hint'] = 'Max thanks a day a user can give to a particular user. Separately for each recipient. "0" - unlimited';
$L['cfg_maxrowsperpage'] = 'Max thanks displayed per page';
$L['cfg_maxthanked'] = 'Number of users that liked an object';
$L['cfg_maxthanked_hint'] = 'In object lists (posts, comments etc.) "0" - show all, "-1" - disable';
$L['cfg_short'] = 'Short list of thankers';
$L['cfg_short_hint'] = 'Only names (w/o dates)';
$L['cfg_count_last_days'] = 'Show rating list based on last ## days';
$L['cfg_count_last_days_hint'] = 'Set empty or "0" for count all statistics';
$L['cfg_page_on'] = 'Enable for pages';
$L['cfg_forums_on'] = 'Enable for forums posts';
$L['cfg_comments_on'] = 'Enable for comments';
$L['cfg_notifications'] = 'Notifications:';
$L['cfg_notify_by_email'] = 'Send new thanks notification by email';
$L['cfg_notify_from'] = '"Reply to" for notifications by email';
$L['cfg_notify_from_hint'] = 'If empty, '
    . '<a href="' . cot_url('admin', ['m' => 'config', 'n' => 'edit', 'o' => 'core', 'p' => 'main']) . '"> the default value:</a> '
    . '"<strong>' . Cot::$cfg['adminemail'] . '</strong>" will be used';
$L['cfg_notify_by_pm'] = 'Send new thanks notification by pm';

/**
 * Plugin Body
 */
$L['thanks_addNew'] = 'Say thanks!';
$L['thanks_category'] = 'category';
$L['thanks_clear_filters'] = 'Clear filters';
$L['thanks_commentToPage'] = 'Comment to page';
$L['thanks_commentToPoll'] = 'Comment to poll';
$L['thanks_commentTo'] = 'Comment to';
$L['thanks_deleteAllFromUser'] = 'Delete all thanks from {$userName}';
$L['thanks_deleteAllToUser'] = 'Delete all thanks to {$userName}';
$L['thanks_deleteOne'] = 'Delete the thank';
$L['thanks_deleted'] = '{$n} deleted';
$L['thanks_deletedOne'] = 'Thank deleted';
$L['thanks_ensure'] = 'Would you like to thank the user?';
$L['thanks_filterDesc'] = "To view or delete all the user's thanks, select it in the filter";
$L['thanks_for'] = 'for';
$L['thanks_forMe'] = 'Thanks for me';
$L['thanks_forMeDesc'] = 'List of thanks (likes) for me';
$L['thanks_forUser'] = 'Thanks for user';
$L['thanks_forUserDesc'] = 'List of thanks (likes) to the user';
$L['thanks_forums_post'] = 'Forums post';
$L['thanks_from'] = 'from';
$L['thanks_fromUser'] = 'Thanks from user';
$L['thanks_lastThanked'] = 'Last thanked';
$L['thanks_none'] = 'No thanks present';
$L['thanks_notificationBody'] = '{$userName},<br>user {$fromUser} thanked you for {$item}';
$L['thanks_notificationSubject'] = 'You have been thanked!';
$L['thanks_postInTopic'] = 'Post in topic';
$L['thanks_rated'] = 'Thanks';
$L['thanks_ratedForPeriod'] = 'Thanks in given period';
$L['thanks_ratingPeriodDesc'] = 'Rating calculated based on last {$days}.';
$L['thanks_ratingPeriodUsersCountDesc'] = '{$users} got a thanks for this period..';
$L['thanks_title'] = 'Thanks for users';
$L['thanks_title_short'] = 'Thanks';
$L['thanks_thanked'] = 'Thanked';
$L['thanks_thanks'] = 'Thanks';
$L['to'] = 'кому';
$L['thanks_toUser'] = 'to user';
$L['thanks_top'] = 'Top thanks for users';

$L['thanks_declension'] = "thanks,thank";

/**
 * Error Messages
 */
$L['thanks_err_maxday'] = 'Sorry, you can not give any more thanks today';
$L['thanks_err_maxuser'] = 'Sorry, this users can not be thanks today anymore';
$L['thanks_err_item'] = 'Sorry, you can not thank for one object twice';
$L['thanks_err_self'] = 'You can not thank yourself';
$L['thanks_err_banned'] = 'The user is banned';
$L['thanks_err_inactive'] = "User's account is currently inactive";
$L['thanks_err_cantBanned'] = "You can't thank the user. The user is banned";
$L['thanks_err_request'] = 'An error occurred when sending the request. Try refreshing the page and thanking again.';
$L['thanks_err_wrong_parameter'] = 'Wrong parameter';

$L['thanks_done'] = 'You have thanked the author';
