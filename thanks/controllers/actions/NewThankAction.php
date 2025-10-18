<?php
/**
 * Thanks New Action
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
use cot\exceptions\NotFoundHttpException;
use cot\modules\users\inc\UsersRepository;
use cot\plugins\thanks\controllers\IndexController;
use cot\plugins\thanks\inc\ThanksHelper;
use cot\plugins\thanks\inc\ThanksRepository;
use cot\plugins\thanks\inc\ThanksService;
use cot\plugins\thanks\inc\UsersRepository as ThanksUsersRepository;

/**
 * @property-read IndexController $controller
 */
class NewThankAction extends BaseAction
{
    public function run(): string
    {
        if (!Cot::$usr['auth_write']) {
            throw new NotFoundHttpException();
        }

        $method = $_SERVER['REQUEST_METHOD'] === 'POST' ? 'P' : 'G';
        $source = cot_import('source', $method, 'ALP');
        $sourceId = cot_import('item', $method, 'INT');
        if (!$source || !$sourceId) {
            throw new NotFoundHttpException();
        }

        // Get item author Id
        $authorId = ThanksService::getAuthorIdBySourceId($source, $sourceId);
        if (!$authorId) {
            throw new NotFoundHttpException();
        }

        $author = UsersRepository::getInstance()->getById($authorId);
        if (!$author) {
            throw new NotFoundHttpException();
        }
        cot_fillGroupsForUser($author);

        if (!COT_AJAX) {
            $this->addThanksWithoutAjax($source, $sourceId, $author);
        }

        $validate = ThanksService::validateThankAdd($source, $sourceId, $author, Cot::$usr['id']);
        if ($validate['error']) {
            return $this->controller->result([
                'success' => 0,
                'errors' => $validate['message'],
            ]);
        }

        if (!ThanksService::addThank($source, $sourceId, Cot::$usr['id'], $author['user_id'])) {
            return $this->controller->result([
                'success' => 0,
                'errors' => ['Unknown error'],
            ]);
        }

        ThanksService::notifyThankedUser($source, $sourceId, Cot::$usr['profile'], $author);

        $lastThankedLimit = (int) Cot::$cfg['plugin']['thanks']['maxthanked'];
        /**
         * @todo как вариант выбирать всех, и выводить в постах $lastThankedLimit, а остальных попапом
         * @see plugins/thanks/thanks.footer.last.php
         */
        $lastThankedData[$source] = ThanksRepository::getBySourceIds($source, [$sourceId], $lastThankedLimit);
        $thanksCounts[$source] = ThanksRepository::getCountsBySourceIds($source, [$sourceId]);

        $userThanksCount = ThanksUsersRepository::getCountById($authorId);

        $data = [
            'source' => $source,
            'sourceId' => $sourceId,
            'thanksCount' => $thanksCounts[$source][$sourceId] ?? 0,
            'data' => $lastThankedData[$source][$sourceId] ?? [],
        ];

        return $this->controller->result([
            'success' => 1,
            'message' => Cot::$L['thanks_done'],
            'whoThankedWidget' => ThanksHelper::renderWhoThankedItemWidget($data),
            'itemThanksCountWidget' => ThanksHelper::renderItemCountWidget($data),
            'itemThanksCount' => $data['thanksCount'],
            'userThanksCountWidget' => ThanksHelper::renderUserCountWidget([
                'userId' => $authorId,
                'thanksCount' => $userThanksCount,
            ]),
            'userThanksCount' => $userThanksCount,
            'userId' => $authorId,
        ]);
    }

    private function addThanksWithoutAjax(string $source, int $sourceId, array $author): void
    {
        $redirectUrl = COT_ABSOLUTE_URL;  // @todo не COT_ABSOLUTE_URL, с список благодарностей для объекта
        if (isset($_SERVER['HTTP_REFERER']) && mb_stripos($_SERVER['HTTP_REFERER'], 'a=thank&source=') === false) {
            $redirectUrl = $_SERVER['HTTP_REFERER'];
        }

        $validate = ThanksService::validateThankAdd($source, $sourceId, $author, Cot::$usr['id']);
        if ($validate['error']) {
            foreach ($validate['message'] as $message) {
                cot_error($message);
            }
            header('403 Forbidden');
            cot_redirect($redirectUrl);
        }

        if (ThanksService::addThank($source, $sourceId, Cot::$usr['id'], $author['user_id'])) {
            ThanksService::notifyThankedUser($source, $sourceId, Cot::$usr['profile'], $author);
            cot_message(Cot::$L['thanks_done']);
        }

        cot_redirect($redirectUrl);
    }
}