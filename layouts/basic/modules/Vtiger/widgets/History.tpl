{strip}
	<div class="summaryWidgetContainer">
		<div class="widgetContainer_{$key} widgetContentBlock" data-url="{$WIDGET['url']}" data-name="{$WIDGET['label']}" data-type="{$WIDGET['type']}">
			<div class="widget_header">
				<div class="widgetTitle row">
					<div class="col-xs-7">
						<h4 class="moduleColor_{$WIDGET['label']}">{vtranslate($WIDGET['label'],$MODULE_NAME)}</h4>
					</div>
					<div class="col-xs-5">
						<select class="select2 relatedHistoryTypes" multiple>
							<option value="ModComments">{vtranslate('ModComments', 'ModComments')}</option>	
							<option value="Emails">{vtranslate('Emails', $MODULE_NAME)}</option>	
							<option value="Calendar">{vtranslate('Calendar', 'Calendar')}</option>	
						</select>
						{*<select class="select2" multiple>
							{foreach from=$WIDGET['types'] item=MODULE_FILTER}
								<option value="{$MODULE_FILTER}">{vtranslate($MODULE_FILTER, $MODULE_FILTER)}</option>	
							{/foreach}
						</select>*}
					</div>
				</div>
			</div>
			<div class="widget_contents widgetContent">
			</div>
		</div>
	</div>
{/strip}
