<?php
/**
 * Thanks user Action
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
use cot\plugins\thanks\controllers\IndexController;
use cot\plugins\thanks\inc\ThanksHelper;
use cot\plugins\thanks\inc\ThanksRepository;
//use cot\plugins\thanks\inc\UsersRepository;
use cot\services\ItemService;
use cot\users\UsersHelper;
use XTemplate;

/**
 * @property IndexController $controller
 */
class UserThanksAction extends BaseAction
{
    public function run(): string
    {
        $userId = cot_import('id', 'G', 'INT');
        if (empty($userId)) {
            $userId = Cot::$usr['id'];
        }

        if (empty($userId)) {
            cot_die_message(404);
        }

        if ($userId === Cot::$usr['id']) {
            $user = Cot::$usr['profile'];
        } else {
            $user = UsersRepository::getInstance()->getById($userId);
            if (!$user) {
                cot_die_message(404);
            }
            cot_fillGroupsForUser($user);
        }

        $template = 'thanks.list.user.' . ($userId !== Cot::$usr['id'] ? 'user' : 'my');

        $t = new XTemplate(cot_tplfile($template, 'plug'));

        if ($userId !== Cot::$usr['id']) {
            $userFullName = UsersHelper::getInstance()->getFullName($user);
            $title = Cot::$L['thanks_forUser'] . ' ' . $userFullName;
            $metaTitle = Cot::$L['thanks_forUser'] . ' ' . $userFullName;
            $metaDescription = Cot::$L['thanks_forUserDesc'] . ' ' . $userFullName;
            $breadcrumbs = [
                [cot_url('users'), Cot::$L['Users']],
                [
                    cot_url(
                        'users',
                        ['m' => 'details', 'id' => $user['user_id'], 'u' => $user['user_name']]
                    ),
                    $userFullName
                ],
                [thanks_userThanksUrl($user['user_id']), Cot::$L['thanks_forUser']],
            ];
        } else {
            $title = Cot::$L['thanks_forMe'];
            $metaTitle = $title;
            $metaDescription = Cot::$L['thanks_forMeDesc'];
            $breadcrumbs = [
                [cot_url('users', ['m' => 'profile']), Cot::$L['pro_title']],
                [thanks_userThanksUrl(), Cot::$L['thanks_forMe']],
            ];
        }

        $t->assign(cot_generate_usertags($user, 'USER_'));
        $t->assign([
            'PAGE_TITLE' => $title,
            'PAGE_BREADCRUMBS' => cot_breadcrumbs($breadcrumbs, Cot::$cfg['homebreadcrumb']),
            'USER_GROUPS' => $user['groups'],
            'IS_AJAX' => COT_AJAX,
            'IS_ADMIN' => Cot::$usr['isadmin'],
        ]);

        $perPage = ThanksHelper::getPerPage();
        [$pg, $d, $durl] = cot_import_pagenav('d', $perPage);
        if (!empty($pg) && $pg > 1) {
            // Appending page number to subtitle and meta description
            $metaTitle .= htmlspecialchars(cot_rc('code_title_page_num', ['num' => $pg]));
            $metaDescription .= htmlspecialchars(cot_rc('code_title_page_num', ['num' => $pg]));
        }

        Cot::$out['subtitle'] = $metaTitle;
        Cot::$out['desc'] = $metaDescription;

        $errors = [];
        if (in_array(COT_GROUP_BANNED, $user['groups'])) {
            $errors[] = Cot::$L['thanks_err_banned'];
        }

        if (in_array(COT_GROUP_INACTIVE, $user['groups'])) {
            $errors[] = Cot::$L['thanks_err_inactive'];
        }

        if (!empty($errors)) {
            foreach ($errors as $error) {
                cot_error($error);
            }
            cot_display_messages($t);
            $t->parse('MAIN');
            return $t->text('MAIN');
        }

        $thanksTable = Cot::$db->quoteTableName(Cot::$db->thanks);

        $queryWhere = ['userId' => $thanksTable . '.to_user_id = :userId'];
        $queryParams = ['userId' => $userId];

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

        $urlParams = ['a' => 'user'];
        if ($userId !== Cot::$usr['id']) {
            $urlParams['id'] = $userId;
        }

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

        $sourceIds = [];
        $userIds = [];
        foreach ($thanks as $key => $thank) {
            $thanks[$key] = ThanksRepository::getInstance()->castAttributes($thank);
            if (
                !isset($sourceIds[$thank['source']])
                || !in_array($thanks[$key]['source_id'], $sourceIds[$thank['source']], true)
            ) {
                $sourceIds[$thank['source']][] = $thanks[$key]['source_id'];
            }
            if (!in_array($thanks[$key]['from_user_id'], $userIds, true)) {
                $userIds[] = $thanks[$key]['from_user_id'];
            }
        }

        $items = [];
        foreach ($sourceIds as $source => $ids) {
            $items[$source] = ItemService::getInstance()->getItems($source, $ids);
        }

        $users = UsersRepository::getInstance()->getByIds($userIds);

        $backUrlParams = ['a' => 'user'];
        if ($userId !== Cot::$usr['id']) {
            $backUrlParams['id'] = $userId;
        }
        if ($durl > 1) {
            $backUrlParams['d'] = $durl;
        }

        $backUrl = cot_url(cot_url('thanks', $backUrlParams, '', true));

        $dateFormat = 'datetime_medium';
        foreach ($thanks as $thank) {
            $item = $items[$thank['source']][$thank['source_id']] ?? null;
            $unknownTitleAppend = '';
            if (!$item && Cot::$usr['isadmin']) {
                $unknownTitleAppend = ' (Source: ' . $thank['source'] . ', SourceId: ' . $thank['source_id'] . ')';
            }
            $timeStamp = strtotime($thank['created_at']);

            $t->assign(ThanksHelper::generateItemTags($item, 'ROW_'));
            $t->assign([
                'ROW_SOURCE' => $thank['source'],
                'ROW_SOURCE_ID' => $thank['source_id'],
                'ROW_TITLE' => $item ? htmlspecialchars($item->title) : Cot::$L['Deleted'] . $unknownTitleAppend,
                'ROW_HTML_TITLE' => $item ? $item->getTitleHtml() : Cot::$L['Deleted'] . $unknownTitleAppend,
                'ROW_TYPE_TITLE' => $item ? $item->typeTitle : (Cot::$usr['isadmin'] ? $thank['source'] : 'Unknown'),
                'ROW_DATE' => cot_date($dateFormat, $timeStamp),
                'ROW_DATE_STAMP' => $timeStamp,
                'ROW_FROM_USER_ID' => $thank['from_user_id'],
                'ROW_TO_USER_ID' => $thank['to_user_id'],
            ]);
            $sender = $users[$thank['from_user_id']] ?? [];
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