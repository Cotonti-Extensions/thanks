<?php
/**
 * Thanks Admin controller
 *
 * @package thanks
 * @author Trustmaster & Cotonti team
 * @copyright Copyright (c) 2011-2015 Vladimir Sibirov, 2016-2024 Cotonti team
 * @license BSD
 */

declare(strict_types=1);

namespace cot\plugins\thanks\controllers\admin;

use cot\controllers\BaseController;
use cot\plugins\thanks\controllers\admin\actions\IndexAction;

(defined('COT_CODE') && defined('COT_ADMIN')) or die('Wrong URL.');

class IndexController extends BaseController
{
    public static function actions(): array
    {
        return [
            'index' => IndexAction::class,
        ];
    }
}