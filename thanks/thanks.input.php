<?php
/* ====================
[BEGIN_COT_EXT]
Hooks=input
[END_COT_EXT]
==================== */

declare(strict_types=1);

/**
 * Thanks disable anti xss for registered users
 *
 * @package thanks
 * @author Trustmaster & Cotonti team
 * @copyright Copyright (c) 2011-2015 Vladimir Sibirov, 2016-2024 Cotonti team
 * @license BSD
 */

defined('COT_CODE') or die('Wrong URL.');

if (
    isset($_GET['e'])
    && $_GET['e'] === 'thanks'
    && isset($_GET['a'])
    && $_GET['a'] === 'new'
    && isset($_SERVER['REQUEST_METHOD'])
    && $_SERVER['REQUEST_METHOD'] === 'POST'
    && Cot::$usr['id'] > 0
) {
    define('COT_NO_ANTIXSS', true);
}
