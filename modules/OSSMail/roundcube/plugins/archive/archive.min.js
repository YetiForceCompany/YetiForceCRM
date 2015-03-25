/**
 * Archive plugin script
 * @version 2.3
 *
 * @licstart  The following is the entire license notice for the
 * JavaScript code in this file.
 *
 * Copyright (c) 2012-2014, The Roundcube Dev Team
 *
 * The JavaScript code in this page is free software: you can redistribute it
 * and/or modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation, either version 3 of
 * the License, or (at your option) any later version.
 *
 * @licend  The above is the entire license notice
 * for the JavaScript code in this file.
 */
function rcmail_archive(c){if(rcmail.env.uid||rcmail.message_list&&rcmail.message_list.get_selection().length)rcmail_is_archive()||(rcmail.env.archive_type?rcmail.http_post("plugin.move2archive",rcmail.selection_post_data()):rcmail.command("move",rcmail.env.archive_folder))}function rcmail_is_archive(){if(rcmail.env.mailbox==rcmail.env.archive_folder||rcmail.env.mailbox.startsWith(rcmail.env.archive_folder+rcmail.env.delimiter))return!0}
window.rcmail&&rcmail.addEventListener("init",function(c){rcmail.register_command("plugin.archive",rcmail_archive,rcmail.env.uid&&!rcmail_is_archive());rcmail.message_list&&rcmail.message_list.addEventListener("select",function(a){rcmail.enable_command("plugin.archive",0<a.get_selection().length&&!rcmail_is_archive())});var b;rcmail.env.archive_folder&&(b=rcmail.get_folder_li(rcmail.env.archive_folder,"",!0))&&$(b).addClass("archive");rcmail.addEventListener("plugin.move2archive_response",function(a){a.update&&
rcmail.command("list")})});
