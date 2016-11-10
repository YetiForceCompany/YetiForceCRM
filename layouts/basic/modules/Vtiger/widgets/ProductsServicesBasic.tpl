{*<!-- {[The file is published on the basis of YetiForce Public License that can be found in the following directory: licenses/License.html]} --!>*}
{strip}
	<div class="summaryWidgetContainer productsServicesWidgetContainer">
		<div class="widgetContainer_{$key} widgetContentBlock" data-url="{$WIDGET['url']}" data-name="{$WIDGET['label']}">
			<div class="widget_header row">
				<span class="col-md-12 margin0px">
					<div class="row">
						<div class="col-md-4">
							<span class="{$span} margin0px"><h4>{vtranslate($WIDGET['label'],$MODULE_NAME)}</h4></span>
						</div>
						<div class="col-md-8" align="center" style="padding-right: 40px;">
							<div class="btn-group" data-toggle="buttons">
								{foreach name=BTN item=COUNT key=MODULE_DATA from=Products_SummaryWidget_Model::getModulesAndCount($RECORD)}
									<label class="btn btn-xs btn-default {if $smarty.foreach.BTN.first}active{/if}" title="{App\Language::translate($MODULE_DATA,$MODULE_DATA)}">
										<input type="radio" name="mod" class="filterField" value="{$MODULE_DATA}" if {if $smarty.foreach.BTN.first}checked{/if}>
										<span class="cursorPointer userIcon-{$MODULE_DATA}"></span>&nbsp;&nbsp;
										<span class="badge">{$COUNT}</span>
									</label>
								{/foreach}
							</div>
						</div>
					</div>
				</span>
			</div>
			<div class="widget_contents">
			</div>
		</div>
	</div>
{/strip}
