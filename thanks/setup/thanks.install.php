<?php
/**
 * Thanks installation handler
 *
 * @package thanks
 * @author Trustmaster & Cotonti team
 * @copyright Copyright (c) 2011-2015 Vladimir Sibirov, 2016-2024 Cotonti team
 * @license BSD
 */

declare(strict_types = 1);

defined('COT_CODE') or die('Wrong URL');

global $db_thanks, $db_users;

if (empty($db_thanks)) {
    // Registering tables
    Cot::$db->registerTable('thanks');
}
if (empty($db_users)) {
    Cot::$db->registerTable('users');
}

$usersTableName = Cot::$db->users;
if (!Cot::$db->fieldExists(Cot::$db->users, 'user_thanks')) {
    Cot::$db->query("ALTER TABLE {$usersTableName} ADD COLUMN user_thanks INT NOT NULL DEFAULT 0");
}
Cot::$db->query(
    'ALTER TABLE ' . $db_users . ' ADD INDEX users_user_thanks_idx (user_thanks); '
);

if (!Cot::$db->tableExists(Cot::$db->thanks)) {
    Cot::$db->query(
        'CREATE TABLE ' . Cot::$db->thanks . '('
        . 'id INT UNSIGNED NOT NULL AUTO_INCREMENT, '
        . 'to_user_id INT UNSIGNED NOT NULL, '
        . 'from_user_id INT UNSIGNED NOT NULL, '
        . 'source VARCHAR(100) NOT NULL, '
        . 'source_id INT NOT NULL, '
        . 'created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, '
        //. 'params TEXT NULL DEFAULT NULL, ' Пока не уверен что там хранить
        . 'PRIMARY KEY (id), '
        . 'INDEX thanks_source_idx (source), '
        . 'INDEX thanks_source_source_id_idx (source, source_id), '
        . 'INDEX thanks_from_user_id_idx (from_user_id), '
        . 'INDEX thanks_created_at_idx (created_at), ' // Что-то он не используется
        . 'INDEX thanks_to_user_id_idx (to_user_id) '
        . ')'
    );
}
