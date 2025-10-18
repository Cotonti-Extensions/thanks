<?php
/**
 * Thanks / Resources
 *
 * @package thanks
 * @author Trustmaster & Cotonti team
 * @copyright Copyright (c) 2011-2015 Vladimir Sibirov, 2016-2025 Cotonti team
 * @license BSD
 */

declare(strict_types=1);

defined('COT_CODE') or die('Wrong URL');

$R['thanks_addThank'] = '<div class="thanks thanks-add-container thanks-{$source}-add-container">'
    . '<a href="{$url}" rel="nofollow" class="thanks thanks-add thanks-{$source}-add confirm" data-source="{$source}" '
    . 'data-source_id="{$sourceId}" data-to_user="{$toUserId}">' . Cot::$L['thanks_addNew'] . '</a>'
    . '</div>';
$R['thanks_deleteLabel'] = cot_rc_modify(Cot::$R['admin_icon_delete'], ['title' => Cot::$L['thanks_deleteOne']]);
