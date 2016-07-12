<!-- BEGIN: MAIN -->
<h2>{PHP.L.thanks_top} <small>({THANKS_TOTAL_USERS})</small></h2>
<!-- IF {PHP.count_last_days} -->
<p>{THANKS_RATING_INFO}</p>
<!-- ENDIF -->
<table class="cells">
	<tr>
		<td class="coltop">#</td>
		<td class="coltop">{PHP.L.User}</td>
		<td class="coltop">{THANKS_COUNT}</td>
		<!-- IF {PHP.show_totals} --><td class="coltop">{THANKS_TOTAL}</td><!-- ENDIF -->
	</tr>
	<!-- BEGIN: THANKS_ROW -->
	<tr>
		<td>{THANKS_ROW_NUM}</td>
		<td>{THANKS_ROW_NAME}</td>
		<td><a href="{THANKS_ROW_URL}">{THANKS_ROW_TOTALCOUNT}</a></td>
		<!-- IF {PHP.show_totals} --><td><a href="{THANKS_ROW_URL}">{THANKS_ROW_USERTOTAL}</a></td><!-- ENDIF -->
	</tr>
	<!-- END: THANKS_ROW -->
</table>

<p class="pagination">{PAGEPREV} {PAGENAV} {PAGENEXT}</p>

<!-- END: MAIN -->