<?php
/**
 * Thanks repository
 *
 * @package thanks
 * @author Trustmaster & Cotonti team
 * @copyright Copyright (c) 2011-2015 Vladimir Sibirov, 2016-2024 Cotonti team
 * @license BSD
 */

declare(strict_types=1);

namespace cot\plugins\thanks\inc;

use Cot;
use cot\repositories\BaseRepository;
use PDO;

class ThanksRepository extends BaseRepository
{
    public static function getTableName(): string
    {
        if (empty(Cot::$db->thanks)) {
            Cot::$db->registerTable('thanks');
        }
        return Cot::$db->thanks;
    }

    protected function afterFetch(array $item): array
    {
        return $this->castAttributes($item);
    }

    public function getById(int $id): ?array
    {
        $table = Cot::$db->quoteTableName(Cot::$db->thanks);
        $condition['id'] = "{$table}.id = :thankId";
        $params['thankId'] = $id;

        $result = $this->getByCondition($condition, $params);

        return !empty($result) ? array_shift($result) : null;
    }

    /**
     * @return array<int, list<array<string, int|string>>> An array of thanks for each item (indexed by source_id)
     */
    public static function getBySourceIds(string $source, array $sourceIds, int $limit = 0): array
    {
        if (empty($source) || empty($sourceIds) || $limit < 0) {
            return [];
        }

        $preparedIds = self::prepareIntegerIds($sourceIds);

        $thanksTable = Cot::$db->quoteTableName(Cot::$db->thanks);
        $usersTable = Cot::$db->quoteTableName(Cot::$db->users);

        $queryWhere = [
            'source' => "{$thanksTable}.source = :source",
            // 'userExists' => "{$usersTable}.user_id IS NOT NULL", // Not needed with INNER JOIN
        ];
        $queryWhere['userIsNotBanned'] = cot_user_sqlExcludeBanned();
        $queryParams = ['source' => $source];

        $result = [];
        if ($limit === 0) {
            $queryWhere['sourceId'] = "{$thanksTable}.source_id IN ( " . implode(', ', $preparedIds) . " ) ";

            $sqlWhere = ' WHERE ' . implode(' AND ', $queryWhere);

            $sql = "SELECT {$thanksTable}.*, {$usersTable}.* "
                . " FROM {$thanksTable} "
                . " INNER JOIN {$usersTable} ON {$thanksTable}.from_user_id = {$usersTable}.user_id "
                . $sqlWhere . " ORDER BY {$thanksTable}.created_at DESC";

            $queryResult = Cot::$db->query($sql, $queryParams)->fetchAll();
            if (!empty($queryResult)) {
                foreach ($queryResult as $row) {
                    $result[$row['source_id']][] = $row;
                }
            }

        } else {
            // With limit for each item
            $sqlLimit = "LIMIT $limit";
            $queryWhere['sourceId'] = "{$thanksTable}.source_id = :sourceId";
            $sqlWhere = ' WHERE ' . implode(' AND ', $queryWhere);

            $sql = "SELECT {$thanksTable}.*, {$usersTable}.* "
                . " FROM {$thanksTable} "
                . " INNER JOIN {$usersTable} ON {$thanksTable}.from_user_id = {$usersTable}.user_id "
                . $sqlWhere . " ORDER BY {$thanksTable}.created_at DESC $sqlLimit";

            foreach ($preparedIds as $sourceId) {
                $queryParams['sourceId'] = $sourceId;
                $queryResult = Cot::$db->query($sql, $queryParams)->fetchAll();
                if (!empty($queryResult)) {
                    foreach ($queryResult as $row) {
                        $result[$row['source_id']][] = $row;
                    }
                }
            }
        }

        return $result;
    }

    /**
     * @return list<int> source ids which has thanks from user
     * @todo $excludeBanned parameter. if true join users table and use self::userIsNotBannedCondition() ???
     */
    public static function thankedByUserId(string $source, array $sourceIds, int $userId): array
    {
        $thanksTable = Cot::$db->quoteTableName(Cot::$db->thanks);

        $preparedIds = self::prepareIntegerIds($sourceIds);

        $result = Cot::$db->query(
            "SELECT DISTINCT {$thanksTable}.source_id FROM {$thanksTable} "
            . ' WHERE source = :source AND source_id IN (' . implode(', ', $preparedIds) . ") AND from_user_id = :userId",
            ['source' => $source, 'userId' => $userId]
        )->fetchAll(PDO::FETCH_COLUMN);

        if (!empty($result)) {
            $result = array_map('intval', $result);
        }

        return $result;
    }

