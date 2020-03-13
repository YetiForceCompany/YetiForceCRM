<?php

header('location: ../../../../index.php?module=MailIntegration&view=MessageCompose&source=outlook&query=' . $_SERVER['QUERY_STRING'], true, 301);
