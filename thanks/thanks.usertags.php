<?php
/* ====================
[BEGIN_COT_EXT]
Hooks=usertags.main
[END_COT_EXT]
==================== */

declare(strict_types=1);

/**
 * Thanks user tags
 *
 * @package thanks
 * @author Trustmaster & Cotonti team
 * @copyright Copyright (c) 2011-2015 Vladimir Sibirov, 2016-2025 Cotonti team
 * @license BSD
 *
 * @var array $user_data
 */

defined('COT_CODE') or die('Wrong URL');

static $th_lang_loaded = false;

require_once cot_incfile('thanks', 'plug');

if (!$th_lang_loaded) {
	require_once cot_langfile('thanks', 'plug');
	$th_lang_loaded = true;
}

$user_data['user_thanks'] = (int) $user_data['user_thanks'];

$temp_array['THANKS'] = $user_data['user_thanks'];
$temp_array['THANKS_URL'] = thanks_userThanksUrl($user_data['user_id']);
$temp_array['THANKS_TIMES'] = cot_declension($user_data['user_thanks'], 'Times');
