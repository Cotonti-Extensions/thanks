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
use cot\services\ItemService;

require_once cot_incfile('thanks', 'plug');

class Thanks
{
    public static function buildList(
        $t,
        int $page = 1,
        ?int $perPage = null,
        array $urlParams = [],
        array $queryWhere = [],
        array $queryParams = [],
        string $pageNumCharacter = 'd'
    ) : void {
        [$auth['read'], $auth['write'], $auth['isAdmin']] = cot_auth('plug', 'comments');
        if (!$auth['read']) {
            return;
        }

        $currentPage = $page;
        if ($currentPage < 1) {
            $currentPage = 1;
        }

        $itemsPerPage = $perPage;
        if ($itemsPerPage === null) {
            $itemsPerPage = ThanksHelper::getPerPage();
        }

        $thanksTable = Cot::$db->quoteTableName(Cot::$db->thanks);

        $sqlWhere = !empty($queryWhere) ? ' WHERE ' . implode(' AND ', $queryWhere) : '';

        $sql = "SELECT COUNT(*) FROM {$thanksTable} {$sqlWhere}";
        $count = (int) Cot::$db->query($sql, $queryParams)->fetchColumn();

        $t->assign([
            'IS_ADMIN' => $auth['isAdmin'],
            'IS_AJAX' => COT_AJAX,
            'COUNT' => $count,
        ]);

        if ($count === 0) {
            return;
        }

        $ext = defined('COT_ADMIN') ? 'admin' : 'thanks';

        $offset = ($currentPage - 1) * $itemsPerPage;

        $pageNav = cot_pagenav(
            $ext,
            $urlParams,
            $offset,
            $count,
            $itemsPerPage,
            $pageNumCharacter,
            '',
//            Cot::$cfg['jquery'] && Cot::$cfg['turnajax'],
//            'thanks-container'
        );
        if ($pageNav['onpage'] === 0 && $offset > $pageNav['entries']) {
            $urlParams[$pageNumCharacter] = Cot::$cfg['easypagenav']
                ? $pageNav['total']
                : ($pageNav['total'] - 1) * $itemsPerPage;
            cot_redirect(cot_url($ext, $urlParams, '', true));
        }

        $sqlLimit = $itemsPerPage > 0 ? " LIMIT $itemsPerPage OFFSET $offset" : '';

        $sql = "SELECT {$thanksTable}.* FROM {$thanksTable} $sqlWhere ORDER BY {$thanksTable}.created_at DESC {$sqlLimit}";
        $thanks = Cot::$db->query($sql, $queryParams)->fetchAll();

        $userIds = [];
        $itemIds = [];
        foreach ($thanks as $key => $thank) {
            $thanks[$key] = ThanksRepository::getInstance()->castAttributes($thank);
            if (!in_array($thanks[$key]['from_user_id'], $userIds, true)) {
                $userIds[] = $thanks[$key]['from_user_id'];
            }
            if (!in_array($thanks[$key]['to_user_id'], $userIds, true)) {
                $userIds[] = $thanks[$key]['to_user_id'];
            }
            if (
                !isset($itemIds[$thank['source']])
                || !in_array($thanks[$key]['source_id'], $itemIds[$thank['source']], true)
            ) {
                $itemIds[$thank['source']][] = $thanks[$key]['source_id'];
            }
        }

        $items = [];
        foreach ($itemIds as $source => $ids) {
            $items[$source] = ItemService::getInstance()->getItems($source, $ids);
        }

        $users = \cot\modules\users\inc\UsersRepository::getInstance()->getByIds($userIds);

        $backUrlParams = $urlParams;
        if ($pageNav['current'] > 1) {
            $backUrlParams[$pageNumCharacter] = Cot::$cfg['easypagenav']
                ? $pageNav['current']
                : ($pageNav['current'] - 1) * $itemsPerPage;
        }

        $backUrl = cot_url($ext, $backUrlParams, '#thanks', true);

        $dateFormat = 'datetime_medium';
        foreach ($thanks as $thank) {
            $sender = $users[$thank['from_user_id']] ?? null;
            $recipient = $users[$thank['to_user_id']] ?? null;
            $timeStamp = strtotime($thank['created_at']);
            $item = $items[$thank['source']][$thank['source_id']] ?? null;
            $t->assign([
                'ROW_DATE' => cot_date($dateFormat, $timeStamp),
                'ROW_DATE_STAMP' => $timeStamp,
                'ROW_FROM_USER_ID' => $thank['from_user_id'],
                'ROW_TO_USER_ID' => $thank['to_user_id'],
            ]);
            $t->assign(cot_generate_usertags($sender, 'ROW_FROM_'));
            $t->assign(cot_generate_usertags($recipient, 'ROW_TO_'));
            $t->assign(ThanksHelper::generateItemTags($item, 'ROW_'));

            if ($auth['isAdmin']) {
                $deleteUrl = ThanksHelper::deleteUrl($thank['id'], $backUrl);
                $deleteConfirmUrl = cot_confirm_url($deleteUrl, $ext, Cot::$L['thanks_deleteOne'] . '?');
                $filterFromUrl = !empty($sender['user_name'])
                    ? cot_url('admin', ['m' => 'thanks', 'from' => $sender['user_name']])
                    : '';
                $filterToUrl = !empty($recipient['user_name'])
                    ? cot_url('admin', ['m' => 'thanks', 'to' => $recipient['user_name']])
                    : '';
                $t->assign([
                    'ROW_DELETE' => cot_rc_link($deleteConfirmUrl, Cot::$R['thanks_deleteLabel'], 'class="confirmLink"'),
                    'ROW_DELETE_URL' => $deleteConfirmUrl,
                    //'ROW_FROM_USER_FILTER' => cot_rc_link($filterFromUrl, Cot::$R['thanks_deleteLabel'], 'class="confirmLink"'),
                    'ROW_FROM_USER_FILTER_URL' => $filterFromUrl,
                    //'ROW_TO_USER_FILTER' => cot_rc_link($deleteConfirmUrl, Cot::$R['thanks_deleteLabel'], 'class="confirmLink"'),
                    'ROW_TO_USER_FILTER_URL' => $filterToUrl,
                ]);
            }

            $t->parse('MAIN.THANKS_ROW');
        }

        $t->assign(cot_generatePaginationTags($pageNav));
    }
}