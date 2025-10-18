<?php
/* ====================
[BEGIN_COT_EXT]
Hooks=footer.tags
[END_COT_EXT]
==================== */

declare(strict_types=1);

/**
 * Thanks plugin
 * Load JS if header resource consolidation is turned off
 *
 * @package thanks
 * @author Trustmaster & Cotonti team
 * @copyright Copyright (c) 2011-2015 Vladimir Sibirov, 2016-2024 Cotonti team
 * @license BSD
 */


use cot\plugins\thanks\dto\ThanksRequestDto;
use cot\plugins\thanks\inc\ThanksRepository;

defined('COT_CODE') or die('Wrong URL');

if (defined('COT_ADMIN')) {
    return;
}

if (!Cot::$cfg['headrc_consolidate'] && !empty(ThanksRequestDto::getRequestedItems())) {
    Resources::linkFileFooter(Cot::$cfg['plugins_dir'] . '/thanks/js/thanks.js');
}

$thankedUsersToday = ThanksRepository::getCountByUserForUsersToday(Cot::$usr['id']);

Resources::embedFooter(
    'window.thanks = { '
    . 'maxPerDay: ' . Cot::$cfg['plugin']['thanks']['maxday'] . ', '
    . 'maxToEachUser: ' . Cot::$cfg['plugin']['thanks']['maxuser'] . ', '
    . 'thankedToday: ' . ThanksRepository::getCountByUserToday(Cot::$usr['id']) . ', '
    . 'thankedUsersToday: ' . ($thankedUsersToday !== [] ? json_encode($thankedUsersToday) : '{}') . ', '
    . "confirmPrompt: '" . Cot::$L['thanks_ensure'] . "', "
    . "errorLimit: '" . Cot::$L['thanks_err_maxday'] . "', "
    . "errorUserLimit: '" . Cot::$L['thanks_err_maxuser'] . "', "
    . "errorRequest: '" . Cot::$L['thanks_err_request'] . "', "
    . "x: '" . Cot::$sys['xk'] . "', "
    . '}'
);