{*<!--
/*********************************************************************************
  ** The contents of this file are subject to the vtiger CRM Public License Version 1.0
   * ("License"); You may not use this file except in compliance with the License
   * The Original Code is:  vtiger CRM Open Source
   * The Initial Developer of the Original Code is vtiger.
   * Portions created by vtiger are Copyright (C) vtiger.
   * All Rights Reserved.
  *
 ********************************************************************************/
-->*}
{strip}
<div name='calendarViewTypes'>
	<div id="calendarview-feeds" style="margin-left:10px;">
		<!--Adding or Editing calendar views in My Calendar-->
		<div class="modal addViewsToCalendar hide">
			<div class="modal-header contentsBackground">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
				<h3>{vtranslate('LBL_ADD_CALENDAR_VIEW', $MODULE)}</h3>
			</div>
			<div class="modal-body">
				<form class="form-horizontal">
					<input type="hidden" class="selectedUser" value="" />
					<input type="hidden" class="selectedUserColor" value="" />
					<input type="hidden" class="selectedViewModule" value="" />
					<input type="hidden" class="userCalendarMode" value="" />
					<div class="control-group addCalendarViewsList">
						<label class="control-label">{vtranslate('LBL_SELECT_ACTIVITY_TYPE', $MODULE)}</label>
						<div class="controls">
							<select class="select2" name="usersCalendarList" style="min-width: 250px;">
								{foreach item=VIEWINFO from=$VIEWTYPES['invisible']}
									<option value="{$VIEWINFO['fieldname']}" data-viewmodule="{$VIEWINFO['module']}">{vtranslate($VIEWINFO['fieldlabel'], $VIEWINFO['module'])}</option>
								{/foreach}
							</select>
						</div>
					</div>
					<div class="control-group editCalendarViewsList">
						<label class="control-label">{vtranslate('LBL_EDITING_CALENDAR_VIEW', $MODULE)}</label>
						<div class="controls">
							<select class="select2" name="editingUsersList" style="min-width: 250px;">
								{foreach item=VIEWINFO from=$VIEWTYPES['visible']}
									<option value="{$VIEWINFO['fieldname']}" data-viewmodule="{$VIEWINFO['module']}">{vtranslate($VIEWINFO['fieldlabel'], $VIEWINFO['module'])}</option>
								{/foreach}
							</select>
						</div>
					</div>
					<div class="control-group">
						<label class="control-label">{vtranslate('LBL_SELECT_CALENDAR_COLOR', $MODULE)}</label>
						<div class="controls">
							<p class="calendarColorPicker"></p>
						</div>
					</div>
				</form>
			</div>
			{include file='ModalFooter.tpl'|@vtemplate_path:$MODULE}
		</div>
		<!--Adding or Editing calendar views in Shared Calendar-->
		
		<div class="labelModal hide">
			<label class="checkbox addedCalendars" style="text-shadow: none">
				<input type="checkbox" />
				&nbsp;<span class="label" style="text-shadow: none"></span>
				&nbsp;<i class="icon-pencil editCalendarColor cursorPointer actionImage" title="{vtranslate('LBL_EDIT_COLOR',$MODULE)}"></i>
				&nbsp;<i class="icon-trash cursorPointer actionImage deleteCalendarView" title="{vtranslate('LBL_DELETE_CALENDAR',$MODULE)}"></i>
			</label>
		</div>
		
		{foreach item=VIEWINFO from=$VIEWTYPES['visible']}
			<label class="checkbox addedCalendars">
				<input type="checkbox" data-calendar-sourcekey="{$VIEWINFO['fieldname']}" data-calendar-feed="{$VIEWINFO['module']}" data-calendar-feed-color="{$VIEWINFO['color']}" 
					   data-calendar-fieldname="{$VIEWINFO['fieldname']}" data-calendar-fieldlabel="{vtranslate($VIEWINFO['fieldlabel'], $VIEWINFO['module'])}"> 
				&nbsp;<span class="label" style="text-shadow: none; background-color: {$VIEWINFO['color']};">{vtranslate($VIEWINFO['fieldlabel'], $VIEWINFO['module'])}</span>
				&nbsp;<i class="icon-pencil editCalendarColor cursorPointer actionImage" title="{vtranslate('LBL_EDIT_COLOR',$MODULE)}"></i>
				&nbsp;<i class="icon-trash cursorPointer actionImage deleteCalendarView" title="{vtranslate('LBL_DELETE_CALENDAR',$MODULE)}"></i>
			</label>
		{/foreach}
		{assign var=INVISIBLE_CALENDAR_VIEWS_EXISTS value='false'}
		{if $VIEWTYPES['invisible']}
			{assign var=INVISIBLE_CALENDAR_VIEWS_EXISTS value='true'}
		{/if}
		<input type="hidden" class="invisibleCalendarViews" value="{$INVISIBLE_CALENDAR_VIEWS_EXISTS}" />
	</div>
</div>
{/strip}

<script type="text/javascript">
jQuery(document).ready(function() {
	Calendar_CalendarView_Js.initiateCalendarFeeds();
});
</script>