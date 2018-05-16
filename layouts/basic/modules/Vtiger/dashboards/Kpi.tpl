{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
<script type="text/javascript">
	Vtiger_KpiBarchat_Widget_Js('Vtiger_Kpi_Widget_Js',{},{});
</script>
{strip}
	<div class="dashboardWidgetHeader">
		{foreach key=index item=cssModel from=$STYLES}
			<link rel="{$cssModel->getRel()}" href="{$cssModel->getHref()}" type="{$cssModel->getType()}" media="{$cssModel->getMedia()}" />
		{/foreach}
		{foreach key=index item=jsModel from=$SCRIPTS}
			<script type="{$jsModel->getType()}" src="{$jsModel->getSrc()}"></script>
		{/foreach}
		<table width="100%" cellspacing="0" cellpadding="0">
			<tbody>
				<tr>
					<td class="col-md-5">
						<h5 class="dashboardTitle u-text-ellipsis h6" title="{\App\Language::translate($WIDGET->getTitle(), $MODULE_NAME)}" style="width: 15em;"><b>&nbsp;&nbsp;{\App\Language::translate($WIDGET->getTitle(), $MODULE_NAME)}</b></h5>
					</td>
					<td class="refresh col-md-2" align="right">
						<span style="position:relative;">&nbsp;</span>
					</td>
					<td class="widgeticons col-md-5" align="right">
						<div class="box float-right">
							<a name="dfilter">
								<i class='icon-cog' border='0' align="absmiddle" title="{\App\Language::translate('LBL_FILTER')}" alt="{\App\Language::translate('LBL_FILTER')}" />
							</a>
							<!--
							<a class="dprint" name="dprint">
								<i class='icon-print' border='0' align="absmiddle" title="{\App\Language::translate('LBL_PRINT')}" alt="{\App\Language::translate('LBL_PRINT')}" />
							</a>
							-->
							<a href="javascript:void(0);" name="drefresh" data-url="{$WIDGET->getUrl()}&linkid={$WIDGET->get('linkid')}&content=data">
								<i class="fas fa-sync-alt" hspace="2" border="0" align="absmiddle" title="{\App\Language::translate('LBL_REFRESH')}" alt="{\App\Language::translate('LBL_REFRESH')}"></i>
							</a>
							{if !$WIDGET->isDefault()}
								<a name="dclose" class="widget" data-url="{$WIDGET->getDeleteUrl()}">
									<i class="fas fa-times" hspace="2" border="0" align="absmiddle" title="{\App\Language::translate('LBL_REMOVE')}" alt="{\App\Language::translate('LBL_REMOVE')}"></i>
								</a>
							{/if}
						</div>
					</td>
				</tr>
			</tbody>
		</table>



		<div class="row filterContainer d-none" style="position:absolute;z-index:100001">
			<div class="row">
				<span class="col-md-4">
					<span class="float-right">
						{\App\Language::translate('LBL_TIME', $MODULE_NAME)}
					</span>
				</span>
				<span class="col-md-8">
					<input type="text" name="time" title="{\App\Language::translate('LBL_CHOOSE_DATE')}" class="dateRangeField widgetFilter" />
				</span>
			</div>
			<div class="row">
				<span class="col-md-4">
					<span class="float-right">
						{\App\Language::translate('Services', $MODULE_NAME)}
					</span>
				</span>
				<span class="col-md-8">
					<select class="widgetFilter" name="service">
						<option value="">{\App\Language::translate('--None--', $MODULE_NAME)}</option>
						{foreach key=KEY item=ITEM from=$KPILIST}
							<option value="{$KEY}">{$ITEM}</option>
						{/foreach}
					</select>
				</span>
			</div>
			<div class="row">
				<span class="col-md-4">
					<span class="float-right">
						{\App\Language::translate('Types', $MODULE_NAME)}
					</span>
				</span>
				<span class="col-md-8">
					<select class="widgetFilter" name="type">
						<option value="">{\App\Language::translate('--None--', $MODULE_NAME)}</option>
						{foreach key=KEY item=ITEM from=$KPITYPES}
							<option value="{$KEY}">{$ITEM}</option>
						{/foreach}
					</select>
				</span>
			</div>
		</div>
	</div>
	<div class="dashboardWidgetContent">
		{include file=\App\Layout::getTemplatePath('dashboards/KpiContents.tpl', $MODULE_NAME)}
	</div>
{/strip}
