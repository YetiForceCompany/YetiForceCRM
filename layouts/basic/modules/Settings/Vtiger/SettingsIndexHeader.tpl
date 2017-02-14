{strip}
	<div class="widget_header row ">
		<div class="col-xs-12">
			{include file='BreadCrumbs.tpl'|@vtemplate_path:$MODULE}
		</div>
	</div>
	<div class="row no-margin">
		<ul class="nav nav-tabs massEditTabs">
			{*<li  data-mode="DonateUs"><a data-toggle="tab"><strong>{vtranslate('LBL_DONATE_US', $QUALIFIED_MODULE)}</strong></a></li>*}
			<li class="active" data-mode="index" data-params="{Vtiger_Util_Helper::toSafeHTML(\App\Json::encode(['count'=>$WARNINGS_COUNT]))}"><a data-toggle="tab"><strong>{vtranslate('LBL_START', $QUALIFIED_MODULE)}</strong></a></li>
			<li data-mode="github"><a data-toggle="tab"><strong>{vtranslate('LBL_GITHUB', $QUALIFIED_MODULE)}</strong></a></li>
			<li data-mode="systemWarnings"><a data-toggle="tab"><strong>{vtranslate('LBL_SYSTEM_WARNINGS', $QUALIFIED_MODULE)}</strong></a></li>
		</ul>
	</div>
	<div class="indexContainer"></div>
{/strip}
