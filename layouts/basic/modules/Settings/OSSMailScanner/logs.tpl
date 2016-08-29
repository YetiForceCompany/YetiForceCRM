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
    .table tbody tr.error > td {
        background-color: #f2dede;
    }
    .table th, .table td {
        padding: 3px;
    }
</style>
    <div class='editViewContainer ' id="tab_cron">
		<div class="widget_header row">
		<div class="col-xs-12">
			{include file='BreadCrumbs.tpl'|@vtemplate_path:$MODULE}
		</div>
	</div>
        <table class="">
            <tr>
                <td><button class="btn btn-success" id="run_cron" type="button" {if $STOP_BUTTON_STATUS neq 'false'}disabled{/if}>{vtranslate('RunCron', 'OSSMailScanner')}</button></td>
            </tr>
            </table><br />   
		<div class="row col-xs-12">
			<div  class="row col-sm-10 col-md-8 col-lg-7 marginBottom10px" >
				<div class="row col-sm-4">{vtranslate('email_to_notify', 'OSSMailScanner')}: &nbsp;</div>
				<div class="col-sm-7"><input type="text" class="form-control" title="{vtranslate('email_to_notify', 'OSSMailScanner')}" name="email_to_notify" value="{$WIDGET_CFG['cron']['email']}" /></div>
			</div>
			<div class='row col-sm-10 col-md-8 col-lg-7 marginBottom10px'>
				<div class="row col-sm-4">{vtranslate('time_to_notify', 'OSSMailScanner')}: &nbsp;</div>
				<div class="col-sm-7"><input type="text" name="time_to_notify" title="{vtranslate('time_to_notify', 'OSSMailScanner')}" class="form-control" value="{$WIDGET_CFG['cron']['time']}" /></div>
			</div>
		</div>
		<div class="pull-right">
		<select class="col-md-1 form-control" name="page_num" title="{vtranslate('LBL_PAGE_NUMBER', $QUALIFIED_MODULE)}">
						{if $HISTORYACTIONLIST_NUM eq 0}<option vlaue="1">1</option>{/if}
			{for $i=1 to $HISTORYACTIONLIST_NUM}
			<option vlaue="{$i}">{$i}</option>
			{/for}
		</select>
		</div>
			<table class="table tableRWD table-bordered log-list">
				<thead>
					<tr class="listViewHeaders">
						<th>{vtranslate('No', 'OSSMailScanner')}.</th>
						<th>{vtranslate('startTime', 'OSSMailScanner')}</th>
						<th>{vtranslate('endTime', 'OSSMailScanner')}</th>
						<th>{vtranslate('status', 'OSSMailScanner')}</th>
						<th>{vtranslate('who', 'OSSMailScanner')}</th>
						<th>{vtranslate('count', 'OSSMailScanner')}</th>
						<th>{vtranslate('stop_user', 'OSSMailScanner')}</th>
						<th>{vtranslate('Action', 'OSSMailScanner')}</th>
						<th>{vtranslate('Desc', 'OSSMailScanner')}</th>
						<th></th>
					</tr>
				</thead>
				{foreach item=item key=key from=$HISTORYACTIONLIST}
					<tr>
						<td>{$item['id']}</td>
						<td>{$item['start_time']}</td>
						<td>{$item['end_time']}</td>
						<td>{vtranslate($item['status'], 'OSSMailScanner')}</td>
						<td>{$item['user']}</td>
						<td>{$item['count']}</td>
						<td>{$item['stop_user']}</td>
						<td>{vtranslate($item['action'], 'OSSMailScanner')}</td>
						<td>{$item['info']}</td>
						<td>
							{if $item['status'] eq 'In progress'}
							<button type="button" class="btn btn-danger" id="manula_stop_cron" {if $STOP_BUTTON_STATUS eq 'false'}disabled{/if}>{vtranslate('StopCron', 'OSSMailScanner')}</button>
							{/if}
						</td>
					</tr>
				{/foreach}
			</table>
		
    </div>
