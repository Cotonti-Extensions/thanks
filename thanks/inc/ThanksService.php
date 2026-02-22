<?php
/**
 * Thanks Service
 *
 * @package thanks
 * @author Trustmaster & Cotonti team
 * @copyright Copyright (c) 2011-2015 Vladimir Sibirov, 2016-2024 Cotonti team
 * @license BSD
 */

declare(strict_types=1);

namespace cot\plugins\thanks\inc;

use Cot;
use cot\modules\pm\services\PrivateMessageService;
use cot\plugins\thanks\exceptions\NotFoundException;
use cot\services\ItemService;
use cot\users\UsersHelper;
use DateTime;
use DateTimeZone;
use Throwable;

class ThanksService
{
    /**
     * @param array{user_id: int, groups: list<int>} $toUser
     * @return array{error: bool, message: string}
     */
    public static function validateThankAdd(string $source, int $sourceId, array $toUser, int $fromUserId): array
    {
        $result = ['error' => false, 'message' => []];

        if ($toUser['user_id'] === $fromUserId) {
            $result['error'] = true;
            $result['message'][] = Cot::$L['thanks_err_self'];
        }

        $todayBegin = self::getTodayMidnight();

        if (
            Cot::$cfg['plugin']['thanks']['maxday'] > 0
            && ThanksRepository::getCountByUserToday($fromUserId) >= Cot::$cfg['plugin']['thanks']['maxday']
        ) {
            $result['error'] = true;
            $result['message'][] = Cot::$L['thanks_err_maxday'];
        }

        if (
            Cot::$cfg['plugin']['thanks']['maxuser'] > 0
            && Cot::$db->query(
                'SELECT COUNT(*) FROM ' . Cot::$db->thanks . " WHERE from_user_id = ? AND to_user_id = ? AND created_at >= '{$todayBegin}'",
                [$fromUserId, $toUser['user_id']]
            )->fetchColumn() >= Cot::$cfg['plugin']['thanks']['maxuser']
        ) {
            $result['error'] = true;
            $result['message'][] = Cot::$L['thanks_err_maxuser'];
        }

        if (
            Cot::$db->query(
                'SELECT COUNT(*) FROM ' . Cot::$db->thanks . " WHERE from_user_id = ? AND source = ? AND source_id = ?",
                [$fromUserId, $source, $sourceId]
            )->fetchColumn() >= 1
        ) {
            $result['error'] = true;
            $result['message'][] = Cot::$L['thanks_err_item'];
        }

        if (in_array(COT_GROUP_BANNED, $toUser['groups'], true)) {
            $result['error'] = true;
            $result['message'][] = Cot::$L['thanks_err_cantBanned'];
        }

        return $result;
    }

    /**
     * Current day begin for user in default timezone
     * @return void
     */
    public static function getTodayMidnight(): string
    {
        $defaultTimeZone = !empty(Cot::$cfg['defaulttimezone']) ? Cot::$cfg['defaulttimezone'] : 'UTC';
        if (!empty(Cot::$usr['timezonename']) && Cot::$usr['timezonename'] !== $defaultTimeZone) {
            try {
                $userTimeZone = new DateTimeZone(Cot::$usr['timezonename']);
            } catch (Throwable $e) {
            }
        }

        $date = new DateTime('today midnight', new DateTimeZone($defaultTimeZone));
        if (!empty($userTimeZone)) {
            $date->setTimezone($userTimeZone);
        }

        return $date->format('Y-m-d H:i:s');
    }

