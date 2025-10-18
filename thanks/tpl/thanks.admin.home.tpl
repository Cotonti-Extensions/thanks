<!-- BEGIN: MAIN -->
<div id="thanks" class="block thanks">
    <h2>{PHP.L.thanks_title}</h2>
    <div class="wrapper">
        <!-- IF {COUNT} > 0 -->
        <ul>
            <!-- BEGIN: THANKS_ROW -->
            <li class="thanks-row">
                <div class="thanks-row-content">
                    <div>
                        <!-- IF {ROW_FROM_AVATAR_SRC} -->
                        <a href="{ROW_FROM_DETAILS_URL}"><img src="{ROW_FROM_AVATAR_SRC}" style="max-height: 30px;" /></a>
                        <!-- ENDIF -->
                        <a href="{ROW_FROM_DETAILS_URL}" class="strong"">{ROW_FROM_FULL_NAME}</a>
                        <span class="small marginright10">({ROW_DATE})</span>

                        {PHP.L.thanks_toUser}
                        <!-- IF {ROW_TO_AVATAR_SRC} -->
                        <a href="{ROW_TO_DETAILS_URL}"><img src="{ROW_TO_AVATAR_SRC}" style="max-height: 30px;" /></a>
                        <!-- ENDIF -->
                        <a href="{ROW_TO_DETAILS_URL}" class="strong"">{ROW_TO_FULL_NAME}</a>
                    </div>
                    <div>
                        {PHP.L.thanks_for} {ROW_HTML_TITLE}<!-- IF {ROW_CATEGORY_URL} -->,
                        {PHP.L.thanks_category} <a href="{ROW_CATEGORY_URL}">{ROW_CATEGORY_TITLE}</a><!-- ENDIF -->
                    </div>

                </div>
                <!-- IF {IS_ADMIN} -->
                <div class="thanks-row-admin">
                    {ROW_DELETE}
                </div>
                <!-- ENDIF -->
            </li>
            <!-- END: THANKS_ROW -->
        </ul>
        <!-- ELSE -->
        {PHP.L.thanks_none}
        <!-- ENDIF -->
        <p class="text-center" style="margin-bottom: 0">
            <a class="button" href="{PHP|cot_url('admin', 'm=other&p=thanks')}">{PHP.L.More}</a>
        </p>
    </div>
</div>
<style>
    .thanks-row {
        margin-bottom: 18px;
        display: flex;
    }
    .thanks-row-content {
        flex-grow: 1;
    }
</style>
<!-- END: MAIN -->