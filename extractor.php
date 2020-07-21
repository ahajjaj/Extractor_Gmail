<?php

set_time_limit(3000);

include 'extractor.config.php';


if($emails) {

    rsort($emails);
    include 'script_expediteur.php';

}elseif ($emails_s){

    rsort($emails_s);
    include 'script_subject.php';
}

imap_close($inbox);

echo "Done";

?>