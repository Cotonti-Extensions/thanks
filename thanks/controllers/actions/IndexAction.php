<?php
/**
 * Thanks Index Action
 *
 * @package thanks
 * @author Cotonti team
 * @copyright (c) 2016-2025 Cotonti team
 * @license BSD
 */

declare(strict_types=1);

namespace cot\plugins\thanks\controllers\actions;

use Cot;
use cot\controllers\BaseAction;
use cot\plugins\thanks\inc\ThanksHelper;
use cot\plugins\thanks\inc\UsersRepository as ThanksUsersRepository;
use XTemplate;

class IndexAction extends BaseAction
{
    public const TOP_SORT_FIELDS = ['count', 'total'];

    public function run(): string
    {
        $title = Cot::$L['thanks_top'];
        $metaTitle = $title;
        $metaDescription = Cot::$L['thanks_meta_desc'];

        $perPage = ThanksHelper::getPerPage();
        [$pg, $d, $durl] = cot_import_pagenav('d', $perPage);
        if (!empty($pg) && $pg > 1) {
            // Appending page number to subtitle and meta description
            $metaTitle .= htmlspecialchars(cot_rc('code_title_page_num', ['num' => $pg]));
            $metaDescription .= htmlspecialchars(cot_rc('code_title_page_num', ['num' => $pg]));
        }

        $urlParams = [];

        // Limit top rating to last N days statistic
        $days = cot_import('days', 'G', 'INT');
        if ($days > 0) {
            $urlParams['days'] = $days;
            $daysPeriod = $days;
        } else {
            $daysPeriod = (int) Cot::$cfg['plugin']['thanks']['count_last_days'];
        }

        // Order field name ('count', 'total')
        $sort = cot_import('s', 'G', 'ALP');
        if (!in_array($sort, self::TOP_SORT_FIELDS, true)) {
            $sort = 'count';
        }
        if ($sort !== 'count') {
            $urlParams['s'] = $sort;
        }

        // Order way (asc, desc)
        $way = cot_import('w', 'G', 'ALP', 4);
        if (!in_array($way, ['asc', 'desc'], true)) {
            $way = 'desc';
        }
        if ($way !== 'desc') {
            $urlParams['w'] = $way;
        }

        $count = (int) Cot::$db->query(ThanksUsersRepository::getInstance()->getTopCountQuery($daysPeriod))->fetchColumn();

        $title = Cot::$L['thanks_top'];
        Cot::$out['subtitle'] = $metaTitle;
        Cot::$out['desc'] = $metaDescription;

        $sortByCountUrl = $this->getTopSortUrl('count', $urlParams);
        $sortByCountWay = $sort === 'count' ? Cot::$R["icon_order_{$way}"] : '';
        $sortByCountWayTitle = $sortByCountWay !== ''
            ? ($way === 'asc' ? Cot::$L['Ascending'] : Cot::$L['Descending'])
            : '';

        $sortByTotalUrl = $this->getTopSortUrl('total', $urlParams);
        $sortByTotalWay = $sort === 'total' ? Cot::$R["icon_order_{$way}"] : '';
        $sortByTotalWayTitle = $sortByTotalWay !== ''
            ? ($way === 'asc' ? Cot::$L['Ascending'] : Cot::$L['Descending'])
            : '';

        // make column header title with sorting mode links if needed
        $ratedMsg = $daysPeriod > 0 ? 'thanks_ratedForPeriod' : 'thanks_rated';

        $showTotals = Cot::$cfg['plugin']['thanks']['show_totals'] && $daysPeriod > 0;

        $t = new XTemplate(cot_tplfile('thanks.top', 'plug'));
        $t->assign([
            'TITLE' => $title,
            'PERIOD_DESCRIPTION' => $daysPeriod > 0
                ? cot_rc(Cot::$L['thanks_ratingPeriodDesc'], ['days' => cot_declension($daysPeriod, 'Days')])
                : '',
            'PERIOD_USERS_COUNT_DESCRIPTION' => $daysPeriod > 0
                ?  cot_rc(
                    Cot::$L['thanks_ratingPeriodUsersCountDesc'],
                    ['users' => cot_declension($count, 'Members')]
                )
                : '',
            'IS_AJAX' => COT_AJAX,
            'IS_ADMIN' => Cot::$usr['isadmin'],
            'PERIOD_DAYS' => $daysPeriod,
            'COUNT' => $count,
            'SORT_BY_COUNT_URL' => $sortByCountUrl,
            'COUNT_TITLE' => Cot::$L[$ratedMsg],
            'COUNT_TITLE_LINK' => cot_rc_link($sortByCountUrl, Cot::$L[$ratedMsg]),
            'SORT_BY_COUNT_WAY' => $sortByCountWay,
            'SORT_BY_COUNT_WAY_LINK' => $sortByCountWay !== ''
                ? cot_rc_link($sortByCountUrl, $sortByCountWay, ['title' => $sortByCountWayTitle])
                : '',
            'SHOW_TOTALS' => $showTotals,
            'SORT_BY_TOTAL_URL' => $sortByTotalUrl,
            'TOTAL_TITLE' => Cot::$L[$ratedMsg],
            'TOTAL_TITLE_LINK' => cot_rc_link($sortByTotalUrl, Cot::$L['Total']),
            'SORT_BY_TOTAL_WAY' => $sortByTotalWay,
            'SORT_BY_TOTAL_WAY_LINK' => $sortByTotalWay !== ''
                ? cot_rc_link($sortByTotalUrl, $sortByTotalWay, ['title' => $sortByCountWayTitle])
                : '',
        ]);

        if ($count === 0) {
            $t->parse('MAIN');
            return $t->text('MAIN');
        }

        $pageNav = cot_pagenav(
            'thanks',
            $urlParams,
            $d,
            $count,
            $perPage,
            'd',
            '',
            Cot::$cfg['jquery'] && Cot::$cfg['turnajax']
        );
        if ($pageNav['onpage'] === 0 && $d > $pageNav['total']) {
            $urlParams['d'] = Cot::$cfg['easypagenav']
                ? $pageNav['total']
                : ($pageNav['total'] - 1) * $perPage;
            cot_redirect(cot_url('thanks', $urlParams, '', true));
        }

        $sql = ThanksUsersRepository::getInstance()->getTopQuery($daysPeriod, $sort, $way) . " LIMIT {$perPage} OFFSET {$d}";
        $users = Cot::$db->query($sql)->fetchAll();
        $num = $d + 1;
        foreach ($users as $user) {
            $t->assign(cot_generate_usertags($user, 'USER_ROW_'));
            $t->assign([
                'USER_ROW_NUM' => $num,
                'USER_ROW_THANKS_COUNT' => $daysPeriod > 0 && isset($user['thanksForPeriod'])
                    ? $user['thanksForPeriod']
                    : $user['user_thanks'],
                'USER_ROW_THANKS_TOTAL' => $daysPeriod > 0 ? $user['user_thanks'] : '',
            ]);
            $t->parse('MAIN.USER_ROW');
            $num++;
        }

        $t->assign(cot_generatePaginationTags($pageNav));

        $t->parse('MAIN');
        return $t->text('MAIN');
    }

    private function getTopSortUrl(string $sort, $urlParams): string
    {
        $currentSort = $urlParams['s'] ?? 'count';
        if (!in_array($currentSort, self::TOP_SORT_FIELDS, true)) {
            $currentSort = 'count';
        }

        $currentWay = $urlParams['w'] ?? 'desc';
        if (!in_array($currentWay, ['asc', 'desc'], true)) {
            $currentWay = 'desc';
        }

        unset($urlParams['s'], $urlParams['w'], $urlParams['d']);

        if (!in_array($sort, self::TOP_SORT_FIELDS, true)) {
            $sort = 'count';
        }
        if ($sort !== 'count') {
            $urlParams['s'] = $sort;
        }

        if ($currentSort === $sort && $currentWay === 'desc') {
            $urlParams['w'] = 'asc';
        }

        return cot_url('thanks', $urlParams);
    }
}