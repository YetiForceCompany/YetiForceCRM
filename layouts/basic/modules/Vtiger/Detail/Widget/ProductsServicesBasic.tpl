{*<!-- {[The file is published on the basis of YetiForce Public License 3.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
{strip}
	<div class="c-detail-widget mb-3 js-detail-widget productsServicesWidgetContainer">
		<div class="widgetContainer_{$key} widgetContentBlock" data-url="{$WIDGET['url']}" data-name="{$WIDGET['label']}">
			<div class="c-detail-widget__header js-detail-widget-header">
				<span class="col-md-12 form-row align-items-center">
					<div class="form-row align-items-center">
						<div class="col-md-4">
							<h4>{\App\Language::translate($WIDGET['label'],$MODULE_NAME)}</h4>
						</div>
						<div class="col-md-8" align="center">
							<div class="btn-group" data-toggle="buttons">
								{foreach name=BTN item=COUNT key=MODULE_DATA from=Products_SummaryWidget_Model::getModulesAndCount($RECORD)}
									<label class="btn btn-sm btn-light my-1 {if $smarty.foreach.BTN.first}active{/if}" title="{App\Language::translate($MODULE_DATA,$MODULE_DATA)}">
										<input type="radio" name="mod" class="filterField" value="{$MODULE_DATA}" if {if $smarty.foreach.BTN.first}checked{/if}>
										<span class="u-cursor-pointer mx-1 userIcon-{$MODULE_DATA}"></span>
										<span class="badge">{$COUNT}</span>
									</label>
								{/foreach}
							</div>
						</div>
					</div>
				</span>
			</div>
			<div class="c-detail-widget__content js-detail-widget-content">
			</div>
		</div>
	</div>
{/strip}