    public static function getAuthorIdBySourceId(string$source, int $source_id): ?int
    {
        $authorId = null;
        switch ($source) {
            case THANKS_SOURCE_PAGE:
                require_once cot_incfile('page', 'module');
                $authorId = (int) Cot::$db->query('SELECT page_ownerid FROM ' . Cot::$db->pages . ' WHERE page_id = ?', [$source_id])
                    ->fetchColumn();
                break;

            case THANKS_SOURCE_FORUM_POST:
                require_once cot_incfile('forums', 'module');
                $authorId = (int) Cot::$db->query('SELECT fp_posterid FROM ' . Cot::$db->forum_posts . ' WHERE fp_id = ?', [$source_id])
                    ->fetchColumn();
                break;

            case THANKS_SOURCE_COMMENT:
                require_once cot_incfile('comments', 'plug');
                $authorId = (int) Cot::$db->query('SELECT com_authorid FROM ' . Cot::$db->com . ' WHERE com_id = ?', [$source_id])
                    ->fetchColumn();
                break;
        }

        /* === Hook === */
        foreach (cot_getextplugins('thanks.getAuthorIdBySourceId') as $pl) {
            include $pl;
        }
        /* ===== */

        return $authorId;
    }

    public static function addThank(string $source, int $sourceId, ?int $fromUserId = null, ?int $toUserId = null): bool
    {
        if ($fromUserId === null) {
            $fromUserId = Cot::$usr['id'];
        }

        if ($toUserId === null) {
            $toUserId = self::getAuthorIdBySourceId($source, $sourceId);
        }

        Cot::$db->getConnection()->beginTransaction();
        try {
            $inserted = Cot::$db->insert(
                Cot::$db->thanks,
                [
                    'created_at' => date('Y-m-d H:i:s', Cot::$sys['now']),
                    'to_user_id' => $toUserId,
                    'from_user_id' => $fromUserId,
                    'source' => $source,
                    'source_id' => $sourceId
                ]
            );

            self::recalculateUserThanks($toUserId);

            Cot::$db->getConnection()->commit();
        } catch (Throwable $e) {
            Cot::$db->getConnection()->rollBack();
            $inserted = 0;
        }

        /* === Hook === */
        foreach (cot_getextplugins('thanks.add.done') as $pl) {
            include $pl;
        }
        /* ===== */

        return $inserted > 0;
    }

    /**
     * @param string[] $condition
     * @param array<string, int|float|string> $params
     * @param ?int $parentTrashId When deleting to the trash an object to which these thanks belong,
     *  the id of the object in the trash
     * @return int deleted items count
     */
    public static function deleteThankByCondition(array $condition, array $params = [], ?int $parentTrashId = 0): int
    {
        global $lang;

        if ($condition === []) {
            return 0;
        }

        $items = ThanksRepository::getInstance()->getByCondition($condition, $params);
        if (empty($items)) {
            return 0;
        }

        $tmpLang = null;
        if ($parentTrashId > 0 && !Cot::$cfg['forcedefaultlang'] && Cot::$cfg['defaultlang'] !== $lang) {
            $tmpLang = Cot::$L;
            $loc = LangService::load('thanks', 'plug', Cot::$cfg['defaultlang']);
            Cot::$L = array_merge($loc, Cot::$L);
        }

        $userIdsToRecalculate = [];
        foreach ($items as $item) {
            if (!in_array($item['to_user_id'], $userIdsToRecalculate, true)) {
                $userIdsToRecalculate[] = $item['to_user_id'];
            }
            if ($parentTrashId > 0) {
                cot_trash_put(
                    THANKS_SOURCE,
                    Cot::$L['thanks_title_short'] . " #" . $item['id']
                        . " from {$item['source']} #{$item['source_id']}",
                    $item['id'],
                    $item,
                    $parentTrashId
                );
            }
        }

        if ($tmpLang !== null) {
            Cot::$L = $tmpLang;
        }

        $sqlWhere = implode(' AND ', $condition);
        try {
            Cot::$db->beginTransaction();
            $deleted = Cot::$db->delete(Cot::$db->thanks, $sqlWhere, $params);
            self::recalculateUserThanks($userIdsToRecalculate);
            Cot::$db->commit();
        } catch (Throwable $e) {
            Cot::$db->rollBack();
            throw $e;
        }

        /* === Hook === */
        foreach (cot_getextplugins('thanks.delete.done') as $pl) {
            include $pl;
        }
        /* ===== */

        return $deleted;
    }

