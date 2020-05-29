<?
namespace swa\digitalphotoframe\lib;

require_once ("config.php");


function isIgnoreFileFormat($pFilename, $pFormats) {
    if(strlen($pFilename) == 0) {
        return false;
    }
    
    $retVal = false;
    foreach($pFormats as $currentFormat) {
        if(strpos($pFilename, ".".$currentFormat) > 0) {
            $retVal = true;
        }
    }
    return $retVal;
}

header('Content-Type: application/json');

/* return message*/
$retVal = array();
$retVal['debug'] = array();
$retVal['messages'] = array();

/* try to connect */
$inbox = imap_open(Config::read('mail_host'), Config::read('mail_user'), Config::read('mail_password')) or die('Cannot connect to Email: ' . imap_last_error());

/* grab emails */
$emails = imap_search($inbox, '');

/* if emails are returned, cycle through each... */
if ($emails) {
    
    /* begin output var */
    $output = '';
    
    /* put the newest emails on top */
    rsort($emails);
    
    foreach ($emails as $email_number) {
        
        /* get information specific to this email */
        $overview = imap_fetch_overview($inbox, $email_number, 0);
        //$message = imap_fetchbody($inbox, $email_number, 2);
        $structure = imap_fetchstructure($inbox, $email_number);
        
        $attachments = array();
        if (isset($structure->parts) && count($structure->parts)) {
            for ($i = 0; $i < count($structure->parts); $i ++) {
                $attachments[$i] = array(
                    'is_attachment' => false,
                    'filename' => '',
                    'name' => '',
                    'attachment' => ''
                );
                
                if ($structure->parts[$i]->ifdparameters) {
                    foreach ($structure->parts[$i]->dparameters as $object) {
                        if (strtolower($object->attribute) == 'filename') {
                            $attachments[$i]['is_attachment'] = true;
                            $attachments[$i]['filename'] = $object->value;
                        }
                    }
                }
                
                if ($structure->parts[$i]->ifparameters) {
                    foreach ($structure->parts[$i]->parameters as $object) {
                        if (strtolower($object->attribute) == 'name') {
                            $attachments[$i]['is_attachment'] = true;
                            $attachments[$i]['name'] = $object->value;
                        }
                    }
                }
                
                if ($attachments[$i]['is_attachment']) {
                    $attachments[$i]['attachment'] = imap_fetchbody($inbox, $email_number, $i + 1);
                    if ($structure->parts[$i]->encoding == 3) { // 3 = BASE64
                        $attachments[$i]['attachment'] = base64_decode($attachments[$i]['attachment']);
                    } elseif ($structure->parts[$i]->encoding == 4) { // 4 = QUOTED-PRINTABLE
                        $attachments[$i]['attachment'] = quoted_printable_decode($attachments[$i]['attachment']);
                    }
                }
            } // for($i = 0; $i < count($structure->parts); $i++)
        } // if(isset($structure->parts) && count($structure->parts))
        
        if (count($attachments) != 0) {
            foreach ($attachments as $at) {
                if (isIgnoreFileFormat($at['name'], Config::read('mail_image_ignore_fileformats'))) {
                    $retVal['debug'][] = "Ignore Video Attachment ".$at['filename'];
                } elseif ($at['is_attachment'] == 1) {
					
					$imagePath = Config::read("mail_image_directory_current").$at['filename'];
					$iFileCount=0;
					while(file_exists($imagePath)) {
						$imagePath =  Config::read("mail_image_directory_current") . ++$iFileCount .  $at['filename'];
					}
                    file_put_contents($imagePath, $at['attachment']);
                    
                    // Fix Image Rotation Start
                    $exif = exif_read_data($imagePath);
                    if(array_key_exists('Orientation', $exif)) {
                        $ort = $exif['Orientation']; /*STORES ORIENTATION FROM IMAGE */
                        $ort1 = $ort;
                        $exif = exif_read_data($imagePath, 0, true);
                        if (!empty($ort1)) {
                            $image = imagecreatefromjpeg($imagePath);
                            $ort = $ort1;
                            switch ($ort) {
                                case 3:
                                    $image = imagerotate($image, 180, 0);
                                    break;
                            
                                case 6:
                                    $image = imagerotate($image, -90, 0);
                                    break;
                            
                                case 8:
                                    $image = imagerotate($image, 90, 0);
                                    break;
                            }
                        }
                        imagejpeg($image,$imagePath, 90); /*IF FOUND ORIENTATION THEN ROTATE IMAGE IN PERFECT DIMENSION*/
                    } else {
                        $retVal['debug'][] = "Could not change picture orientation ".$at['filename'];
                    }
                    // Fix Image Rotation End
					
                    // Uploaded File
                    $retVal['items'][] = $imagePath;
                                        
                    // Move old files Start
                    $listFolderContents = FolderUtil::listFiles(Config::read("mail_image_directory_current"));
                    
                    //Fetch Items
                    $cacheByTime = array();
                    foreach($listFolderContents as $cu) {
                        $cacheByTime[filemtime($cu)] = $cu;
                    }
                    
                    ksort($cacheByTime);
                    $cacheByTime = array_reverse($cacheByTime, TRUE);
					
					if(count($cacheByTime) > Config::read('image_cache')) {
						for($i = Config::read('image_cache'); $i < count($cacheByTime); $i++) {
							$item = array_values($cacheByTime)[$i];
							if(!is_dir(Config::read('mail_image_directory_archive'))) {
								$retVal['cleanup'][] = "Create Archive Directory". Config::read('mail_image_directory_archive');
								
								mkdir(Config::read('mail_image_directory_archive'));
							}
							$destination = Config::read('mail_image_directory_archive').basename($item);
							
							try {
								rename($item, $destination);
								$retVal['cleanup'][] = $item;
							} catch(\Exception $e) {
								try {
									//$deletedFolder = $api->delete($destination);
									$retVal['cleanup'][] = $e->getMessage().". Deleted item manually";
								} catch(\Exception $e) {
									$retVal['cleanup'][] = "Failed to Deleted item manually ".$item;
								}
							}
						}
					}
                    
                    // Move old files End
                }
            }
        }
        
        // Write Subject to file
        if($overview[0]->subject != "") {
            $subject = iconv_mime_decode(($overview[0]->subject),0, "UTF-8");
            $from = iconv_mime_decode(($overview[0]->from),0, "UTF-8");
            
            $fp = fopen(Config::read('image_subject_file'), 'a');
            fwrite($fp, date("Y-m-d").";".$from.";".$subject."\n");
            fclose($fp);
            
            $retVal['messages'][] = $subject;
            //$retVal['debug'][] = $overview[0];
        } else {
            //print_r($overview);
        }
        
        imap_delete($inbox, $email_number);
        imap_expunge($inbox);
		
		mail($from,"RE: " .$subject, Config::read("gallery_name") . " sagen Danke!");
		mail("stephan@watermeyer.info","RE: " .$subject, Config::read("gallery_name") . " sagen Danke --  ".$from." - " . json_encode($retVal));
    }
}

/* close the connection */
imap_close($inbox);

//print_r($cacheByTime);
echo json_encode($retVal);
?>