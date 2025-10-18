<?php
/**
 * @package thanks
 * @author Trustmaster & Cotonti team
 * @copyright Copyright (c) 2011-2015 Vladimir Sibirov, 2016-2024 Cotonti team
 * @license BSD
 */

declare(strict_types=1);

namespace cot\plugins\thanks\inc;

use Cot;
use cot\dto\ItemDto;
use XTemplate;

class ThanksHelper
{
   public static function whoThankedItemWidgetPlaceholder(string $source, int $sourceId): string
   {
       return "[thanksItemWhoThankedWidget source={$source} sourceId={$sourceId}]";
   }

    public static function itemAddThankWidgetPlaceholder(string $source, int $sourceId): string
    {
        return "[thanksAddThankWidget source={$source} sourceId={$sourceId}]";
    }

    public static function itemCountWidgetPlaceholder(string $source, int $sourceId): string
    {
        return "[thanksItemCount source={$source} sourceId={$sourceId}]";
    }

    /**
     * @param array{source: string, sourceId: int, thanksCount: int, data: list<array<string, mixed>>} $data
     */
    public static function renderWhoThankedItemWidget(array $data): string
    {
        $t = new XTemplate(cot_tplfile(['thanks.who-thanked-item', $data['source']], 'plug'));

        $t->assign([
            'SOURCE' => $data['source'],
            'SOURCE_ID' => $data['sourceId'],
            'THANKS_COUNT' => $data['thanksCount'],
            'THANKS_LAST_COUNT' => count($data['data']),
            'THANKS_LIST_URL' => thanks_itemThanksUrl($data['source'], (int) $data['sourceId']),
            'IS_AJAX' => COT_AJAX,
        ]);

        $number = 1;
        foreach ($data['data'] as $row) {
            $thanksRowTimestamp = strtotime($row['created_at']);
            $thanksRowDate = cot_date('date_full', $thanksRowTimestamp);
            $t->assign([
                'USER_ROW_NUMBER' => $number,
                'USER_ROW_THANK_TIMESTAMP' => $thanksRowTimestamp,
                'USER_ROW_THANK_DATE' => $thanksRowDate,
            ]);
            $t->assign(cot_generate_usertags($row, 'USER_ROW_'));
            $t->parse('MAIN.USER_ROW');
            $number++;
        }

        $t->parse('MAIN');

        return $t->text('MAIN');
    }

    /**
     * @param array{source: string, sourceId: int, toUserId: int, url: string} $data
     */
    public static function renderAddThankWidget(array $data): string
    {
        $resourceStrings = [
            'thanks_addThank_' . $data['source'] . '_' . $data['sourceId'],
            'thanks_addThank_' . $data['source']
        ];

        foreach ($resourceStrings as $resourceString) {
            if (isset(Cot::$R[$resourceString])) {
                return cot_rc($resourceString, $data);
            }
        }

        return cot_rc('thanks_addThank', $data);
    }

    /**
     * @param array{source: string, sourceId: int, thanksCount: int} $data
     */
    public static function renderItemCountWidget(array $data): string
    {
        global $Ls;

        $t = new XTemplate(cot_tplfile(['thanks.item-count', $data['source']], 'plug'));

        $t->assign([
            'SOURCE' => $data['source'],
            'SOURCE_ID' => $data['sourceId'],
            'THANKS_COUNT' => $data['thanksCount'],
            'THANKS_TIMES' => cot_declension($data['thanksCount'], $Ls['Times']),
            'IS_AJAX' => COT_AJAX,
        ]);

        $t->parse('MAIN');

        return $t->text('MAIN');
    }

    /**
     * @param array{userId: int, thanksCount: int} $data
     */
    public static function renderUserCountWidget(array $data): string
    {
        global $Ls;

        $t = new XTemplate(cot_tplfile(['thanks.user-count'], 'plug'));

        $t->assign([
            'USER_ID' => $data['userId'],
            'THANKS_COUNT' => $data['thanksCount'],
            'THANKS_COUNT_TIMES' => cot_declension($data['thanksCount'], $Ls['Times']),
            'THANKS_URL' => thanks_userThanksUrl($data['userId']),
            'IS_AJAX' => COT_AJAX,
        ]);

        $t->parse('MAIN');

        return $t->text('MAIN');
    }

    /**
     * @param list<int> $ids
     * @return list<int>
     */
    public static function prepareIds(array $ids): array
    {
        $result = [];
        foreach ($ids as $id) {
            $id = (int) $id;
            if ($id > 0) {
                $result[] = $id;
            }
        }
        return array_unique($result);
    }

    public static function isEnabled(string $source): bool
    {
        if (
            ($source === THANKS_SOURCE_PAGE && !Cot::$cfg['plugin']['thanks']['page_on'])
            || ($source === THANKS_SOURCE_COMMENT && !Cot::$cfg['plugin']['thanks']['comments_on'])
            || ($source === THANKS_SOURCE_FORUM_POST && !Cot::$cfg['plugin']['thanks']['forums_on'])
        ) {
            return false;
        }

        $result = true;

        /* === Hook === */
        foreach (cot_getextplugins('thanks.isEnabled') as $pl) {
            include $pl;
        }

        return $result;
    }

    /**
     * @param ?\cot\dto\ItemDto $item If NULL array with empty tags will be returned
     */
    public static function generateItemTags(?ItemDto $item, $prefix = 'ITEM_'): array
    {
        return [
            $prefix . 'SOURCE' => $item ? $item->source : '',
            $prefix . 'SOURCE_ID' => $item ? $item->id : null,
            $prefix . 'TITLE' => $item ? htmlspecialchars($item->title) : '',
            $prefix . 'HTML_TITLE' => $item ? $item->getTitleHtml() : '',
            $prefix . 'TYPE_TITLE' => $item ? $item->typeTitle : '',
            $prefix . 'DESCRIPTION' => $item ? $item->description : '',
            $prefix . 'URL' => $item ? $item->url : '',
            $prefix . 'CATEGORY' => $item ? $item->categoryCode : '',
            $prefix . 'CATEGORY_URL' => $item ? $item->categoryUrl : '',
            $prefix . 'CATEGORY_TITLE' => $item ? htmlspecialchars($item->categoryTitle) : '',
        ];
    }

    public static function getPerPage(): int
    {
        return !empty(Cot::$cfg['plugin']['thanks']['maxrowsperpage'])
            ? (int) Cot::$cfg['plugin']['thanks']['maxrowsperpage']
            : (int) Cot::$cfg['maxrowsperpage'];
    }

    public static function deleteUrl(int $id, string $backUrl = ''): string
    {
        $back = !empty($backUrl) ? base64_encode($backUrl) : null;

        return cot_url(
            'thanks',
            ['n' => 'control', 'a' => 'delete', 'id' => $id, 'back' => $back]
        );
    }

    public static function deleteFromUserUrl(int $userId, string $backUrl = ''): string
    {
        $back = !empty($backUrl) ? base64_encode($backUrl) : null;

        return cot_url(
            'thanks',
            ['n' => 'control', 'a' => 'delete', 'from' => $userId, 'back' => $back]
        );
    }

    public static function deleteToUserUrl(int $userId, string $backUrl = ''): string
    {
        $back = !empty($backUrl) ? base64_encode($backUrl) : null;

        return cot_url(
            'thanks',
            ['n' => 'control', 'a' => 'delete', 'to' => $userId, 'back' => $back]
        );
    }
}