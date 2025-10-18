<!-- BEGIN: MAIN -->
<!-- IF !{IS_AJAX} -->
<div id="thanks">
    <div class="marginbottom10">
    {PAGE_BREADCRUMBS}
    </div>
    <h2 class="page">{PAGE_TITLE}</h2>
    {FILE "{PHP.cfg.themes_dir}/{PHP.usr.theme}/warnings.tpl"}

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
                <td class="coltop">{PHP.L.Category}</td>
                <td class="coltop">{PHP.L.Item}</td>
                <!-- IF {IS_ADMIN} -->
                <td class="coltop">{PHP.L.Action}</td>
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
                <td>
                    <!-- IF {ROW_CATEGORY_URL} --><a href="{ROW_CATEGORY_URL}">{ROW_CATEGORY_TITLE}</a><!-- ENDIF -->
                </td>
                <td>{ROW_HTML_TITLE}</td>
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