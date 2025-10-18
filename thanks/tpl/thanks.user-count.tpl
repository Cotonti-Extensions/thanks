<!-- BEGIN: MAIN -->
<!-- IF !{IS_AJAX} -->
<div class="thanks-author-thanks thanks-author-thanks-{USER_ID}" <!-- IF {THANKS_COUNT} === 0 -->style="display: none"<!-- ENDIF -->>
<!-- ENDIF -->
    <!-- IF {THANKS_COUNT} > 0 -->
    <a href="{THANKS_URL}" title="{PHP.L.thanks_forUser}">
        {PHP.L.thanks_thanked}: <br>
        <span class="count-times">{THANKS_COUNT_TIMES}</span>
    </a>
    <!-- ENDIF -->
<!-- IF !{IS_AJAX} -->
</div>
<!-- ENDIF -->
<!-- END: MAIN -->