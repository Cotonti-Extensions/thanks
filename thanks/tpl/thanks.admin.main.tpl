<!-- BEGIN: MAIN -->
{FILE "{PHP.cfg.system_dir}/admin/tpl/warnings.tpl"}

<div class="block">
    <form id="thanks-filter" action="{FILTER_FORM_ACTION}" method="GET">
        {FILTER_PARAMS}
        <div id="thanks-filter-inputs">
            <label for="filter-from" class="thanks-filter-label">{PHP.L.thanks_from}:</label> {FILTER_FROM}
            <label for="filter-to" class="thanks-filter-label">{PHP.L.thanks_toUser}:</label> {FILTER_TO}
        </div>
        <div class="marginbottom10">{PHP.L.thanks_filterDesc}</div>
        <button type="submit">{PHP.L.Filter}</button>
        <a href="{PHP|cot_url('admin', 'm=thanks')}" style="margin-left: 15px">{PHP.L.thanks_clear_filters}</a>
    </form>

    <!-- IF {FROM_USER_ID} > 0 OR {TO_USER_ID} > 0  -->
    <div id="thanks-selected-users">
        <!-- IF {FROM_USER_ID} > 0 -->
        <div class="thanks-selected-user-row">
            <p><strong>{PHP.L.thanks_fromUser}:</strong></p>
            <div class="thanks-selected-user">
                <a href="{FROM_USER_DETAILS_URL}"><!-- IF {FROM_USER_AVATAR_SRC} -->
                    <img src="{FROM_USER_AVATAR_SRC}" class="avatar" />
                    <!-- ELSE -->
                    <img src="{PHP.R.users_defaultAvatarSrc}" class="avatar" />
                    <!-- ENDIF -->
                </a>
                <div>
                    <h4><a href="{FROM_USER_DETAILS_URL}">{FROM_USER_FULL_NAME}</a></h4>
                    {FROM_USER_DELETE_FROM}<br>{FROM_USER_DELETE_TO}
                </div>
            </div>
        </div>
        <!-- ENDIF -->
        <!-- IF {TO_USER_ID} > 0 -->
        <div class="thanks-selected-user-row">
            <p><strong>{PHP.L.thanks_forUser}:</strong></p>
            <div class="thanks-selected-user">
                <a href="{TO_USER_DETAILS_URL}"><!-- IF {TO_USER_AVATAR_SRC} -->
                    <img src="{TO_USER_AVATAR_SRC}" class="avatar" />
                    <!-- ELSE -->
                    <img src="{PHP.R.users_defaultAvatarSrc}" class="avatar" />
                    <!-- ENDIF -->
                </a>
                <div>
                    <h4><a href="{TO_USER_DETAILS_URL}">{TO_USER_FULL_NAME}</a></h4>
                    {TO_USER_DELETE_FROM}<br>{TO_USER_DELETE_TO}
                </div>
            </div>
        </div>
        <!-- ENDIF -->
    </div>
    <!-- ENDIF -->

    <div id="thanks-container">
        <!-- IF {COUNT} > 0 -->
        <table class="cells">
            <tr>
                <th style="width: 14%">{PHP.L.Date}</th>
                <th>{PHP.L.Sender}</th>
                <th>{PHP.L.Recipient}</th>
                <th>{PHP.L.Item}</th>
                <!-- IF {IS_ADMIN} -->
                <th>{PHP.L.Action}</th>
                <!-- ENDIF -->
            </tr>
            <!-- BEGIN: THANKS_ROW -->
            <tr>
                <td>{ROW_DATE}</td>
                <td>
                    <div class="thanks-user">
                        <!-- IF {ROW_FROM_AVATAR_SRC} -->
                        <a href="{ROW_FROM_DETAILS_URL}"><img src="{ROW_FROM_AVATAR_SRC}" style="max-height: 30px;" /></a>
                        <!-- ENDIF -->
                        <a href="{ROW_FROM_DETAILS_URL}">{ROW_FROM_FULL_NAME}</a>
                        <!-- IF {ROW_FROM_USER_FILTER_URL} -->
                        <a href="{ROW_FROM_USER_FILTER_URL}">{PHP.R.admin_icon_page}</a>
                        <!-- ENDIF -->
                    </div>
                </td>
                <td>
                    <div class="thanks-user">
                        <!-- IF {ROW_TO_AVATAR_SRC} -->
                        <a href="{ROW_TO_DETAILS_URL}"><img src="{ROW_TO_AVATAR_SRC}" style="max-height: 30px;" /></a>
                        <!-- ENDIF -->
                        <a href="{ROW_TO_DETAILS_URL}">{ROW_TO_FULL_NAME}</a>
                        <!-- IF {ROW_TO_USER_FILTER_URL} -->
                        <a href="{ROW_TO_USER_FILTER_URL}">{PHP.R.admin_icon_page}</a>
                        <!-- ENDIF -->
                    </div>
                </td>
                <td>
                    {ROW_HTML_TITLE}
                    <!-- IF {ROW_CATEGORY_URL} -->
                    <br>{PHP.L.thanks_category} <a href="{ROW_CATEGORY_URL}">{ROW_CATEGORY_TITLE}</a>
                    <!-- ENDIF -->
                </td>
                <!-- IF {IS_ADMIN} -->
                <td>{ROW_DELETE}</td>
                <!-- ENDIF -->
            </tr>
            <!-- END: THANKS_ROW -->
        </table>
        <!-- ELSE -->
        <div class="text-center marginbottom10 strong">{PHP.L.thanks_none}</div>
        <!-- ENDIF -->

        <!-- IF {PAGINATION} -->
        <p class="paging">
            <span>{PHP.L.Page} {CURRENT_PAGE} {PHP.L.Of} {TOTAL_PAGES}</span>
            {PREVIOUS_PAGE}{PAGINATION}{NEXT_PAGE}
        </p>
        <!-- ENDIF -->
    </div>
</div>
<!-- IF !{IS_AJAX} -->
<script>
    $(document).ready(function() {
        $(document).on('focus', '.user-input', function() {
            $('.user-input').autocomplete(
                'index.php?r=autocomplete',
                { multiple: false, minChars: {PHP.cfg.plugin.autocomplete.autocomplete} }
            );
        });
    });
</script>
<!-- ENDIF -->
<!-- END: MAIN -->