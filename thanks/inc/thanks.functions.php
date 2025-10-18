<?php
/**
 * Thanks main functions
 *
 * @package thanks
 * @author Trustmaster & Cotonti team
 * @copyright Copyright (c) 2011-2015 Vladimir Sibirov, 2016-2024 Cotonti team
 * @license BSD
 */

declare(strict_types=1);

use cot\extensions\ExtensionsService;
use cot\modules\forums\inc\ForumsDictionary;
use cot\modules\page\inc\PageDictionary;
use cot\modules\polls\inc\PollsDictionary;
use cot\plugins\comments\inc\CommentsDictionary;
use cot\plugins\thanks\dto\ThanksRequestDto;
use cot\plugins\thanks\inc\ThanksHelper;
use cot\plugins\thanks\inc\ThanksRepository;
use cot\plugins\thanks\inc\ThanksService;

defined('COT_CODE') or die('Wrong URL');

$extensionsService = ExtensionsService::getInstance();
if (class_exists(PageDictionary::class)) {
    define('THANKS_SOURCE_PAGE', PageDictionary::SOURCE_PAGE);
} else {
    define('THANKS_SOURCE_PAGE', 'page');
}

if (class_exists(ForumsDictionary::class)) {
    define('THANKS_SOURCE_FORUM_POST', ForumsDictionary::SOURCE_POST);
} else {
    define('THANKS_SOURCE_FORUM_POST', 'forumPost');
}

if (class_exists(CommentsDictionary::class)) {
    define('THANKS_SOURCE_COMMENT', CommentsDictionary::SOURCE_COMMENT);
} else {
    define('THANKS_SOURCE_COMMENT', 'comment');
}

if (class_exists(PollsDictionary::class)) {
    define('THANKS_SOURCE_POLL', PollsDictionary::SOURCE_POLL);
} else {
    define('THANKS_SOURCE_POLL', 'poll');
}

const THANKS_SOURCE = 'thanks';

Cot::$db->registerTable('thanks');

// Dependencies
require_once cot_incfile('users', 'module');

require_once cot_langfile('thanks', 'plug');
require_once cot_incfile('thanks', 'plug','resources');

/**
 * Item thanked users list widget for use in templates
 * @param int|numeric-string $sourceId
 */
function thanks_itemWhoThankedWidget(string $source, $sourceId): string
{
    $sourceId = (int) $sourceId;
    if (!ThanksHelper::isEnabled($source) || $sourceId < 1) {
        return '';
    }

    ThanksRequestDto::add($source, $sourceId);

    return ThanksHelper::whoThankedItemWidgetPlaceholder($source, $sourceId);
}

/**
 * Item add thank (like) widget for use in templates
 * @param int|numeric-string $sourceId
 */
function thanks_itemAddThankWidget(string $source, $sourceId): string
{
    $sourceId = (int) $sourceId;
    if (!ThanksHelper::isEnabled($source) || $sourceId < 1) {
        return '';
    }

    ThanksRequestDto::add($source, $sourceId);

    return ThanksHelper::itemAddThankWidgetPlaceholder($source, $sourceId);
}

/**
 * Item thanks (likes) count widget for use in templates
 * @param int|numeric-string $sourceId
 */
function thanks_itemCountWidget(string $source, $sourceId): string
{
    $sourceId = (int) $sourceId;
    if (!ThanksHelper::isEnabled($source) || $sourceId < 1) {
        return '';
    }

    ThanksRequestDto::add($source, $sourceId);

    return ThanksHelper::itemCountWidgetPlaceholder($source, $sourceId);
}

/**
 * User thanks (likes) count widget for use in templates
 * @param int|numeric-string $userId
 * @param int|numeric-string|null $count
 */
function thanks_userCountWidget($userId, $count = null): string
{
    $userId = (int) $userId;
    if ($userId < 1) {
        return '';
    }

    if ($count === null) {
        // @todo использовать такой же виджет как и выше
    }
    $count = (int) $count;

    return ThanksHelper::renderUserCountWidget(['userId' => $userId, 'thanksCount' => $count]);
}

/**
 * Thanks count for item
 * @param int|numeric-string $sourceId
 */
function thanks_count(string $source, $sourceId): int
{
    $sourceId = (int) $sourceId;
    if (!ThanksHelper::isEnabled($source) || $sourceId < 1) {
        return 0;
    }

    $result = ThanksRepository::getCountsBySourceIds($source, [$sourceId]);
    if (empty($result) || empty($result[$source]) || empty($result[$source][$sourceId])) {
        return 0;
    }
    return $result[$source][$sourceId];
}

/**
 * @param int|numeric-string $userId
 */
function thanks_userThanksUrl($userId = 0): string
{
    $userId = (int) $userId;
    $params = ['a' => 'user'];
    if ($userId > 0) {
        $params['id'] = $userId;
    }
    return cot_url('thanks', $params);
}

/**
 * @param int|numeric-string $sourceId
 */
function thanks_itemThanksUrl(string $source, $sourceId): string
{
    $sourceId = (int) $sourceId;
    if (!ThanksHelper::isEnabled($source) || $sourceId < 1) {
        return '';
    }

    return cot_url('thanks', ['a' => 'list', 'source' => $source, 'item' => $sourceId]);
}

function cot_trash_thanks_sync(array $data): void
{
    if (empty($data) || !isset($data['to_user_id'])) {
        return;
    }
    ThanksService::recalculateUserThanks($data['to_user_id']);
}
