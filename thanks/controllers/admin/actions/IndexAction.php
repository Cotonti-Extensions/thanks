<?php
/**
 * Comments system for Cotonti
 * Admin index action
 *
 * @package Comments
 * @copyright (c) Cotonti Team
 * @license https://github.com/Cotonti/Cotonti/blob/master/License.txt
 */

declare(strict_types=1);

namespace cot\plugins\thanks\controllers\admin\actions;

use Cot;
use cot\controllers\BaseAction;
use cot\modules\users\inc\UsersHelper;
use cot\modules\users\inc\UsersRepository;
use cot\plugins\thanks\controllers\admin\IndexController;
use cot\plugins\thanks\inc\Thanks;
use cot\plugins\thanks\inc\ThanksHelper;
use Resources;
use XTemplate;

defined('COT_CODE') or die('Wrong URL');

/**
 * @property-read IndexController $controller
 */
class IndexAction extends BaseAction
{
    public function run(): string
    {
        global $L, $R, $Ls;

        $perPage = ThanksHelper::getPerPage();
        [$pg, $d, $durl] = cot_import_pagenav('d', $perPage);

        $urlParams = ['m' => 'thanks'];

        $userIdsFilter = [];
        $from = cot_import('from', 'G', 'INT');
        if (!empty($from)) {
            $userIdsFilter[] = $from;
        }
        $to = cot_import('to', 'G', 'INT');
        if (!empty($to)) {
            $userIdsFilter[] = $to;
        }

        $filterUsers = [];
        if ($userIdsFilter !== []) {
            $filterUsers = UsersRepository::getInstance()->getByIds($userIdsFilter);
        }

        $template = cot_tplfile('thanks.admin.main', 'plug');
        $t = new XTemplate($template);

        if (!COT_AJAX) {
            Resources::linkFile('plugins/thanks/tpl/style-admin.css', 'css');
        }

        $fromId = $toId = null;
        foreach ($filterUsers as $user) {
            if ($user['user_id'] === $from) {
                $fromId = $user['user_id'];
                $urlParams['from'] = $user['user_id'];
                $t->assign(cot_generate_usertags($user, 'FROM_USER_'));
            }

            if ($user['user_id'] === $to) {
                $toId = $user['user_id'];
                $urlParams['to'] = $user['user_id'];
                $t->assign(cot_generate_usertags($user, 'TO_USER_'));
            }
        }

        $backUrl = cot_url('admin', $urlParams, '', true);

        foreach ($filterUsers as $user) {
            if (in_array($user['user_id'],  [$fromId, $toId], true)) {
                $prefix = $user['user_id'] === $fromId ? 'FROM_' : 'TO_';

                $deleteFromUrl = ThanksHelper::deleteFromUserUrl($user['user_id'], $backUrl);
                $deleteFromLabelText = cot_rc(Cot::$L['thanks_deleteAllFromUser'], ['userName' =>  cot_user_full_name($user)]);
                $deleteFromLabel = Cot::$R['admin_icon_delete'] . ' ' . $deleteFromLabelText;
                $deleteFromConfirmUrl = cot_confirm_url($deleteFromUrl, 'admin', $deleteFromLabelText);

                $deleteToUrl = ThanksHelper::deleteToUserUrl($user['user_id'], $backUrl);
                $deleteToLabelText = cot_rc(Cot::$L['thanks_deleteAllToUser'], ['userName' =>  cot_user_full_name($user)]);
                $deleteToLabel = Cot::$R['admin_icon_delete'] . ' ' . $deleteToLabelText;
                $deleteToConfirmUrl = cot_confirm_url($deleteToUrl, 'admin', $deleteToLabelText);

                $t->assign([
                    $prefix . 'USER_DELETE_FROM' => cot_rc_link($deleteFromConfirmUrl, $deleteFromLabel, 'class="confirmLink"'),
                    $prefix . 'USER_DELETE_FROM_URL' => $deleteFromConfirmUrl,
                    $prefix . 'USER_DELETE_TO' => cot_rc_link($deleteToConfirmUrl, $deleteToLabel, 'class="confirmLink"'),
                    $prefix . 'USER_DELETE_TO_URL' => $deleteToConfirmUrl,
                ]);
            }
        }

        $where = [];
        $queryParams = [];
        if ($toId !== null) {
            $where['to'] = 'to_user_id = :toUserId';
            $queryParams['toUserId'] = $toId;
        }
        if ($fromId !== null) {
            $where['from'] = 'from_user_id = :fromUserId';
            $queryParams['fromUserId'] = $fromId;
        }

        Thanks::buildList($t, $pg, $perPage, $urlParams, $where, $queryParams);

        $filterFormAction = cot_url('admin', ['m' => 'thanks']);
        $filterFormAction = explode('?', $filterFormAction);
        $filterFormAction = $filterFormAction[0];

        $filterParams = '';

        $t->assign([
            'FILTER_FORM_ACTION' => $filterFormAction,
            'FILTER_PARAMS' => $filterParams,
//            'FILTER_FROM' => cot_inputbox(
//                'text',
//                'from',
//                $from,
//                ['id' => 'filter-from', 'class' => 'form-control user-input']
//            ),
            'FILTER_FROM' => UsersHelper::getInstance()->usersSelect('from', $from),
//            'FILTER_TO' => cot_inputbox(
//                'text',
//                'to',
//                $to,
//                ['id' => 'filter-to', 'class' => 'form-control user-input']
//            ),
            'FILTER_TO' => UsersHelper::getInstance()->usersSelect('to', $to),
        ]);

        if ($t->hasTag('DONE_ROW_MSG')) {
            cot_display_messages($t);
        }

        $t->parse('MAIN');
        return $t->text('MAIN');
    }
}