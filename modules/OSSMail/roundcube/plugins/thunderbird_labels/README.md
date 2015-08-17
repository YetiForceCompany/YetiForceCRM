## Thunderbird Labels Plugin for Roundcube Webmail

### Features

* Displays the message rows using the same colors as Thunderbird does
* Label of a message can be changed/set exactly like in Thunderbird
* Keyboard shortcuts on keys 0-5 work like in Thunderbird
* Integrates into contextmenu plugin when available
* Works for skins classic and larry
* currently available translations:
  * English
  * French (Français)
  * German (Deutsch)
  * Polish (Polski)
  * Russian (Русский)
  * Hungarian (Magyar)
  * Czech (Česky)
  * Bulgarian (български език)
  * Catalan (català)
  * Latvian (latviešu)
  * Italian (italiano)
  * Spanish (español)
  * Ukranian (українська)
* screenshot: http://rcmail-thunderbird-labels.googlecode.com/files/screenshot.png

### TODO
- allow users to have an arbitrary number of labels

### INSTALL
1. unpack to plugins directory
2. add `, 'thunderbird_labels'` to `$rcmail_config['plugins']` in main.inc.php

### CONFIGURE

See config.inc.php

- `tb_label_enable = true/false` (can be changed by user in prefs UI)
- `tb_label_modify_labels = true/false`
- `tb_label_enable_contextmenu = true/false`
- `tb_label_enable_shortcuts = true/false` (can be changed by user in prefs UI)
- `tb_label_style = 'bullets'` or `'thunderbird'`

### Author
Michael Kefeder
https://github.com/mike-kfed/rcmail-thunderbird-labels

### History
This plugin is based on a patch I found for roundcube 0.3 a long time ago.

Since roundcube is now able to handle the labels without modification of its source I decided to create a plugin.

There exists a "Tags plugin for RoundCube" http://sourceforge.net/projects/tagspluginrc/ which does something similar, my plugin emulates thunderbirds behaviour better I think (coloring the message rows for example)

