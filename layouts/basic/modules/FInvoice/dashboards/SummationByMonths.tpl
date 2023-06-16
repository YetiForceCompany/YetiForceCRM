{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{assign var=CONF_DATA value=\App\Json::decode(html_entity_decode($WIDGET->get('data')))}
<script type="text/javascript">
	YetiForce_Bar_Widget_Js('YetiForce_SummationByMonths_Widget_Js', {}, {
		getBasicOptions: function getBasicOptions() {
			let options = this._super();
			options.yAxis = {
				{if !empty($CONF_DATA['plotTickSize'])}
					interval: {$CONF_DATA['plotTickSize']},
				{/if}
				{if !empty($CONF_DATA['plotLimit'])}
					max: {$CONF_DATA['plotLimit']},
				{/if}
			};

			return options;
		},
	});
</script>
{strip}
	<div class="dashboardWidgetHeader">
		<div class="d-flex flex-row flex-nowrap no-gutters justify-content-between">
			{include file=\App\Layout::getTemplatePath('dashboards/WidgetHeaderTitle.tpl', $MODULE_NAME)}
			{include file=\App\Layout::getTemplatePath('dashboards/WidgetHeaderButtons.tpl', $MODULE_NAME)}
		</div>
		<hr class="widgetHr" />
		<div class="row no-gutters">
			{foreach from=$FILTER_FIELDS item=FIELD_MODEL key=FIELD_NAME}
				<div class="col-ceq-xsm-6">
					<div class="input-group input-group-sm">
						<span class="input-group-prepend">
							<span class="input-group-text">
								<span class="{if $FIELD_MODEL->get('icon')}{$FIELD_MODEL->get('icon')}{else}fas fa-filter{/if} iconMiddle margintop3"
									title="{\App\Language::translate($FIELD_MODEL->get('label'), $FIELD_MODEL->getModuleName())}"></span>
							</span>
						</span>
						{assign var=FIELD_VALUE value=$FIELD_MODEL->get('fieldvalue')}
						<select class="widgetFilter select2 form-control" aria-label="Small"
							aria-describedby="inputGroup-sizing-sm" name="{$FIELD_MODEL->getName()}"
							title="{\App\Language::translate($FIELD_MODEL->get('label'), $FIELD_MODEL->getModuleName())}">
							{foreach item=VALUE key=KEY from=$FIELD_MODEL->getPicklistValues()}
								<option value="{\App\Purifier::encodeHtml($KEY)}" {if $FIELD_VALUE eq $KEY} selected{/if}>{\App\Purifier::encodeHtml($VALUE)}</option>
							{/foreach}
						</select>
					</div>
				</div>
			{/foreach}
			<div class="col-ceq-xsm-6">
				{include file=\App\Layout::getTemplatePath('dashboards/SelectAccessibleTemplate.tpl', $MODULE_NAME)}
			</div>
		</div>
	</div>
	<div class="dashboardWidgetContent">
		{include file=\App\Layout::getTemplatePath('dashboards/SummationByMonthsContents.tpl', $MODULE_NAME)}
	</div>
{/strip}
