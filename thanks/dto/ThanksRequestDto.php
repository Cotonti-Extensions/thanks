<?php
/**
 * @package thanks
 * @author Trustmaster & Cotonti team
 * @copyright Copyright (c) 2011-2015 Vladimir Sibirov, 2016-2024 Cotonti team
 * @license BSD
 */

declare(strict_types=1);

namespace cot\plugins\thanks\dto;

final class ThanksRequestDto
{
    /**
     * @var array<string, list<int>>
     */
    private static array $requestedItems = [];

    public static function add(string $source, int $sourceId): void
    {
        if (
            isset(self::$requestedItems[$source])
            && in_array($sourceId, self::$requestedItems[$source], true)
        ) {
            return;
        }

        self::$requestedItems[$source][] = $sourceId;
    }

    /**
     * @return array<string, list<int>>
     */
    public static function getRequestedItems(): array
    {
        return self::$requestedItems;
    }
}