<!DOCTYPE html>
<html>
	<head>
		<link REL="SHORTCUT ICON" HREF="{vimage_path('favicon.ico')}">
		<meta name="viewport" content="width=device-width, initial-scale=1.0" />
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
		<meta name="robots" content="noindex" />
	</head>
	<body style="margin: 0;">
		<form id="roundcubeInterfaceForm" action="{$URL}" method="post" >
			{foreach key=NAME item=VALUE from=$POST_DATA}
				<input type="hidden" name="{$NAME}" value="{$VALUE}" />
			{/foreach}
		</form>
		<script>
			document.getElementById('roundcubeInterfaceForm').submit();
		</script>
	</body>
</html>
