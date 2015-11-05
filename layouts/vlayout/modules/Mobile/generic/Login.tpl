{include file="modules/Mobile/generic/Header.tpl"}
<body>
<table width=100% cellpadding=0 cellspacing=0 border=0>
<tr>
	<td>
		<h1 class='page_title'>YetiForce mobile CRM</h1>
	</td>
</tr>
<tr>
	<td>	
		<form method="post" action="index.php?_operation=loginAndFetchModules">
		<table width=100% cellpadding=5 cellspacing=0 border=0 class="panel_login">
		<tr>
			<td colspan="2">
				{if $_ERR}<p class='error'>{$_ERR}</p>
				{else}<p>Please login to proceed...</p>{/if}
			</td>
		</tr>
		<tr>
			<td width="40px">Username</td>
			<td><input type="text" name="username" value=""/></td>
		</tr>
		<tr>
			<td>Password</td>
			<td><input type="password" name="password" value=""/></td>
		</tr>
		<tr>
			<td colspan="2">
				<input type="submit" value="Login" class="button">
			</td>
		</tr>
		</table>

		</form>
	</td>
</tr>
</table>
</body>
{include file="modules/Mobile/generic/Footer.tpl"}