{include file="modules/Mobile/generic/Header.tpl"}

<body>

<table width=100% cellpadding=0 cellspacing=0 border=0>
	<tr class="toolbar">
		<td><a class="link" href="javascript:window.close();"><img src="resources/images/iconza/royalblue/undo_32x32.png" border="0"></a></td>
		<td width="100%">
			<h1 class='page_title'>
			<a class="link" href="javascript:void(0);"><img src="resources/images/iconza/yellow/left_arrow_24x24.png" border="0"></a>
			{$_MODULE->label}
			<a class="link" href="javascript:void(0);"><img src="resources/images/iconza/yellow/right_arrow_24x24.png" border="0"></a>
			</h1>
		</td>
		<td align="right" style="padding-right: 5px;"><a class="link" href="javascript:void(0);" onclick="$fnT('__listview__', '__searchbox__'); $fnFocus('__searchbox__q_');" target="_self"><img src="resources/images/iconza/yellow/lens_32x32.png" border="0"></a></td>
	</tr>
</table>


</body>

{include file="modules/Mobile/generic/Footer.tpl"}
