<?php
/* ====================
[BEGIN_COT_EXT]
Hooks=users.delete
[END_COT_EXT]
==================== */

declare(strict_types=1);

/**
 * Thanks on user delete
 *
 * @package thanks
 * @author Trustmaster & Cotonti team
 * @copyright Copyright (c) 2011-2015 Vladimir Sibirov, 2016-2026 Cotonti team
 * @license BSD
 *
 * @var int $id User ID
 */

use cot\plugins\thanks\inc\ThanksService;

$thanksDeleteCondition = ['to_user_id = :userId OR from_user_id = :userId'];
$thanksDeleteParams = ['userId' => $id];

ThanksService::deleteThankByCondition($thanksDeleteCondition, $thanksDeleteParams);
