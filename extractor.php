<?php

set_time_limit(3000);

require_once '.env';
include 'extractor.config.php';


$inbox = imap_open($ENV['HOSTNAME'],$ENV['USERNAME'],$ENV['PASSWORD']) or die('Cannot connect to Gmail: ' . imap_last_error());


$emails = imap_search($inbox,'SUBJECT "jdma"');



if($emails) {

    rsort($emails);

    foreach($emails as $email_number)
    {

        $overview = imap_fetch_overview($inbox,$email_number,0);

        $message = imap_fetchbody($inbox,$email_number,2);

        $structure = imap_fetchstructure($inbox, $email_number);

        $attachments = array();

        if(isset($structure->parts) && count($structure->parts))
        {
            for($i = 0; $i < count($structure->parts); $i++)
            {
                $attachments[$i] = array(
                    'is_attachment' => false,
                    'filename' => '',
                    'name' => '',
                    'attachment' => ''
                );

                if($structure->parts[$i]->ifdparameters)
                {
                    foreach($structure->parts[$i]->dparameters as $object)
                    {
                        if(strtolower($object->attribute) == 'filename')
                        {
                            $attachments[$i]['is_attachment'] = true;
                            $attachments[$i]['filename'] = $object->value;
                        }
                    }
                }

                if($structure->parts[$i]->ifparameters)
                {
                    foreach($structure->parts[$i]->parameters as $object)
                    {
                        if(strtolower($object->attribute) == 'name')
                        {
                            $attachments[$i]['is_attachment'] = true;
                            $attachments[$i]['name'] = $object->value;
                        }
                    }
                }

                if($attachments[$i]['is_attachment'])
                {
                    $attachments[$i]['attachment'] = imap_fetchbody($inbox, $email_number, $i+1);

                    /* 4 = QUOTED-PRINTABLE encoding */
                    if($structure->parts[$i]->encoding == 3)
                    {
                        $attachments[$i]['attachment'] = base64_decode($attachments[$i]['attachment']);
                    }
                    /* 3 = BASE64 encoding */
                    elseif($structure->parts[$i]->encoding == 4)
                    {
                        $attachments[$i]['attachment'] = quoted_printable_decode($attachments[$i]['attachment']);
                    }
                }
            }
        }

        foreach($attachments as $attachment)
        {
            if($attachment['is_attachment'] == 1)
            {
                $filename = $attachment['name'];
                if(empty($filename)) $filename = $attachment['filename'];

                if(empty($filename)) $filename = time() . ".dat";


                $fp = fopen($pathToSave . $email_number . "-" . $filename, "w+");
                echo 'Destination: ' . $pathToSave . $email_number . "-" . $filename . "\n";
                fwrite($fp, $attachment['attachment']);
                fclose($fp);
            }
        }

        break;
    }
    imap_mail_move($inbox, $i, '[Gmail]/Corbeille');

}

imap_close($inbox);

echo "Done";

?>