    public static function deleteThankById(int $id): bool
    {
        if ($id < 1) {
            throw new NotFoundException;
        }

//        $thank = ThanksRepository::getById($id);
//        if (!$thank) {
//            throw new NotFoundException;
//        }

        $table = Cot::$db->quoteTableName(Cot::$db->thanks);
        $condition['id'] = "{$table}.id = :thankId";
        $params['thankId'] = $id;

        return (bool) self::deleteThankByCondition($condition, $params);
    }

    /**
     * @param int|list<int> $userIds
     */
    public static function recalculateUserThanks($userIds): void
    {
        if (!is_array($userIds)) {
            $userIds = [(int) $userIds];
        }

        $userIds = ThanksRepository::prepareIntegerIds($userIds);
        if (empty($userIds)) {
            return;
        }

        $usersTable = Cot::$db->quoteTableName(Cot::$db->users);
        $thanksTable = Cot::$db->quoteTableName(Cot::$db->thanks);
        $where = " WHERE {$usersTable}.user_id "
            . (
                count($userIds) === 1
                    ? '= ' . array_shift($userIds)
                    : 'IN (' . implode(',', $userIds) . ')'
            );

        $sql = "UPDATE $usersTable SET user_thanks = "
            . "(SELECT COUNT(*) FROM $thanksTable WHERE {$thanksTable}.to_user_id = {$usersTable}.user_id) "
            . $where;

        Cot::$db->query($sql);
    }

    public static function notifyThankedUser(string $source, int $sourceId, array $fromUser, array $toUser): bool
    {
        // Notification body on recipient's language
        $tmpLang = null;
        if (!Cot::$cfg['forcedefaultlang'] && Cot::$cfg['defaultlang'] !== $toUser['user_lang']) {
            $tmpLang = Cot::$L;
            $loc = LangService::load('thanks', 'plug', $toUser['user_lang']);
            Cot::$L = array_merge($loc, Cot::$L);
        }

        $item = ItemService::getInstance()->get($source, $sourceId);
        if (!$item) {
            return false;
        }

        $usersHelper = UsersHelper::getInstance();

        $thankedUserUrl = cot_url(
            'users',
            ['m' => 'details', 'id' => $fromUser['user_id'], 'u' => $fromUser['user_name']]
        );
        $thankedUserName = htmlspecialchars($usersHelper->getFullName($fromUser));

        $notificationBody = cot_rc(
            Cot::$L['thanks_notificationBody'],
            [
                'userName' => $usersHelper->getFullName($toUser),
                'fromUser' => cot_rc_link($thankedUserUrl, $thankedUserName),
                'item' => mb_lcfirst($item->getTitleHtml()),
            ]
        );

        $emailSent = false;
        if (Cot::$cfg['plugin']['thanks']['notify_by_email']) {
            cot_mail(
                ['to' => $toUser['user_email'], 'from' => [Cot::$cfg['plugin']['thanks']['notify_from']]],
                Cot::$L['thanks_notificationSubject'],
                $notificationBody,
                '',
                false,
                '',
                true
            );
            $emailSent = true;
        }

        if (Cot::$cfg['plugin']['thanks']['notify_by_pm']) {
            if ($emailSent) {
                $oldValue = Cot::$cfg['pm']['allownotifications'];
                Cot::$cfg['pm']['allownotifications'] = false;
            }
            include_once cot_incfile('pm', 'module');
            PrivateMessageService::getInstance()->send(
                (int) $toUser['user_id'],
                Cot::$L['thanks_notificationSubject'],
                $notificationBody
            );
            if ($emailSent) {
                Cot::$cfg['pm']['allownotifications'] = $oldValue;
            }
        }

        if ($tmpLang !== null) {
            Cot::$L = $tmpLang;
        }

        return true;
    }
}