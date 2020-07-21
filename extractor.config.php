<?php

require_once '.env';

$inbox = imap_open($ENV['HOSTNAME'],$ENV['USERNAME'],$ENV['PASSWORD']) or die('Cannot connect to Gmail: ' . imap_last_error());

$emails = imap_search($inbox,'FROM "@gmail.com"');
$emails_s = imap_search($inbox,'SUBJECT "JDMA"');
$pathToSave = "local_server/client1/";
$trashBox = '[Gmail]/Corbeille';

?>
