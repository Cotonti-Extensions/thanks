<?php
/**
 * Thanks List Action
 *
 * @package thanks
 * @author Cotonti team
 * @copyright (c) 2016-2025 Cotonti team
 * @license BSD
 */

declare(strict_types=1);

namespace cot\plugins\thanks\controllers\actions;

use Cot;
use cot\controllers\BaseAction;
use cot\modules\users\inc\UsersRepository;
use cot\plugins\thanks\inc\ThanksHelper;
use cot\plugins\thanks\inc\ThanksRepository;
use cot\services\ItemService;
use XTemplate;

class ListAction extends BaseAction
{
    public function run(): string
    {
        $source = cot_import('source', 'G', 'ALP');
        $sourceId = cot_import('item', 'G', 'INT');
        if (empty($source) || empty($sourceId) || $sourceId < 1) {
            cot_die_message(404);
        }

        $item = ItemService::getInstance()->get($source, $sourceId);
        if (!$item) {
            cot_die_message(404);
        }

        $title = Cot::$L['thanks_title_short'] . ': ' . htmlspecialchars($item->title);
        $titleHtml = Cot::$L['thanks_title_short'] . ': ' . $item->getTitleHtml();
        $metaTitle = $title;
        $metaDescription = $title;

        $usersRepository = UsersRepository::getInstance();

        $author = null;
        if (!empty($item->authorId)) {
            $author = $usersRepository->getById($item->authorId);
            if ($author) {
                cot_fillGroupsForUser($author);
            }
        }

        $template = ['thanks.list', 'item'];
        $t = new XTemplate(cot_tplfile($template, 'plug'));

        $t->assign(cot_generate_usertags($author, 'AUTHOR_'));
        $t->assign([
            'PAGE_TITLE' => $title,
            'PAGE_TITLE_HTML' => $titleHtml,
            'AUTHOR_BANNED' => !empty($author) && in_array(COT_GROUP_BANNED, $author['groups'], true),
            'IS_AJAX' => COT_AJAX,
            'IS_ADMIN' => Cot::$usr['isadmin'],
        ]);
        $t->assign(ThanksHelper::generateItemTags($item));

        $perPage = ThanksHelper::getPerPage();
        [$pg, $d, $durl] = cot_import_pagenav('d', $perPage);
        if (!empty($pg) && $pg > 1) {
            // Appending page number to subtitle and meta description
            $metaTitle .= htmlspecialchars(cot_rc('code_title_page_num', ['num' => $pg]));
            $metaDescription .= htmlspecialchars(cot_rc('code_title_page_num', ['num' => $pg]));
        }

        Cot::$out['subtitle'] = $metaTitle;
        Cot::$out['desc'] = $metaDescription;

        $thanksTable = Cot::$db->quoteTableName(Cot::$db->thanks);

        $queryWhere = [
            'source' => $thanksTable . '.source = :source',
            'sourceId' => $thanksTable . '.source_id = :sourceId',
        ];
        $queryParams = ['source' => $source, 'sourceId' => $sourceId];

        // Hook

        $sqlWhere = ' WHERE ' . implode(' AND ', $queryWhere);

        $sql = "SELECT COUNT(*) FROM {$thanksTable}  {$sqlWhere}";
        $count = (int) Cot::$db->query($sql, $queryParams)->fetchColumn();

        $t->assign([
            'COUNT' => $count,
        ]);

        if ($count === 0) {
            $t->parse('MAIN');
            return $t->text('MAIN');
        }

        $urlParams = ['a' => 'list', 'source' => $source, 'item' => $sourceId];

        $pageNav = cot_pagenav(
            'thanks',
            $urlParams,
            $d,
            $count,
            $perPage,
            'd',
            '',
            Cot::$cfg['jquery'] && Cot::$cfg['turnajax']
        );
        if ($pageNav['onpage'] === 0 && $d > $pageNav['total']) {
            $urlParams['d'] = Cot::$cfg['easypagenav']
                ? $pageNav['total']
                : ($pageNav['total'] - 1) * $perPage;
            cot_redirect(cot_url('thanks', $urlParams, '', true));
        }

        $sql = "SELECT {$thanksTable}.* FROM {$thanksTable} $sqlWhere ORDER BY {$thanksTable}.created_at "
            . " DESC LIMIT {$perPage} OFFSET {$d}";
        $thanks = Cot::$db->query($sql, $queryParams)->fetchAll();

        $userIds = [];
        foreach ($thanks as $key => $thank) {
            $thanks[$key] = ThanksRepository::getInstance()->castAttributes($thank);
            if (!in_array($thanks[$key]['from_user_id'], $userIds, true)) {
                $userIds[] = $thanks[$key]['from_user_id'];
            }
        }

        $users = $usersRepository->getByIds($userIds);

        $backUrlParams = $urlParams;
        if ($durl > 1) {
            $backUrlParams['d'] = $durl;
        }
        $backUrl = cot_url('thanks', $backUrlParams, '', true);

        $dateFormat = 'datetime_medium';
        foreach ($thanks as $thank) {
            $sender = $users[$thank['from_user_id']] ?? null;
            $timeStamp = strtotime($thank['created_at']);
            $t->assign([
                'ROW_DATE' => cot_date($dateFormat, $timeStamp),
                'ROW_DATE_STAMP' => $timeStamp,
                'ROW_FROM_USER_ID' => $thank['from_user_id'],
            ]);
            $t->assign(cot_generate_usertags($sender, 'ROW_FROM_'));

            if (Cot::$usr['isadmin']) {
                $deleteUrl = ThanksHelper::deleteUrl($thank['id'], $backUrl);
                $deleteConfirmUrl = cot_confirm_url($deleteUrl, '', Cot::$L['thanks_deleteOne'] . '?');
                $t->assign([
                    'ROW_DELETE' => cot_rc_link($deleteConfirmUrl, Cot::$R['thanks_deleteLabel'], 'class="confirmLink"'),
                    'ROW_DELETE_URL' => $deleteConfirmUrl,
                ]);
            }

            $t->parse('MAIN.THANKS_ROW');
        }

        $t->assign(cot_generatePaginationTags($pageNav));

        cot_display_messages($t);

        $t->parse('MAIN');
        return $t->text('MAIN');
    }
}