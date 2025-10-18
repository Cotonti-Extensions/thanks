<!-- BEGIN: MAIN -->
<!-- IF !{IS_AJAX} -->
<div id="thanks-item-count-{SOURCE}-{SOURCE_ID}" class="thanks item-count thanks-{SOURCE}-item-count" <!-- IF {THANKS_COUNT} === 0 -->style="display: none"<!-- ENDIF -->>
<!-- ENDIF -->
    <!-- IF {THANKS_COUNT} > 0 -->
    {PHP.L.thanks_thanks}: {THANKS_COUNT}
    <!-- ENDIF -->
<!-- IF !{IS_AJAX} -->
</div>
<!-- ENDIF -->
<!-- END: MAIN -->