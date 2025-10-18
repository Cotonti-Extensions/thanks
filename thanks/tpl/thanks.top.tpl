<!-- BEGIN: MAIN -->
<!-- IF !{IS_AJAX} -->
<h2 class="users">{TITLE}<!-- IF {THANKS_TOTAL_USERS} > 0 --> <small>({THANKS_TOTAL_USERS})</small><!-- ENDIF --></h2>
<!-- IF {PERIOD_DESCRIPTION} -->
<p class="desc" style="margin-bottom: 15px; text-align: center">{PERIOD_DESCRIPTION} {PERIOD_USERS_COUNT_DESCRIPTION}</p>
<!-- ENDIF -->
<div id="ajaxBlock">
<!-- ENDIF -->
	<table class="cells">
		<tr>
			<td class="coltop">#</td>
			<td class="coltop">{PHP.L.User}</td>
			<td class="coltop">{COUNT_TITLE_LINK} {SORT_BY_COUNT_WAY_LINK}</td>
			<!-- IF {SHOW_TOTALS} --><td class="coltop">{TOTAL_TITLE_LINK} {SORT_BY_TOTAL_WAY_LINK}</td><!-- ENDIF -->
		</tr>
		<!-- BEGIN: USER_ROW -->
		<tr>
			<td class="textcenter">{USER_ROW_NUM}</td>
			<td>
				<div style="display: inline-flex; align-items: center; gap: 5px; line-height: normal">
					<!-- IF {USER_ROW_AVATAR_SRC} -->
					<a href="{USER_ROW_DETAILS_URL}"><img src="{USER_ROW_AVATAR_SRC}" style="max-height: 30px;" /></a>
					<!-- ENDIF -->
					<a href="{USER_ROW_DETAILS_URL}">{USER_ROW_FULL_NAME}</a>
				</div>
			</td>
			<td class="textcenter"><a href="{USER_ROW_THANKS_URL}">{USER_ROW_THANKS_COUNT}</a></td>
			<!-- IF {SHOW_TOTALS} -->
			<td class="textcenter"><a href="{USER_ROW_THANKS_URL}">{USER_ROW_THANKS_TOTAL}</a></td>
			<!-- ENDIF -->
		</tr>
		<!-- END: USER_ROW -->
	</table>

	<!-- IF {PAGINATION} -->
	<p class="paging">{PREVIOUS_PAGE}{PAGINATION}{NEXT_PAGE}</p>
	<!-- ENDIF -->
<!-- IF !{IS_AJAX} -->
</div>
<!-- ENDIF -->
<!-- END: MAIN -->