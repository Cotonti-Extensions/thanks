<?php
/**
 * Thanks Index controller
 *
 * @package thanks
 * @author Cotonti team
 * @copyright (c) 2016-2025 Cotonti team
 * @license BSD
 */

declare(strict_types=1);

namespace cot\plugins\thanks\controllers;

use Cot;
use cot\controllers\BaseController;
use cot\plugins\thanks\controllers\actions\IndexAction;
use cot\plugins\thanks\controllers\actions\ListAction;
use cot\plugins\thanks\controllers\actions\NewThankAction;
use cot\plugins\thanks\controllers\actions\UserThanksAction;

class IndexController extends BaseController
{
    public function __construct()
    {
        if (!isset(Cot::$out['head'])) {
            Cot::$out['head'] = '';
        }
        Cot::$out['head'] .= Cot::$R['code_noindex'];
        if (empty(Cot::$out['subtitle'])) {
            Cot::$out['subtitle'] = Cot::$L['thanks_meta_title'];
        }
    }

    public static function actions(): array
    {
        return [
            'index' => IndexAction::class,
            'list' => ListAction::class,
            'new' => NewThankAction::class,
            'user' => UserThanksAction::class,
        ];
    }

    public function userAction(): string
    {

    }

    public function result(array $data = []): string
    {
        header('Content-type: application/json');
        return json_encode($data);
    }
}