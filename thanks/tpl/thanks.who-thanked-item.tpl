<!-- BEGIN: MAIN -->
<!-- IF !{IS_AJAX} -->
<div id="thanks-who-thanked-{SOURCE}-{SOURCE_ID}" class="thanks thanks-who-thanked thanks-{SOURCE}-thanks-who-thanked" <!-- IF {THANKS_COUNT} === 0 -->style="display: none"<!-- ENDIF -->>
<!-- ENDIF -->
    <!-- IF {THANKS_COUNT} > 0 -->
        <hr>
        <!-- IF {THANKS_COUNT} > {PHP.cfg.plugin.thanks.maxthanked} -->
        {PHP.L.thanks_lastThanked}:
        <!-- ELSE -->
        {PHP.L.thanks_thanked}:
        <!-- ENDIF -->
        <!-- BEGIN: USER_ROW -->
        <span class="thanks-who-thanked-row">
        <a href="{USER_ROW_DETAILSLINK}">{USER_ROW_FULL_NAME}</a><!-- IF !{PHP.cfg.plugin.thanks.short} -->
        ({USER_ROW_THANK_DATE})<!-- ENDIF  --><!-- IF {USER_ROW_NUMBER} < {THANKS_LAST_COUNT} -->,<!-- ENDIF -->
        </span>
        <!-- END: USER_ROW -->
        <!-- {PHP.cfg.plugin.thanks.maxthanked} -->
        <div><a href="{THANKS_LIST_URL}">{PHP.L.Total}: {THANKS_COUNT}</a></div>
        <!-- ENDIF -->
    <!-- ENDIF -->
<!-- IF !{IS_AJAX} -->
</div>
<!-- ENDIF -->
<!-- END: MAIN -->