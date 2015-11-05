{*<!--
/*+***********************************************************************************************************************************
 * The contents of this file are subject to the YetiForce Public License Version 1.1 (the "License"); you may not use this file except
 * in compliance with the License.
 * Software distributed under the License is distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either express or implied.
 * See the License for the specific language governing rights and limitations under the License.
 * The Original Code is YetiForce.
 * The Initial Developer of the Original Code is YetiForce. Portions created by YetiForce are Copyright (C) www.yetiforce.com. 
 * All Rights Reserved.
 *************************************************************************************************************************************/
-->*}
<script type="text/javascript" src="libraries/bootstrap/js/bootstrap-tab.js"></script>
<style>

</style>
<div class="main_content">
<ul id="tabs" class="nav nav-tabs nav-justified" data-tabs="tabs" style="margin: 20px;">
    <li class="active"><a data-toggle="tab" href="#cfg">{vtranslate('SETTINGS', $MODULENAME)}</a></li>
</ul>
<div id="my-tab-content" class="tab-content" style="margin: 0 30px;" >
	<div class=" tab-pane active" id="cfg">
		<div class="alert alert-block alert-info fade in">
			<button type="button" class="close" data-dismiss="alert">×</button>
			<h3 class="alert-heading"><i class="glyphicon glyphicon-info-sign"></i>  {vtranslate('Alert_title', $MODULENAME)}</h3>
			<p>{vtranslate('Alert_desc', $MODULENAME)}</p>
		</div>	
		{foreach item=item key=key from=$CONFIG}
			<div class="row">
				<div class="col-md-6">
					<h5>{vtranslate($key, $MODULENAME)}</h5>
				</div>
				<div class="col-md-6">
					<input id="{$key}" name="{$key}" type="checkbox" value="1" {if $item eq 1} checked {/if}/>
				</div>
			</div>
		{/foreach}
    </div>
</div>
</div>
{literal}
<script>
	function saveConfig(param, value) {
		var params = {
			'module': 'OSSCosts',
			'action': "SaveConfig",
			'param': param,
			'value': value
		}
		AppConnector.request(params).then(
			function(data) {
				var response = data['result'];
				if (response['success']) {
					var params = {
						text: response['data'],
						type: 'info',
						animation: 'show'
					};
					Vtiger_Helper_Js.showPnotify(params);
				} else {
					var params = {
						text: response['data'],
						animation: 'show'
					};
					Vtiger_Helper_Js.showPnotify(params);
				}
			},
			function(data, err) {

			}
		);
	}
</script>
{/literal}
<script>
{foreach item=row key=key from=$CONFIG}
	jQuery('#{$key}').on('change', function() {
		saveConfig('{$key}',jQuery(this).attr('checked'));
	});
{/foreach}
</script>
