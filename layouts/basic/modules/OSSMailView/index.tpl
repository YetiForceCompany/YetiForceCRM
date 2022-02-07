{*<!-- {[The file is published on the basis of YetiForce Public License 5.0 that can be found in the following directory: licenses/LicenseEN.txt or yetiforce.com]} -->*}
<div class="">
	<div class="clearfix">
		<div class="o-breadcrumb widget_header row">
			<div class="col-md-8">
				{include file=\App\Layout::getTemplatePath('BreadCrumbs.tpl', $MODULE_NAME)}
			</div>
		</div>
		<form class="js-form mt-2" data-js="validationEngine">
			<table>
				<tr>
					<td>
						<label class="col-form-label">{\App\Language::translate('Widget list limit', 'OSSMailView')}</label>
					</td>
					<td><input id="tab_email_view_widget_limit" class="form-control validate[custom[integer]]"
							value="{$WIDGET_CFG['email_list']['widget_limit']}" /></td>
				</tr>
				<tr>
					<td>
						<label class="col-form-label">{\App\Language::translate('List open email', 'OSSMailView')}</label>&nbsp;
					</td>
					<td>
						<select id="tab_email_view_open_window" class="form-control">
							<option value="_self"
								{if $WIDGET_CFG['email_list']['target'] eq '_self'}selected{/if}>{\App\Language::translate('_self', 'OSSMailView')}</option>
							<option value="_blank"
								{if $WIDGET_CFG['email_list']['target'] eq '_blank'}selected{/if}>{\App\Language::translate('_blank', 'OSSMailView')}</option>
						</select>
					</td>
				</tr>
			</table>
		</form>
	</div>
</div>
{literal}
	<script>
		jQuery(function() {
			$(".js-form").validationEngine(app.validationEngineOptions);
			var saveWidgetConfig = function(name, value, type) {
				var params = {
					'module': 'OSSMailScanner',
					'action': "SaveWidgetConfig",
					'conf_type': type,
					'name': name,
					'value': value
				}
				if ($(".js-form").validationEngine('validate')) {
					AppConnector.request(params).done(function(data) {
						var response = data['result'];
						if (response['success']) {
							var params = {
								text: response['data'],
								type: 'info'
							};
							app.showNotify(params);
						} else {
							var params = {
								text: response['data'],
								type: 'error'
							};
							app.showNotify(params);
						}
					});
				}
			}
			jQuery('#tab_email_view_widget_limit').on('blur', function() {
				saveWidgetConfig('widget_limit', jQuery(this).val(), 'email_list');
			});
			jQuery('#tab_email_view_open_window').on('change', function() {
				saveWidgetConfig('target', jQuery(this).val(), 'email_list');
			});

			jQuery('#email_permissions').select2();
			jQuery('#email_permissions').on('change', function() {
				saveWidgetConfig('permissions', jQuery(this).val(), 'email_list');
			});
		});
	</script>
{/literal}
