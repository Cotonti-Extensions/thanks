<?php
/* ====================
[BEGIN_COT_EXT]
Hooks=admin.home.mainpanel, admin.home.sidepanel
[END_COT_EXT]
==================== */

declare(strict_types=1);

/**
 * Thanks recent thanks widget for admin home page
 *
 * @package thanks
 * @author Trustmaster & Cotonti team
 * @copyright Copyright (c) 2011-2015 Vladimir Sibirov, 2016-2024 Cotonti team
 * @license BSD
 */

use cot\plugins\thanks\inc\Thanks;

(defined('COT_CODE') && defined('COT_ADMIN')) or die('Wrong URL.');

if (empty($thanksAuth)) {
    [$thanksAuth['read'], $thanksAuth['write'], $thanksAuth['isAdmin']] = cot_auth('plug', 'thanks');
}

if (!$thanksAuth['read']) {
    return;
}

$thanksCounter = !isset($thanksCounter) ? 1 : $thanksCounter + 1;

if (
    !($thanksCounter === 1 && Cot::$cfg['plugin']['thanks']['adminWidget'] === 'main')
    && !($thanksCounter === 2 && Cot::$cfg['plugin']['thanks']['adminWidget'] === 'side')
) {
    return;
}

require_once cot_incfile('thanks', 'plug');

//$thanksTable = Cot::$db->quoteTableName(Cot::$db->thanks);
//
$perPage = !empty(Cot::$cfg['plugin']['thanks']['adminWidgetPerPage'])
    ? (int) Cot::$cfg['plugin']['thanks']['adminWidgetPerPage']
    : (int) Cot::$cfg['maxrowsperpage'];
//
//$thanksSql = "SELECT {$thanksTable}.* FROM {$thanksTable} ORDER BY {$thanksTable}.created_at DESC LIMIT {$perPage}";
//$thanks = Cot::$db->query($thanksSql)->fetchAll();
//
//$thanksUserIds = [];
//$thanksItemIds = [];
//foreach ($thanks as $key => $thank) {
//    $thanks[$key] = ThanksRepository::castAttributes($thank);
//    if (!in_array($thanks[$key]['from_user_id'], $thanksUserIds, true)) {
//        $thanksUserIds[] = $thanks[$key]['from_user_id'];
//    }
//    if (!in_array($thanks[$key]['to_user_id'], $thanksUserIds, true)) {
//        $thanksUserIds[] = $thanks[$key]['to_user_id'];
//    }
//    if (
//        !isset($thanksItemIds[$thank['source']])
//        || !in_array($thanks[$key]['source_id'], $thanksItemIds[$thank['source']], true)
//    ) {
//        $thanksItemIds[$thank['source']][] = $thanks[$key]['source_id'];
//    }
//}
//
//$thanksItems = [];
//foreach ($thanksItemIds as $source => $itemIds) {
//    $thanksItems[$source] = SourceItemService::getItems($source, $itemIds);
//}
//
//$thanksUsers = UsersRepository::getByIds($thanksUserIds);
//
//$tt = new XTemplate(cot_tplfile(['thanks.admin.home', Cot::$cfg['plugin']['thanks']['adminWidget']], 'plug'));
//
//$tt->assign([
//    'IS_ADMIN' => $thanksAuth['isAdmin'],
//    'ADMIN_THANKS_URL' => cot_url('admin', 'm=other&p=thanks'),
//    'COUNT' => count($thanks),
//]);
//
//$backUrlParams = [];
//$back = base64_encode(cot_url('admin', $backUrlParams, '#thanks', true));
//
//$dateFormat = 'datetime_medium';
//foreach ($thanks as $thank) {
//    $sender = $thanksUsers[$thank['from_user_id']] ?? null;
//    $recipient = $thanksUsers[$thank['to_user_id']] ?? null;
//    $timeStamp = strtotime($thank['created_at']);
//    $item = $thanksItems[$thank['source']][$thank['source_id']] ?? null;
//    $tt->assign([
//        'ROW_DATE' => cot_date($dateFormat, $timeStamp),
//        'ROW_DATE_STAMP' => $timeStamp,
//        'ROW_FROM_USER_ID' => $thank['from_user_id'],
//        'ROW_TO_USER_ID' => $thank['to_user_id'],
//    ]);
//    $tt->assign(cot_generate_usertags($sender, 'ROW_FROM_'));
//    $tt->assign(cot_generate_usertags($recipient, 'ROW_TO_'));
//    $tt->assign(ThanksHelper::generateItemTags($item, 'ROW_'));
//
//    if ($thanksAuth['isAdmin']) {
//        $deleteUrl = cot_url('thanks', ['a' => 'delete', 'id' => $thank['id'], 'back' => $back]);
//        $deleteConfirmUrl = cot_confirm_url($deleteUrl, '', Cot::$L['thanks_delete_one']);
//        $tt->assign([
//            'ROW_DELETE' => cot_rc_link($deleteConfirmUrl, Cot::$R['thanks_deleteLabel'], 'class="confirmLink"'),
//            'ROW_DELETE_URL' => $deleteConfirmUrl,
//        ]);
//    }
//
//    $tt->parse('MAIN.THANKS_ROW');
//}
//
//$tt->parse('MAIN');
$template = cot_tplfile(['thanks.admin.home', Cot::$cfg['plugin']['thanks']['adminWidget']], 'plug');

$tt = new XTemplate($template);

Thanks::buildList($tt, 1, $perPage);

$tt->parse('MAIN');
$line = $tt->text('MAIN');
