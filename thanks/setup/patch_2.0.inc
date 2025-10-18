<?php
/**
 * Thanks plugin
 *
 * Update to v 2.0
 */

declare(strict_types=1);

use cot\modules\forums\inc\ForumsDictionary;
use cot\plugins\comments\inc\CommentsDictionary;

defined('COT_CODE') or die('Wrong URL');

global $db_thanks, $db_users;

require_once cot_incfile('thanks', 'plug');

if (empty($db_thanks)) {
  // Registering tables
  Cot::$db->registerTable('thanks');
}
if (empty($db_users)) {
    Cot::$db->registerTable('users');
}

$changeCollation = false;
if (
    Cot::$cfg['mysqlcharset'] === 'utf8mb4'
    && (empty(Cot::$cfg['mysqlcollate']) || Cot::$cfg['mysqlcollate'] === 'utf8mb4_unicode_ci')
) {
    $changeCollation = true;
}

Cot::$db->query(
    'ALTER TABLE ' . $db_thanks
    . ' CHANGE th_id id INT UNSIGNED NOT NULL auto_increment, '
    . ' CHANGE th_date created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, '
    . ' CHANGE th_touser to_user_id INT UNSIGNED NOT NULL, '
    . ' CHANGE th_fromuser from_user_id INT UNSIGNED NOT NULL, '
    . ' CHANGE th_ext source VARCHAR(100) NOT NULL, '
    . ' CHANGE th_item source_id INT NOT NULL, '
    . ' ENGINE=InnoDB; '
);

if ($changeCollation) {
    Cot::$db->query(
        "ALTER TABLE {$db_thanks}
        CONVERT TO CHARACTER SET '" . Cot::$cfg['mysqlcharset'] . "' COLLATE '" . Cot::$cfg['mysqlcollate'] . "', 
        DEFAULT CHARACTER SET='" . Cot::$cfg['mysqlcharset'] . "' COLLATE='" . Cot::$cfg['mysqlcollate'] . "'"
    );
}

Cot::$db->query(
    'ALTER TABLE ' . $db_thanks
    . ' ADD INDEX thanks_source_idx (source), '
    . ' ADD INDEX thanks_source_source_id_idx (source, source_id), '
    . ' ADD INDEX thanks_from_user_id_idx (from_user_id), '
    . ' ADD INDEX thanks_created_at_idx (created_at), '
    . ' ADD INDEX thanks_to_user_id_idx (to_user_id); '
);

if (!Cot::$db->indexExists($db_users, 'users_user_thanks_idx')) {
    Cot::$db->addIndex($db_users, 'users_user_thanks_idx', 'user_thanks');
}

Cot::$db->query(
    'UPDATE ' . $db_thanks . " SET source = " . Cot::$db->quote(CommentsDictionary::SOURCE_COMMENT)
    . " WHERE source = 'comments'"
);

Cot::$db->query(
    'UPDATE ' . $db_thanks . " SET source = " . Cot::$db->quote(ForumsDictionary::SOURCE_POST)
    . " WHERE source = 'forums'"
);