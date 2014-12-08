/*
+-------------------------------------------------------------------------+
| OpenPGP.js implemented in Roundcube.                                    |
|                                                                         |
| Copyright (C) Niklas Femerstrand <nik@qnrq.se>                          |
|                                                                         |
| This program is free software; you can redistribute it and/or modify    |
| it under the terms of the GNU General Public License version 2          |
| as published by the Free Software Foundation.                           |
|                                                                         |
| This program is distributed in the hope that it will be useful,         |
| but WITHOUT ANY WARRANTY; without even the implied warranty of          |
| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the           |
| GNU General Public License for more details.                            |
|                                                                         |
| You should have received a copy of the GNU General Public License along |
| with this program; if not, write to the Free Software Foundation, Inc., |
| 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.             |
|                                                                         |
+-------------------------------------------------------------------------+
*/

var VERSTR = "20131021";

if(window.rcmail) {
  rcmail.addEventListener("init", function() {
    if(!window.crypto || !window.crypto.getRandomValues) { // OpenPGP.js specific
      rcmail.display_message(rcmail.gettext("no_window_crypto", "rc_openpgpjs"), "error");
    }

    this.passphrase = "";
    rcmail.addEventListener("plugin.pks_search", pks_search_callback);

    if(sessionStorage.length > 0) {
      this.passphrase = sessionStorage[0];
    }

    $("#openpgpjs_key_select").dialog({
      modal: true,
      autoOpen: false,
      title: rcmail.gettext("select_key", "rc_openpgpjs"),
      width: "30%",
      open: function() {
        updateKeySelector();
      },
      close: function() {
        $("#selected_key_passphrase").val("");
        $("#openpgpjs_rememberpass").attr("checked", false);
      },
    });
	
    $("#openpgpjs_key_search").dialog({
      modal: true,
      autoOpen: false,
      title: rcmail.gettext("key_search", "rc_openpgpjs"),
      width: "60%",
      open: function() {
        $("#openpgpjs_search_results").html("");
        $("#openpgpjs_search_input").val("");
      }
    });

    $("#openpgpjs_key_manager").dialog({
      modal: true,
      autoOpen: false,
      title: rcmail.gettext("key_manager", "rc_openpgpjs"),
      width: "90%",
      open: function() {
        updateKeyManager();
      }
    });

    $("#openpgpjs_tabs").tabs();

    // register open key manager command
    rcmail.register_command("open-key-manager", function() {
      $("#openpgpjs_key_manager").dialog("open");
    });
    rcmail.enable_command("open-key-manager", true);

    if(rcmail.env.action === "compose") {
      rcmail.addEventListener("change_identity", function() {
        sessionStorage.clear();
        this.passphrase = "";
      });
      // Disable draft autosave and prompt user when saving plaintext message as draft
      rcmail.env.draft_autosave = 0;
      rcmail.addEventListener("beforesavedraft", function() {
        if($("#openpgpjs_encrypt").is(":checked")) {
          if(!confirm(rcmail.gettext("save_draft_confirm", "rc_openpgpjs"))) {
            return false;
          }
        }

        return true;
      });

      rcmail.env.compose_commands.push("open-key-manager");
      rcmail.addEventListener("beforesend", function(e) {
        if(!beforeSend()) {
          return false;
        }
      });
    } else if(rcmail.env.action === "show" || rcmail.env.action === "preview") {
      processReceived();
    }
  });

  /**
   * Processes received messages
   */
  function processReceived() {
    var msg = parseMsg($("#messagebody div.message-part pre").html());

    // OpenPGP failed parsing the message, no action required.
    if(!msg) {
      return;
    }

    // msg[0].type: 2 == signed only
    // msg[0].type: 3 == encrypted only

    showKeyInfo(msg);

    if(msg[0].type === 2) {
      // rcmail.env.sender contains "Jon Doe <jd@example.com>" or just "jd@example.com";
      // We try to extract the email address (according to RFC 5322) in either case
      var senderAddress = rcmail.env.sender.match(/[A-Za-z0-9.!#$%&'*+\/=?^_`{|}~-]+@[a-zA-Z0-9._-]+\.[a-zA-Z0-9._-]+/);
      if(!senderAddress || !senderAddress.length) {
        // In the case of a bogus sender name/address, throw an error
        displayUserMessage(rcmail.gettext('signature_invalid_sender', 'rc_openpgpjs'), 'notice');
        return false;
      }
      senderAddress = senderAddress[0];
      var pubkeys = getPubkeyForAddress(senderAddress);
      if(!pubkeys.length) {
        displayUserMessage(rcmail.gettext('signature_invalid_no_pubkey', 'rc_openpgpjs') + senderAddress, 'notice');
        return false;
      }
      if(verify(msg, pubkeys)) {
        displayUserMessage(rcmail.gettext('signature_valid', 'rc_openpgpjs') + ': ' + senderAddress, 'confirmation');
        $("#messagebody div.message-part pre").html("<strong>********* *BEGIN SIGNED PART* *********</strong>\n" + escapeHtml(msg[0].text) + "\n<strong>********** *END SIGNED PART* **********</strong>");
        return true;
      } else {
        displayUserMessage(rcmail.gettext('signature_invalid', 'rc_openpgpjs'), 'error');
        return false;
      }
    }

    if(!getPrivkeyCount()) {
      rcmail.display_message(rcmail.gettext("no_key_imported", "rc_openpgpjs"), "error");
      return false;
    }

    if(this.passphrase === "" && getPrivkeyCount() > 0) {
      $("#openpgpjs_key_select").dialog("open");
      return false;
    }

    // json string from set_passphrase, obj.id = privkey id, obj.passphrase = privkey passphrase
    var passobj = JSON.parse(this.passphrase);
    var privkey_armored = getPrivkeyArmored(passobj.id);

    decrypted = decrypt(msg, privkey_armored, passobj.passphrase);
    if(decrypted) {
      $("#messagebody div.message-part pre").html("<strong>********* *BEGIN ENCRYPTED or SIGNED PART* *********</strong>\n" + escapeHtml(decrypted) + "\n<strong>********** *END ENCRYPTED or SIGNED PART* **********</strong>");
    } else {
      alert("This message was not meant for the private key that you are using.");
    }

    return true;
  }

  /**
   * Extracts public key info from parsed OpenPGP message.
   *
   * @param string Parsed OpenPGP message
   */
  function showKeyInfo(msg) {
    var sender = rcmail.env.sender.match(/[a-zA-Z0-9\._%+-]+@[a-zA-Z0-9\._%+-]+\.[a-zA-Z]{2,4}/g)[0];

    try {
      var fingerprint = getFingerprintForSender(sender);
    } catch(e) {
      return false;
    }

    if(typeof(this.getinfo) === "undefined") {
      $(".headers-table").css( "float", "left" );
      $(".headers-table").after("<div id=\"openpgpjs_info\"><table><tbody></tbody></table></div>");

      // Carefully escape anything that is appended to the info table, otherwise
      // anyone clever enough to write arbitrary data to their pubkey has a clear
      // exploitation path.
      $("#openpgpjs_info table tbody").append("<tr><td>Key algo:</td><td>" + typeToStr(msg[0].type) + "</td></tr>");
      $("#openpgpjs_info table tbody").append("<tr><td>Created:</td><td>" + escapeHtml(String(msg[0].messagePacket.creationTime))  + "</td></tr>");
      $("#openpgpjs_info table tbody").append("<tr><td>Fingerprint:</td><td>" + fingerprint + "</td></tr>");
      this.getinfo = false;
    }
  }

  /**
   * Generates an OpenPGP key pair by calling the necessary crypto
   * functions from openpgp.js and shows them to the user
   *
   * @param bits {Integer} Number of bits for the key creation
   * @param algo {Integer} To indicate what type of key to make. RSA is 1
   */
  function generate_keypair(bits, algo) {
    if($("#gen_passphrase").val() === "") {
      $("#generate_key_error").removeClass("hidden");
      $("#generate_key_error p").html(rcmail.gettext("enter_pass", "rc_openpgpjs"));
      return false;
    } else if($("#gen_passphrase").val() !== $("#gen_passphrase_verify").val()) {
      $("#generate_key_error").removeClass("hidden");
      $("#generate_key_error p").html(rcmail.gettext("pass_mismatch", "rc_openpgpjs"));
      return false;
    }

    // TODO Currently only RSA is supported, fix this when OpenPGP.js implements ElGamal & DSA
    var ident = $("#gen_ident option:selected").text();
    var keys = generateKeys(bits, 1, ident, $("#gen_passphrase").val());
    $("#generated_keys").html("<pre id=\"generated_private\">" + keys["private"] + "</pre><pre id=\"generated_public\">" + keys["public"]  +  "</pre>");
    $("#generate_key_error").addClass("hidden");
    $("#import_button").removeClass("hidden");

    return true;
  }

  /**
   * Import generated key pair.
   */
  function importGenerated() {
    $("#import_button").addClass("hidden");
    importPubKey($("#generated_public").html());

    if(importPrivKey($("#generated_private").html(), $("#gen_passphrase").val())) {
      alert(rcmail.gettext("import_gen", "rc_openpgpjs"));
    }

    $("#gen_passphrase").val("");
    $("#gen_passphrase_verify").val("");
  }

  /**
   * Set passphrase.
   *
   * @param i {Integer} Used as openpgp.keyring[private|public]Keys[i]
   * @param p {String}  The passphrase
   */
  // TODO: move passphrase checks from old decrypt() to here
  function set_passphrase(i, p) {
    if(i === "-1") {
      $("#key_select_error").removeClass("hidden");
      $("#key_select_error p").html(rcmail.gettext("select_key", "rc_openpgpjs"));
      return false;
    }

    if(!decryptSecretMPIs(i, p)) {
      $("#key_select_error").removeClass("hidden");
      $("#key_select_error p").html(rcmail.gettext("incorrect_pass", "rc_openpgpjs"));
      return false;
    }

    this.passphrase = JSON.stringify({ "id" : i, "passphrase" : p } );
    processReceived();

    if($("#openpgpjs_rememberpass").is(":checked")) {
      sessionStorage.setItem(i, this.passphrase);
    }

    $("#key_select_error").addClass("hidden");
    $("#openpgpjs_key_select").dialog("close");

    // This is required when sending emails and private keys are required for
    // sending an email (when signing a message). These lines makes the client
    // jump right back into beforeSend() allowing key sign and message send to
    // be made as soon as the passphrase is correct and available.
    if(typeof(this.sendmail) !== "undefined") {
      rcmail.command("send", this);
    }
  }

  function fetchRecipientPubkeys() {
    var pubkeys = new Array();

    var c = 0;
    var recipients = [];
    var matches = "";
    var fields = ["_to", "_cc", "_bcc"];
    var re = /[a-zA-Z0-9\._%+-]+@[a-zA-Z0-9\._%+-]+\.[a-zA-Z]{2,4}/g;

    for(field in fields) {
      matches = $("#" + fields[field]).val().match(re);

      for(key in matches) {
        recipients[c] = matches[key];
        c++;
      }
    }

    for (var i = 0; i < recipients.length; i++) {
      var recipient = recipients[i].replace(/(.+?<)/, "").replace(/>/, "");
      var pubkey = getPubkeyForAddress(recipient);
      if(typeof(pubkey[0]) != "undefined") {
        pubkeys.push(pubkey[0].obj);
      } else {
        // Querying PKS for recipient pubkey
       if(confirm("Couldn't find a public key for " + recipient + ". If you already have it you can import it into the key manager. Would you like to query the key server for the missing key?")) {
          rcmail.http_post("plugin.pks_search", "search=" + recipient + "&op=index");
          $("#openpgpjs_search_input").attr("disabled", "disabled");
          $("#openpgpjs_search_submit").attr("disabled", "disabled");
          $("#openpgpjs_key_search").dialog("open");
        }
        return false;
      }
    }

    return pubkeys;
  }

  /**
   * Get the user's public key
   */
  function fetchSendersPubkey(armored) {
    
    if (typeof(armored) == "undefined") {
      armored = false;
    }

    var re = /[a-zA-Z0-9\._%+-]+@[a-zA-Z0-9\._%+-]+\.[a-zA-Z]{2,4}/g;
    var address = $("#_from>option:selected").html().match(re);
    
    if (address.length > 0) {
      var pubkey = getPubkeyForAddress(address[0]);

      if(typeof(pubkey[0]) != "undefined") {
        if (armored)
          return pubkey[0].armored;
        else
          return pubkey[0].obj;
      }  
    }
    return false;
  }

  /**
   * Processes messages before sending
   */
  function beforeSend() {
    if(!$("#openpgpjs_encrypt").is(":checked") &&
       !$("#openpgpjs_sign").is(":checked")) {
      if(confirm(rcmail.gettext("continue_unencrypted", "rc_openpgpjs"))) {
        // remove the public key attachment since we don't sign nor encrypt the message
        removePublicKeyAttachment();
        return true;
      } else {
        return false;
      }
    }

    if(typeof(this.finished_treating) !== "undefined") {
      return true;
    }

    // send the user's public key to the server so it can be sent as attachment
    var pubkey_sender = fetchSendersPubkey(true);
    if (pubkey_sender) {
        var lock = rcmail.set_busy(true, 'loading');
        rcmail.http_post('plugin.pubkey_save', { _pubkey: pubkey_sender }, lock);
    }
    // end send user's public key to the server

    // Encrypt and sign
    if($("#openpgpjs_encrypt").is(":checked") && $("#openpgpjs_sign").is(":checked")) {
      // get the private key
      if(this.passphrase === "" && getPrivkeyCount() > 0) {
        this.sendmail = true; // Global var to notify set_passphrase
        $("#openpgpjs_key_select").dialog("open");
        return false;
      }

      if(!getPrivkeyCount()) {
        alert(rcmail.gettext("no_keys", "rc_openpgpjs"));
        return false;
      }

      var passobj = JSON.parse(this.passphrase);
      var privkey = getPrivkeyObj(passobj.id);

      if(!privkey[0].decryptSecretMPIs(passobj.passphrase)) {
        alert(rcmail.gettext("incorrect_pass", "rc_openpgpjs"));
      }
      // we now have the private key (for signing)
      
      // get the public key
      var pubkeys = fetchRecipientPubkeys();
      if(pubkeys.length === 0) {
        return false;
      }
      // done public keys

      // add the user's public key
      var pubkey_sender = fetchSendersPubkey();
      if (pubkey_sender) {
        pubkeys.push(pubkey_sender);
      } else {
        if (!confirm("Couldn't find your public key. You will not be able to decrypt this message. Continue?")) {
          return false;
        }
      }
      // end add user's public key

      var text = $("textarea#composebody").val();
      var encrypted = encrypt(pubkeys, text, 1, privkey, passobj.passphrase);
		
      if(encrypted) {
        $("textarea#composebody").val(encrypted);
        this.finished_treating = 1;
        return true;
      }
    }
	
    // Encrypt only
    if($("#openpgpjs_encrypt").is(":checked") &&
       !$("#openpgpjs_sign").is(":checked")) {
      // Fetch recipient pubkeys
      var pubkeys = fetchRecipientPubkeys();
      if(pubkeys.length === 0) {
        return false;
      }
      
      // add the user's public key
      var pubkey_sender = fetchSendersPubkey();
      if (pubkey_sender) {
        pubkeys.push(pubkey_sender);
      } else {
        if (!confirm("Couldn't find your public key. You will not be able to decrypt this message. Continue?")) {
          return false;
        }
      }
      // end add user's public key

      var text = $("textarea#composebody").val();
      var encrypted = encrypt(pubkeys, text);
      if(encrypted) {
        $("textarea#composebody").val(encrypted);
        this.finished_treating = 1;
        return true;
      }
    }

    // Sign only
    if($("#openpgpjs_sign").is(":checked") &&
       !$("#openpgpjs_encrypt").is(":checked")) {

      if(this.passphrase === "" && getPrivkeyCount() > 0) {
        this.sendmail = true; // Global var to notify set_passphrase
        $("#openpgpjs_key_select").dialog("open");
        return false;
      }

      if(!getPrivkeyCount()) {
        alert(rcmail.gettext("no_keys", "rc_openpgpjs"));
        return false;
      }

      var passobj = JSON.parse(this.passphrase);
      var privkey = getPrivkeyObj(passobj.id);

      if(!privkey[0].decryptSecretMPIs(passobj.passphrase)) {
        alert(rcmail.gettext("incorrect_pass", "rc_openpgpjs"));
      }

      var privkey_armored = getPrivkeyArmored(passobj.id);
      signed = sign($("textarea#composebody").val(), privkey_armored, passobj.passphrase);

      if(signed) {
        $("textarea#composebody").val(signed);
        return true;
      }

      return false;
    }

    return false;
  }

  /**
   * Removes the public key attachment
   * Used if the user doesn't sign nor encrypt the message
   */
  function removePublicKeyAttachment() {
    $("#attachment-list").each(function() {
      $(this).find('li').each(function() {
        if ($(this).text().indexOf('pubkey.asc') >= 0) {
          rcmail.command('remove-attachment', $(this).attr('id'));
          return false;
        }
      });
    });
  }

  function importFromSKS(id) {
    rcmail.http_post("plugin.pks_search", "search=" + id + "&op=get");
    return;
  }

  /**
   * Imports armored public key into the key manager
   *
   * @param key {String} The armored public key
   * @return {Bool} Import successful
   */
  function importPubKey(key) {
    try {
      importPubkey(key);
      updateKeyManager();
      $("#importPubkeyField").val("");
      $("#import_pub_error").addClass("hidden");
    } catch(e) {
      $("#import_pub_error").removeClass("hidden");
      $("#import_pub_error p").html(rcmail.gettext("import_failed", "rc_openpgpjs"));
      alert(rcmail.gettext("import_fail", "rc_openpgpjs"));
	alert(e);
      return false;
    }

    return true;
  }

  /**
   * op: (get|index|vindex) string operation to perform
   * search: string phrase to pass to HKP
   *
   * To retrieve all matching keys: pubkey_search("foo@bar", "index")
   * To retrieve armored key of specific id: pubkey_search("0xF00", "get")
   *
   * If op is get then search should be either 32-bit or 64-bit. See
   * http://tools.ietf.org/html/draft-shaw-openpgp-hkp-00#section-3.1.1.1
   * for more details.
   *
   */
  // TODO: Version 3 fingerprint search
  function pubkey_search(search, op) {
    if(search.length === 0) {
      return false;
    }

    rcmail.http_post("plugin.pks_search", "search=" + search + "&op=" + op);
    return true;
  }

  function pks_search_callback(response) {
    $("#openpgpjs_search_input").removeAttr("disabled");
    $("#openpgpjs_search_submit").removeAttr("disabled");

    if(response.message === "ERR: Missing param") {
      console.log("Missing param");
      return false;
    }

    if(response.message === "ERR: Invalid operation") {
      console.log("Invalid operation");
      return false;
    }

    if(response.message === "ERR: No keys found") {
        alert(rcmail.gettext("search_no_keys", "rc_openpgpjs"));
        return false;
    }

    if(response.op === "index") {
      try {
        result = JSON.parse(response.message);
      } catch(e) {
        alert(rcmail.gettext("search_no_keys", "rc_openpgpjs"));
        return false;
      }

      $("#openpgpjs_search_results").html("");
      for(var i = 0; i < result.length; i++) {
        $("#openpgpjs_search_results").append("<tr class='" + (i%2 !== 0 ? " odd" : "") + "'><td><a href='#' onclick='importFromSKS(\"" + result[i][0] + "\");'>Import</a></td><td>" + result[i][0] + "</td>" + "<td>" + result[i][1] + "</td></tr>");
      }
    } else if(response.op === "get") {
      k = JSON.parse(response.message);
      $("#importPubkeyField").val(k[0]);
      if(importPubKey($("#importPubkeyField").val())) {
        alert(rcmail.gettext("pubkey_import_success", "rc_openpgpjs"));
      }
    }
  }

  /**
   * Imports armored private key into the key manager
   *
   * @param key        {String} The armored private key
   * @param passphrase {String} The corresponding passphrase
   * @return {Bool} Import successful
   */
  function importPrivKey(key, passphrase) {
    if(passphrase === "") {
      $("#import_priv_error").removeClass("hidden");
      $("#import_priv_error p").html(rcmail.gettext("enter_pass", "rc_openpgpjs"));
      return false;
    }

    try {
      privkey_obj = parsePrivkey(key);
    } catch(e) {
      $("#import_priv_error").removeClass("hidden");
      $("#import_priv_error p").html(rcmail.gettext("import_failed", "rc_openpgpjs"));
      return false;
    }

    if(!privkey_obj.decryptSecretMPIs(passphrase)) {
      $("#import_priv_error").removeClass("hidden");
      $("#import_priv_error p").html(rcmail.gettext("incorrect_pass", "rc_openpgpjs"));
      return false;
    }

	importPrivkey(key, passphrase);
    updateKeyManager();
    $("#importPrivkeyField").val("");
    $("#passphrase").val("");
    $("#import_priv_error").addClass("hidden");

    return true;
  }

  /**
   * Select a private key.
   *
   * @param i {Integer} Used as openpgp.keyring[private|public]Keys[i]
   */
  function select_key(i) {
    fingerprint = getFingerprint(i, true, false);
    $("#openpgpjs_selected").html("<strong>" + rcmail.gettext("selected", "rc_openpgpjs") + ":</strong> " + $(".clickme#" + fingerprint).html());
    $("#openpgpjs_selected_id").val(i);
    $("#passphrase").val("");
  }

  /**
   * Update key selector dialog.
   */
  function updateKeySelector() {
    // Fills key_select key list
    $("#openpgpjs_key_select_list").html("<input type=\"hidden\" id=\"openpgpjs_selected_id\" value=\"-1\" />");

    // Only one key in keyring, nothing to select from
    if(getPrivkeyCount() === 1) {
      $("#openpgpjs_selected_id").val(0);
    } else {
      // Selected set as $("#openpgpjs_selected_id").val(), then get that value from set_passphrase
      for (var i = 0; i < getPrivkeyCount(); i++) {
        for (var j = 0; j < getKeyUserids(i, true).length; j++) {
          fingerprint = getFingerprint(i, true, false);
          person = escapeHtml(getPerson(i, j, true));
          $("#openpgpjs_key_select_list").append("<div class=\"clickme\" id=\"" + fingerprint +"\"onclick=\"select_key(" + i + ");\">" + fingerprint + " " + person + "</div>");
        }
      }

      $("#openpgpjs_key_select_list").append("<div id=\"openpgpjs_selected\"><strong>" + rcmail.gettext("selected", "rc_openpgpjs") + ":</strong> <i>" + rcmail.gettext("none", "rc_openpgpjs") + "</i></div>");
    }

    return true;
  }

  /**
   * Updates key manager public keys table, private keys table
   * and identy selector.
   */
  function updateKeyManager() {
    // fill key manager public key table
    $("#openpgpjs_pubkeys tbody").empty();
    for (var i = 0; i < getPubkeyCount(); i++) {
      var key_id = getKeyID(i);
      var fingerprint = getFingerprint(i);
      var person = escapeHtml(getPerson(i, 0));
      var length_alg = getAlgorithmString(i);
      var status = (verifyBasicSignatures(i) ? rcmail.gettext("valid", "rc_openpgpjs") : rcmail.gettext("invalid", "rc_openpgpjs"));
      var del = "<a href='#' onclick='if(confirm(\"" + rcmail.gettext('delete_pub', 'rc_openpgpjs') + "\")) { removeKey(" + i + "); updateKeyManager(); }'>" + rcmail.gettext('delete', 'rc_openpgpjs') + "</a>";
      var exp = "<a href=\"data:asc," + encodeURIComponent(exportArmored(i)) + "\" download=\"pubkey_" + getKeyID(i) + ".asc\">Export</a> ";

      var result = "<tr>" +
        "<td>" + key_id      + "</td>" +
        "<td>" + fingerprint + "</td>" +
        "<td>" + person      + "</td>" +
        "<td>" + length_alg  + "</td>" +
        "<td>" + status      + "</td>" +
        "<td>" + exp + del   + "</td>" +
        "</tr>";
      $("#openpgpjs_pubkeys tbody").append(result);
    }

    // fill key manager private key table
    $("#openpgpjs_privkeys tbody").empty();
    for (var i = 0; i < getPrivkeyCount(); i++) {
      for (var j = 0; j < getKeyUserids(i, true).length; j++) {
        var key_id = getKeyID(i, true);
        var fingerprint = getFingerprint(i, true);
        var person = escapeHtml(getPerson(i, j, true));
        var length_alg = getAlgorithmString(i, true);
        var del = "<a href='#' onclick='if(confirm(\"" + rcmail.gettext('delete_priv', 'rc_openpgpjs') + "\")) { removeKey(" + i + ", true); updateKeyManager(); }'>" + rcmail.gettext('delete', 'rc_openpgpjs') + "</a>";
        var exp = "<a href=\"data:asc," + encodeURIComponent(exportArmored(i, true)) + "\" download=\"privkey_" + getKeyID(i, true) + ".asc\">Export</a> ";

        var result = "<tr>" +
          "<td>" + key_id      + "</td>" +
          "<td>" + fingerprint + "</td>" +
          "<td>" + person      + "</td>" +
          "<td>" + length_alg  + "</td>" +
          "<td>" + exp + del   + "</td>" +
          "</tr>";

        $("#openpgpjs_privkeys tbody").append(result);
      }
    }

    // fill key manager generation identity selector
    $("#gen_ident").html("");
    identities = JSON.parse($("#openpgpjs_identities").html());
    for (var i = 0; i < identities.length; i++) {
      $("#gen_ident").append("<option value='" + i + "'>" + escapeHtml(identities[i].name + " <" + identities[i].email + ">") + "</option>");
    }
  }

  /**
   * Converts an algorithm id (1/2/3/16/17) to the
   * corresponding algorithm type
   *
   * @param id {Integer} Algorithm id
   * @return {String} Algorithm type
   */
  function typeToStr(id) {
    var r = ""

    switch(id) {
      case 1:
        r = "RSA(S/E)";
        break;
      case 2:
        r = "RSA(E)";
        break;
      case 3:
        r = "RSA(S)";
        break;
      case 16:
        r = "Elg";
        break;
      case 17:
        r = "DSA";
        break;
      default:
        r = "UNKNOWN";
        break;
    }

    return(r);
  }

  /**
   * Escape some unsafe characters into their html entities.
   *
   * @param unsafe {String} Unsafe string to escape
   */
  function escapeHtml(unsafe) {
        return unsafe.replace(/&/g, "&amp;")
                     .replace(/</g, "&lt;")
                     .replace(/>/g, "&gt;")
                     .replace(/"/g, "&quot;")
                     .replace(/'/g, "&#039;");
  }

  function showMessages(msg) { console.log(msg); }

  /**
   * Display a custom message above the email body, analogous to
   * Roundcubes privacy warning message.
   *
   * @param msg  {String} Message to display
   * @param type {String} One of 'confirmation', 'notice', 'error'
   */
  function displayUserMessage(msg, type) {
    // Insert a div into the message-objects <div> provided by Roundcube
    $('<div>').text(msg).addClass(type).addClass('messagepadding').appendTo($('#message-objects'));
  }
}
