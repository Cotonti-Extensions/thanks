<?php
/**
 * Users repository
 *
 * @package thanks
 * @author Cotonti team
 * @copyright @copyright Copyright (c) 2011-2015 Vladimir Sibirov, 2016-2024 Cotonti team
 * @license BSD
 */

declare(strict_types=1);

namespace cot\plugins\thanks\inc;

use Cot;
use cot\repositories\BaseRepository;
use DateInterval;
use DateTime;

class UsersRepository extends BaseRepository
{
    public static function getTableName(): string
    {
        return '';
    }

    /**
     * @param list<string> $usersNames
     * @return array<int, <array<string, mixed>>> An array users data
     * @deprecated
     */
    public function getByNames(array $usersNames): array
    {
        if (empty($usersNames)) {
            return [];
        }

        $userNamesPrepared = array_map(fn(string $name): string => Cot::$db->quote($name), $usersNames);

        $usersTable = Cot::$db->quoteTableName(Cot::$db->users);
        $query = "SELECT {$usersTable}.* FROM {$usersTable} "
            . "WHERE {$usersTable}.user_name IN (" . implode(',', $userNamesPrepared) . ')';

        $data = Cot::$db->query($query)->fetchAll();
        $users = [];
        foreach ($data as $row) {
            $row = self::prepareUserData($row);
            $users[$row['user_id']] = $row;
        }

        return $users;
    }

    private static function prepareUserData(array $data): array
    {
        $result = $data;
        if (isset($data['user_id'])) {
            $result['user_id'] = (int) $data['user_id'];
        }
        if (isset($data['user_maingrp'])) {
            $result['user_maingrp'] = (int) $data['user_maingrp'];
        }
        if (isset($data['user_banexpire'])) {
            $result['user_banexpire'] = (int) $data['user_banexpire'];
        }

        return $result;
    }

    public static function getCountById(int $userId): int
    {
        $result = (int) Cot::$db->query(
            'SELECT COUNT(*) FROM ' . Cot::$db->thanks . ' WHERE to_user_id = :userId',
            ['userId' => $userId]
        )->fetchColumn();

        return $result;
    }

    public function getTopQuery(?int $daysPeriod = null, string $sort = 'count', string $way = 'DESC'): string
    {
        $thanksTable = Cot::$db->quoteTableName(Cot::$db->thanks);
        $usersTable = Cot::$db->quoteTableName(Cot::$db->users);

        $where = ['banned' => cot_user_sqlExcludeBanned()];

        $way = strtoupper($way);
        if (!in_array($sort, ['count', 'total'])) {
            $sort = 'count';
        }

        if ((int) $daysPeriod <= 0) {
            $where['count'] = "{$usersTable}.user_thanks > 0";
            $sqlWhere = 'WHERE ' . implode(' AND ', $where);
            return "SELECT {$usersTable}.* FROM {$usersTable} $sqlWhere ORDER BY {$usersTable}.user_thanks $way";
        }

        $where['startDate'] = self::getStartDateConditionForTopQuery($daysPeriod);

        $sqlWhere = '';
        if (!empty($where)) {
            $sqlWhere = 'WHERE ' . implode(' AND ', $where);
        }

        $sqlOrder = $sort === 'count' ? 'thanksForPeriod' : "{$usersTable}.user_thanks";

        return "SELECT COUNT({$thanksTable}.to_user_id) as thanksForPeriod, {$usersTable}.* "
            . "FROM {$thanksTable} "
            . "INNER JOIN $usersTable ON $thanksTable.to_user_id = {$usersTable}.user_id $sqlWhere "
            . "GROUP BY {$thanksTable}.to_user_id "
            . "ORDER BY $sqlOrder $way";
    }

    public function getTopCountQuery(?int $daysPeriod = null): string
    {
        $thanksTable = Cot::$db->quoteTableName(Cot::$db->thanks);
        $usersTable = Cot::$db->quoteTableName(Cot::$db->users);

        $where = ['banned' => cot_user_sqlExcludeBanned()];

        if ((int) $daysPeriod <= 0) {
            $where['count'] = "{$usersTable}.user_thanks > 0";
            $sqlWhere = 'WHERE ' . implode(' AND ', $where);
            return "SELECT COUNT(*) FROM {$usersTable} $sqlWhere";
        }

        $where['startDate'] = self::getStartDateConditionForTopQuery($daysPeriod);

        $sqlWhere = '';
        if (!empty($where)) {
            $sqlWhere = 'WHERE ' . implode(' AND ', $where);
        }

        return "SELECT COUNT(DISTINCT $thanksTable.to_user_id) "
            . "FROM {$thanksTable} "
            . "INNER JOIN $usersTable ON $thanksTable.to_user_id = {$usersTable}.user_id $sqlWhere";
    }

    private static function getStartDateConditionForTopQuery(?int $daysPeriod = null): string
    {
        $thanksTable = Cot::$db->quoteTableName(Cot::$db->thanks);

        if ((int) $daysPeriod <= 0) {
            return '';
        }

        $timeZone = cot_getUserTimeZone();
        $startDate = new DateTime('now', $timeZone);
        $startDate->sub(
            DateInterval::createFromDateString($daysPeriod . ' day')
        );
        return "{$thanksTable}.created_at >='" . $startDate->format('Y-m-d H:i:s') . "'";
    }
}