{include file="modules/Mobile/generic/Header.tpl"}

<body>

<table width=100% cellpadding=0 cellspacing=0 border=0>
<tr>
	<td>
		<h1 class='page_title'>vtiger CRM</h1>
	</td>
</tr>

<tr>
	<td>	
		<form method="post" action="index.php?_operation=loginAndFetchModules">
		
		<table width=100% cellpadding=5 cellspacing=0 border=0 class="panel_login">
		<tr>
			<td colspan="2">
				<p class='error'>{$errormsg}</p>
			</td>
		</tr>
		</table>

		</form>
	</td>
</tr>
</table>

</body>

{include file="modules/Mobile/generic/Footer.tpl"}