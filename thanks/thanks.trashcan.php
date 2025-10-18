<?php
/* ====================
[BEGIN_COT_EXT]
Hooks=trashcan.api
[END_COT_EXT]
==================== */

declare(strict_types=1);

/**
 * Thanks plugin
 * @package thanks
 * @author Trustmaster & Cotonti team
 * @copyright Copyright (c) 2011-2015 Vladimir Sibirov, 2016-2024 Cotonti team
 * @license BSD
 */


defined('COT_CODE') or die('Wrong URL');

require_once cot_incfile('thanks', 'plug');

// Register restoration table
$trash_types[THANKS_SOURCE] = Cot::$db->thanks;

// Actually no functions are required so far