</div>
{literal}
<script>
    jQuery(function() {
        jQuery('select[name="page_num"]').on('change', function(){
            reloadLogTable(jQuery(this).val() - 1);
        });

        jQuery('[name="time_to_notify"]').on('blur', function() {
            var value = jQuery(this).val();
            if (!!number_validate(value)) {
                saveWidgetConfig('time', jQuery(this).val(), 'cron');
            } else {
                var params = {
                    text: app.vtranslate('JS_time_error'),
                    type: 'error',
                    animation: 'show'
                };
                                        
                Vtiger_Helper_Js.showPnotify(params);
            }
        });
        jQuery('[name="email_to_notify"]').on('blur', function() {
            var value = jQuery(this).val();
            if (!!email_validate(value)) {
                saveWidgetConfig('email', value, 'cron');
            }
            else {
                var params = {
                    text: app.vtranslate('JS_mail_error'),
                    type: 'error',
                    animation: 'show'
                };
                                        
                Vtiger_Helper_Js.showPnotify(params);
            }
        });
		jQuery('#run_cron').on('click', function(){
			var paramsInfo = {
				text: app.vtranslate('start_cron'),
				type: 'info',
				animation: 'show'
			};
			Vtiger_Helper_Js.showPnotify(paramsInfo);
			jQuery('#run_cron').attr('disabled', true);
			var ajaxParams = {};
			ajaxParams.data = { module: 'OSSMailScanner', action: "cron" },
			ajaxParams.async = true;
			AppConnector.request(ajaxParams).then(
				function(data) {
					var params = {};
					if(data.success && data.result == 'ok'){
						params = {
							text: app.vtranslate('end_cron_ok'),
							type: 'info',
							animation: 'show'
						};
					} else{
						params = {
							title : app.vtranslate('end_cron_error'),
							text: data.result,
							type: 'error',
							animation: 'show'
						};
					}
					Vtiger_Helper_Js.showPnotify(params);
					jQuery('#run_cron').attr('disabled', false);
					reloadLogTable(jQuery('[name="page_num"]').val() - 1);
				},
				function(data, err) {

				}
			);	
			});
			jQuery('#manula_stop_cron').on('click', function(){
                     var ajaxParams = {};
                     ajaxParams.data = { module: 'OSSMailScanner', action: "restartCron" },
                     ajaxParams.async = true;
                    
			AppConnector.request(ajaxParams).then(
				function(data) {
                                    if(data.success){
                                        var params = {
                                                text: data.result.data,
                                                type: 'info',
                                                animation: 'show'
                                        }
                                        
                                        Vtiger_Helper_Js.showPnotify(params);
                                        jQuery('#run_cron').attr('disabled', false);
                                    }
				},
				function(data, err) {

				}
			);
reloadLogTable(jQuery('[name="page_num"]').val() - 1);            
                })    				
    });
    function isEmpty(val){
        if (!!val) {
            return val;
        }
        
        return '';
    }
    function number_validate(value){
      var valid = !/^\s*$/.test(value) && !isNaN(value);
        return valid;
    }
    
    function reloadLogTable(page){
                var limit = 30,
                ajaxParams = { module: 'OSSMailScanner', action: "GetLog", start_number: page * limit};

                AppConnector.request(ajaxParams).then(
                        function(data) {
                            if (data.success) {
                                var tab = jQuery('table.log-list');
								tab.find('tbody tr').remove();
                                for (i = 0; i < data.result.length; i++) {
                                    
                                    var html = '<tr>' 
                                            + '<td>' + isEmpty(data.result[i]['id']) + '</td>' 
                                            + '<td>' + isEmpty(data.result[i]['start_time']) + '</td>' 
                                            + '<td>' + isEmpty(data.result[i]['end_time']) + '</td>' 
                                            + '<td>' + isEmpty(app.vtranslate(data.result[i]['status'])) + '</td>' 
                                            + '<td>' + isEmpty(data.result[i]['user']) + '</td>' 
                                            + '<td>' + isEmpty(data.result[i]['count']) + '</td>' 
                                            + '<td>' + isEmpty(data.result[i]['stop_user']) + '</td>' 
											+ '<td>' + isEmpty(data.result[i]['action']) + '</td>' 
											+ '<td>' + isEmpty(data.result[i]['info']) + '</td>' 
                                            + '<td>';
                                    
                                    if (data.result[i]['status'] == 'In progress') {
                                        html += '<button type="button" class="btn btn-danger" id="manula_stop_cron"'; 
                                        
                                        if(!{/literal}{$STOP_BUTTON_STATUS}{literal}){
                                            html += 'disabled';
                                        }
                                        
                                        html += '>' + app.vtranslate('JS_StopCron') + '</button></td>';
                                    }
                                    
                                    html += '</tr>';
                                    
                                    tab.append(html);
                                }
                            }
                        },
                        function(data, err) {

                        }
                );
    }
    function email_validate(src){
      var regex = /^[a-zA-Z0-9._-]+@([a-zA-Z0-9.-]+\.)+[a-zA-Z0-9.-]{2,63}$/;
      return regex.test(src);
    }
    function saveWidgetConfig(name, value, type) {
        var params = {
            'module': 'OSSMailScanner',
            'action': "SaveWidgetConfig",
            'conf_type': type,
            'name': name,
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