    /**
     * @return array<int, int> An array of thanks count for each item (indexed by source_id)
     */
    public static function getCountsBySourceIds(string $source, array $sourceIds): array
    {
        if (empty($source) || empty($sourceIds)) {
            return [];
        }

        $preparedIds = self::prepareIntegerIds($sourceIds);

        $thanksTable = Cot::$db->quoteTableName(Cot::$db->thanks);
        $usersTable = Cot::$db->quoteTableName(Cot::$db->users);

        $queryWhere = [
            'source' => "{$thanksTable}.source = :source",
            // 'userExists' => "{$usersTable}.user_id IS NOT NULL", // Not needed with INNER JOIN
        ];
        $queryWhere['userIsNotBanned'] = cot_user_sqlExcludeBanned();
        $queryParams = ['source' => $source];

        $result = [];

        $queryWhere['sourceId'] = "{$thanksTable}.source_id IN ( " . implode(', ', $preparedIds) . " ) ";

        $sqlWhere = ' WHERE ' . implode(' AND ', $queryWhere);

        $sql = "SELECT {$thanksTable}.source_id, COUNT(*) as thanks_count "
            . " FROM {$thanksTable} "
            . " INNER JOIN {$usersTable} ON {$thanksTable}.from_user_id = {$usersTable}.user_id "
            . $sqlWhere . " GROUP BY {$thanksTable}.source_id, {$thanksTable}.source_id ";

        $queryResult = Cot::$db->query($sql, $queryParams)->fetchAll();

        if (!empty($queryResult)) {
            foreach ($queryResult as $row) {
                $result[$row['source_id']] = $row['thanks_count'];
            }
        }

        return $result;
    }

    public static function getCountByUserToday(int $fromUserId): int
    {
        $todayBegin = ThanksService::getTodayMidnight();

        // In PHP below version 8, all fields are fetching as strings
        return (int) Cot::$db->query(
            'SELECT COUNT(*) FROM ' . Cot::$db->thanks . " WHERE from_user_id = ? AND created_at >= '{$todayBegin}'",
            [$fromUserId]
        )->fetchColumn();
    }

    /**
     * @return array<int, int> The thanks count from '$fromUserId' to other users. The key of the array is the user id, the value is the thanks count
     * @todo можно фильровать и по to_user_id чтобы не тянуть лишние данные
     */
    public static function getCountByUserForUsersToday(int $fromUserId): array
    {
        $todayBegin = ThanksService::getTodayMidnight();
        $result = [];
        $thanks = Cot::$db->query(
            'SELECT to_user_id, COUNT(*) as thanks_count FROM ' . Cot::$db->thanks
            . " WHERE from_user_id = ? AND created_at >= '{$todayBegin}' GROUP BY to_user_id",
            [$fromUserId]
        )->fetchAll();

        if (!$thanks) {
            return [];
        }

        foreach ($thanks as $row) {
            $result[(int) $row['to_user_id']] = (int) $row['thanks_count'];
        }

        return $result;
    }

    /**
     * @param array $thank
     * @return array
     */
    public function castAttributes(array $thank): array
    {
        $thank['id'] = (int) $thank['id'];
        $thank['to_user_id'] = (int) $thank['to_user_id'];
        $thank['from_user_id'] = (int) $thank['from_user_id'];
        $thank['source_id'] = (int) $thank['source_id'];
        return $thank;
    }

    /**
     * @param int[] $sourceIds
     * @return list<int>
     */
    public static function prepareIntegerIds(array $sourceIds): array
    {
        $preparedIds = [];
        foreach ($sourceIds as $sourceId) {
            $sourceId = (int) $sourceId;
            if ($sourceId > 0) {
                $preparedIds[] = $sourceId;
            }
        }
        return $preparedIds;
    }

    public static function getCount(
        ?string $source = null,
        ?array $sourceId = null,
        ?array $fromUserIds = null,
        ?array $toUserIds = null,
        ?string $startDate = null,
        ?string $endDate = null
    ): int {

    }


}