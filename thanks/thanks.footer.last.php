<?php
/* ====================
[BEGIN_COT_EXT]
Hooks=footer.last
[END_COT_EXT]
==================== */

declare(strict_types=1);

/**
 * Thanks replace placeholders with widgets
 *
 * @package thanks
 * @author Trustmaster & Cotonti team
 * @copyright Copyright (c) 2011-2015 Vladimir Sibirov, 2016-2024 Cotonti team
 * @license BSD
 */

/**
 * Надо в виджет передавать автора поста, чтобы посчитать кого пользователь благодарил сегодня и учесть ограничения
 * Количество благодарностей от пользователя сегодня и количество благодарностей сегодня авторам переданных публикаций можно сохраить в JS и
 * обрабатывать в хендлере ссылки "Сказать спасибо"
 *
 * списки sourceId для каждого source списки авторов можно получать из DTO
 */

use cot\plugins\thanks\dto\ThanksRequestDto;
use cot\plugins\thanks\inc\ThanksHelper;
use cot\plugins\thanks\inc\ThanksRepository;
use cot\services\ItemService;

defined('COT_CODE') or die('Wrong URL');

if (defined('COT_ADMIN') || ThanksRequestDto::getRequestedItems() === []) {
    return;
}

$thanksAuthWrite = cot_auth('plug', 'thanks', 'W');

$lastThankedWidgets = [];
$addThankWidgets = [];
$thankCountWidgets = [];
$lastThankedLimit = (int) Cot::$cfg['plugin']['thanks']['maxthanked'];
foreach (ThanksRequestDto::getRequestedItems() as $source => $sourceIds) {
    $isEnabled = ThanksHelper::isEnabled($source);

    $thanksByCurrentUser = [];
    $lastThankedData = [];
    $thanksCounts = [];
    $itemsForWidgets = [];

    if ($isEnabled) {
        // Items IDs which current user thanked already
        $thanksByCurrentUser = ThanksRepository::thankedByUserId($source, $sourceIds, Cot::$usr['id']);
        /**
         * @todo как вариант выбирать всех, и выводить в постах $lastThankedLimit, а остальных попапом
         * @see \cot\plugins\thanks\controllers\MainController::newAction()
         */
        $lastThankedData = ThanksRepository::getBySourceIds($source, $sourceIds, $lastThankedLimit);
        $thanksCounts = ThanksRepository::getCountsBySourceIds($source, $sourceIds);
        $itemsForWidgets = ItemService::getInstance()->getItems($source, $sourceIds);
    }

    foreach ($sourceIds as $sourceId) {
        $placeHolder = ThanksHelper::whoThankedItemWidgetPlaceholder($source, $sourceId);
        $item = $itemsForWidgets[$sourceId] ?? null;
        if (!$isEnabled || !$item || (empty($thanksCounts[$sourceId]) && Cot::$usr['id'] === $item->authorId)) {
            $lastThankedWidgets[$placeHolder] = '';
        } else {
            $data = [
                'source' => $source,
                'sourceId' => $sourceId,
                'thanksCount' => $thanksCounts[$sourceId] ?? 0,
                'data' => $lastThankedData[$sourceId] ?? [],
            ];
            $lastThankedWidgets[$placeHolder] = ThanksHelper::renderWhoThankedItemWidget($data);
        }

        // Say thank (like)
        $placeHolder = ThanksHelper::itemAddThankWidgetPlaceholder($source, $sourceId);
        // Do not hide the thanks button if the limit of thanks from the current user or to recipient is exceeded. It will be validated on JS.
        // @todo Проверять что пользователь не в бане
        $canThank = $thanksAuthWrite
            && (!isset($thanksByCurrentUser) || !in_array($sourceId, $thanksByCurrentUser, true))
            && $item !== null
            && $item->authorId !== Cot::$usr['id'];

        if (!$isEnabled || !$canThank) {
            $addThankWidgets[$placeHolder] = '';
        } else {
            $addThankWidgets[$placeHolder] = ThanksHelper::renderAddThankWidget([
                'url' => cot_url('thanks', ['a' => 'new', 'source' => $source, 'item' => $sourceId]),
                'source' => $source,
                'sourceId' => $sourceId,
                'toUserId' => $item->authorId,
            ]);
        }

        $placeHolder = ThanksHelper::itemCountWidgetPlaceholder($source, $sourceId);
        if (!$isEnabled) {
            $thankCountWidgets[$placeHolder] = '';
        } else {
            $thankCountWidgets[$placeHolder] = ThanksHelper::renderItemCountWidget([
                'source' => $source,
                'sourceId' => $sourceId,
                'thanksCount' => $thanksCounts[$sourceId] ?? 0,
            ]);
        }
    }
}

$buffer = ob_get_contents();
ob_clean();
$buffer = str_replace(array_keys($lastThankedWidgets), array_values($lastThankedWidgets), $buffer);
$buffer = str_replace(array_keys($addThankWidgets), array_values($addThankWidgets), $buffer);
$buffer = str_replace(array_keys($thankCountWidgets), array_values($thankCountWidgets), $buffer);
echo $buffer;
