<?php
/**
 * Thanks Index Action
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
use cot\exceptions\NotFoundHttpException;
use cot\plugins\thanks\exceptions\NotFoundException;
use cot\plugins\thanks\inc\ThanksService;

class DeleteAction extends BaseAction
{
    public function run()
    {
        $backUrl = cot_import('back', 'G', 'TXT');
        if ($backUrl) {
            $backUrl = base64_decode($backUrl);
        }

        if (empty($backUrl)) {
            cot_die_message(404);
        }

        $id = cot_import('id', 'G', 'INT');
        $from = cot_import('from', 'G', 'INT');
        $to = cot_import('to', 'G', 'INT');
        if (empty($id) && empty($from) && empty($to)) {
            throw new NotFoundHttpException();
        }

        $table = Cot::$db->thanks;
        $condition = [];
        $params = [];
        if (!empty($id)) {
            $condition['id'] = "{$table}.id = :thankId";
            $params['thankId'] = $id;
        }
        if (!empty($from)) {
            $condition['from'] = "{$table}.from_user_id = :fromUserId";
            $params['fromUserId'] = $from;
        }
        if (!empty($to)) {
            $condition['to'] = "{$table}.to_user_id = :toUserId";
            $params['toUserId'] = $to;
        }

        try {
            $result = ThanksService::deleteThankByCondition($condition, $params);
        } catch (NotFoundException $e) {
            cot_die_message(404);
        }

        if ($result === 1) {
            cot_message(Cot::$L['thanks_deletedOne']);
        } elseif ($result > 1) {
            cot_message(cot_rc(Cot::$L['thanks_deleted'], ['n' => cot_declension($result, Cot::$L['thanks_declension'])]));
        } else {
            cot_error(Cot::$L['Error']);
        }

        cot_redirect($backUrl);
    }
}