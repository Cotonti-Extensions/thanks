<!-- BEGIN: MAIN -->
<!-- IF !{IS_AJAX} -->
<div id="thanks">
    <h2 class="page">{PAGE_TITLE_HTML}</h2>
    {FILE "{PHP.cfg.themes_dir}/{PHP.usr.theme}/warnings.tpl"}

    <!-- IF {AUTHOR_ID} -->
    <div style="display: flex; align-items: center; gap: 10px;  margin-bottom: 15px">
        {PHP.L.Author}:
        <!-- IF {AUTHOR_AVATAR_SRC} -->
        <a href="{AUTHOR_DETAILS_URL}"><img src="{AUTHOR_AVATAR_SRC}" style="max-height: 30px;" /></a>
        <!-- ENDIF -->
        <a href="{AUTHOR_DETAILS_URL}" style="font-size: 1.1em; font-weight: bold">{AUTHOR_FULL_NAME}</a>
        <!-- IF {AUTHOR_BANNED} -->
        ({PHP.L.Banned})
        <!-- ENDIF -->
    </div>
    <!-- ENDIF -->

    <!-- IF {COUNT} < 1 -->
    <div class="textcenter">{PHP.L.thanks_none}</div>
    <!-- ENDIF -->

    <div id="ajaxBlock">
<!-- ENDIF -->
        <!-- IF {COUNT} > 0 -->
        <table class="cells">
            <tr>
                <td class="coltop" style="width: 14%">{PHP.L.Date}</td>
                <td class="coltop">{PHP.L.Sender}</td>
                <!-- IF {IS_ADMIN} -->
                <td class="coltop" style="width: 10%">{PHP.L.Action}</td>
                <!-- ENDIF -->
            </tr>
            <!-- BEGIN: THANKS_ROW -->
            <tr>
                <td>{ROW_DATE}</td>
                <td>
                    <div style="display: inline-flex; align-items: center; gap: 5px; line-height: normal">
                        <!-- IF {ROW_FROM_AVATAR_SRC} -->
                        <a href="{ROW_FROM_DETAILS_URL}"><img src="{ROW_FROM_AVATAR_SRC}" style="max-height: 30px;" /></a>
                        <!-- ENDIF -->
                        <a href="{ROW_FROM_DETAILS_URL}">{ROW_FROM_FULL_NAME}</a>
                    </div>
                </td>
                <!-- IF {IS_ADMIN} -->
                <td class="centerall">{ROW_DELETE}</td>
                <!-- ENDIF -->
            </tr>
            <!-- END: THANKS_ROW -->
        </table>
        <!-- ENDIF -->
        <!-- IF {PAGINATION} -->
        <p class="paging">
            <span>{PHP.L.Page} {CURRENT_PAGE} {PHP.L.Of} {TOTAL_PAGES}</span>
            {PREVIOUS_PAGE}{PAGINATION}{NEXT_PAGE}
        </p>
        <!-- ENDIF -->
<!-- IF !{IS_AJAX} -->
    </div>
</div>
<!-- ENDIF -->
<!-- END: MAIN -->