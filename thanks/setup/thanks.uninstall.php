<?php
/**
 * Thanks uninstallation handler
 *
 * @package thanks
 * @author Trustmaster & Cotonti team
 * @copyright Copyright (c) 2011-2015 Vladimir Sibirov, 2016-2024 Cotonti team
 * @license BSD
 */

declare(strict_types=1);

defined('COT_CODE') or die('Wrong URL');

Cot::$db->query('ALTER TABLE ' . Cot::$db->users . ' DROP COLUMN user_thanks');

global $db_thanks;

if (empty($db_thanks)) {
    // Registering tables
    Cot::$db->registerTable('thanks');
}

Cot::$db->query('DROP TABLE IF EXISTS ' . Cot::$db->thanks);