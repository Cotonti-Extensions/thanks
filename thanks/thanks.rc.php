<?php
/* ====================
[BEGIN_COT_EXT]
Hooks=rc
[END_COT_EXT]
==================== */

declare(strict_types=1);

/**
 * Thanks plugin
 * Load JS if header resource consolidation is turned on
 *
 * @package thanks
 * @author Cotonti team
 * @copyright Copyright (c) 2016-2024 Cotonti team
 * @license BSD
 */

defined('COT_CODE') or die('Wrong URL');

if (!defined('COT_ADMIN') && Cot::$cfg['headrc_consolidate']) {
    Resources::addFile(Cot::$cfg['plugins_dir'] . '/thanks/js/thanks.js');
}
