<?php
/* ====================
[BEGIN_COT_EXT]
Hooks=item.delete
[END_COT_EXT]
==================== */

declare(strict_types=1);

/**
 * Thanks plugin
 * Delete item handler
 *
 * @package thanks
 * @author Cotonti team
 * @copyright (c) 2016-2025 Cotonti team
 * @license BSD
 *
 * @var string $source
 * @var int|string $sourceId
 * @var int $deletedToTrashcanId
 */

use cot\plugins\thanks\inc\ThanksService;

defined('COT_CODE') or die('Wrong URL');

if (empty($source) || empty($sourceId)) {
    return;
}

$thanksDeleteCondition = ['source = :source AND source_id = :sourceId'];
$thanksDeleteParams = ['source' => $source, 'sourceId' => $sourceId];

ThanksService::deleteThankByCondition($thanksDeleteCondition, $thanksDeleteParams, $deletedToTrashcanId);