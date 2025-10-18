<?php
/**
 * Thanks / RU Locale
 *
 * @package thanks
 * @author Trustmaster & Cotonti team
 * @copyright Copyright (c) 2011-2015 Vladimir Sibirov, 2016-2025 Cotonti team
 * @license BSD
 */

declare(strict_types=1);

defined('COT_CODE') or die('Wrong URL');

/**
 * Plugin Info
 */
$L['info_name'] = 'Thanks';
$L['info_desc'] = 'Плагин благодарностей';
$L['info_notes'] = '';

$L['thanks_meta_title'] = 'Благодарности пользователям';
$L['thanks_meta_desc'] = 'Список пользователей сайта с количеством благодарностей (лайков)';

/**
 * Plugin Config
 */
$L['cfg_maxday'] = 'Лимит благодарностей в день';
$L['cfg_maxday_hint'] = 'Сколько раз в день пользователь может сказать спасибо. "0" - неограниченно';
$L['cfg_maxuser'] = 'Лимит благодарностей в день одному получателю';
$L['cfg_maxuser_hint'] = 'Сколько раз в день пользователь может поблагодарить другого пользователя. Отдельно для каждого получателя. "0" - неограниченно';
$L['cfg_maxrowsperpage'] = 'Количество благодарностей на страницу';
$L['cfg_maxthanked'] = 'Количество последних поблагодаривших для объекта';
$L['cfg_maxthanked_hint'] = 'В списках объектов(посты, комментарии и т.п) "0" - вывести всех, "-1" отключить';
$L['cfg_short'] = 'Короткая форма вывода поблагодаривших для объекта';
$L['cfg_short_hint'] = 'Только имена (без дат)';
$L['cfg_count_last_days'] = 'Отображать рейтинг на основе статистики за последние ## дней';
$L['cfg_count_last_days_hint'] = 'Оставьте поле пустым или введите "0" для учета всей статистики';
$L['cfg_page_on'] = 'Включить для страниц';
$L['cfg_forums_on'] = 'Включить для постов на форуме';
$L['cfg_comments_on'] = 'Включить для комментариев';
$L['cfg_notifications'] = 'Уведомления:';
$L['cfg_notify_by_email'] = 'Сообщение на электронную почту о новом лайке';
$L['cfg_notify_from'] = '"Reply-to" для уведомлений по электронной почте';
$L['cfg_notify_from_hint'] = 'Если пустое, будет использовано '
    . '<a href="' . cot_url('admin', ['m' => 'config', 'n' => 'edit', 'o' => 'core', 'p' => 'main']) . '"> значение по-умолчанию:</a> '
    . '"<strong>' . Cot::$cfg['adminemail'] . '</strong>"';
$L['cfg_notify_by_pm'] = 'Сообщение в личку о новом лайке';

/**
 * Plugin Body
 */
$L['thanks_addNew'] = 'Сказать спасибо!';
$L['thanks_category'] = 'категория';
$L['thanks_clear_filters'] = 'Очистить фильтры';
$L['thanks_commentToPage'] = 'Комментарий к странице';
$L['thanks_commentToPoll'] = 'Комментарий к опросу';
$L['thanks_commentTo'] = 'Комментарий к';
$L['thanks_deleteAllFromUser'] = 'Удалить все благодарности от {$userName}';
$L['thanks_deleteAllToUser'] = 'Удалить все благодарности пользователю {$userName}';
$L['thanks_deleteOne'] = 'Удалить данную благодарность';
$L['thanks_deleted'] = 'Удалено {$n}';
$L['thanks_deletedOne'] = 'Благодарность удалена';
$L['thanks_ensure'] = 'Вы хотите поблагодарить этого пользователя?';
$L['thanks_filterDesc'] = 'Чтобы посмотреть или удалить все благодарности пользователя, выберите его в фильтре';
$L['thanks_for'] = 'за';
$L['thanks_forMe'] = 'Благодарности мне';
$L['thanks_forMeDesc'] = 'Список моих благодарностей (лайков)';
$L['thanks_forUser'] = 'Благодарности пользователю';
$L['thanks_forUserDesc'] = 'Список благодарностей (лайков) пользователю';
$L['thanks_forums_post'] = 'Пост на форуме';
$L['thanks_from'] = 'от';
$L['thanks_fromUser'] = 'Благодарности от пользователя';
$L['thanks_lastThanked'] = 'Последние поблагодарившие';
$L['thanks_none'] = 'Благодарности отсутствуют';
$L['thanks_notificationBody'] = '{$userName},<br>пользователь {$fromUser} поблагодарил Вас за {$item}';
$L['thanks_notificationSubject'] = 'Вам сказали спасибо!';
$L['thanks_postInTopic'] = 'Пост в теме';
$L['thanks_rated'] = 'Благодарностей';
$L['thanks_ratedForPeriod'] = 'Благодарностей за период';
$L['thanks_ratingPeriodDesc'] = 'Рейтинг основан на статистике за последние {$days}.';
$L['thanks_ratingPeriodUsersCountDesc'] = 'За этот период благодарности получили {$users}.';
$L['thanks_title'] = 'Благодарности пользователям';
$L['thanks_title_short'] = 'Благодарности';
$L['thanks_thanked'] = 'Поблагодарили';
$L['thanks_thanks'] = 'Благодарностей';
$L['thanks_toUser'] = 'пользователю';
$L['thanks_top'] = 'Топ благодарностей пользователям';

$L['thanks_declension'] = "благодарность,благодарности,благодарностей";

/**
 * Error Messages
 */
$L['thanks_err_maxday'] = 'Извините, сегодня благодарить больше не получится';
$L['thanks_err_maxuser'] = 'Извините, этого пользователя поблагодарить сегодня снова нельзя';
$L['thanks_err_item'] = 'Извините, нельзя благодарить за один элемент дважды';
$L['thanks_err_self'] = 'Вы не можете поблагодарить себя';
$L['thanks_err_banned'] = 'Пользователь забанен';
$L['thanks_err_inactive'] = 'Учетная запись пользователя не активирована';
$L['thanks_err_cantBanned'] = 'Нельзя поблагодарить. Пользователь забанен';
$L['thanks_err_request'] = 'Ошибка при отправке запроса. Попробуйте обновить страницу и поблагодарить снова.';
$L['thanks_err_wrong_parameter'] = 'Ошибка в параметре запроса';

$L['thanks_done'] = 'Вы поблагодарили автора';
