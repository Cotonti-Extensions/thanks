<?php
/**
 * Thanks Control controller
 *
 * @package thanks
 * @author Trustmaster & Cotonti team
 * @copyright Copyright (c) 2011-2015 Vladimir Sibirov, 2016-2024 Cotonti team
 * @license BSD
 */

declare(strict_types=1);

namespace cot\plugins\thanks\controllers;

use cot\controllers\BaseController;
use cot\plugins\thanks\controllers\actions\DeleteAction;

class ControlController extends BaseController
{
    public function __construct()
    {
        [$read, $write, $isAdmin] = cot_auth('plug', 'thanks');
        cot_block($isAdmin);
    }

    public static function actions(): array
    {
        return [
            'delete' => DeleteAction::class,
        ];
    }
}