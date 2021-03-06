<?php
require_once ("config.php");
use swa\digitalphotoframe\lib\Config;

require __DIR__ . '/vendor/autoload.php';

// For strftime
setlocale (LC_ALL, 'de_DE');

// Get the API client and construct the service object.
$client = new Google_Client();
$client->setApplicationName('Google Calendar API PHP Quickstart');
$client->setScopes(Google_Service_Calendar::CALENDAR_READONLY);
$client->setAuthConfig(Config::read("google_calendar_tokenfile"));    
   
$service = new Google_Service_Calendar($client);

// Print the next 10 events on the user's calendar.
$optParams = array(
  'maxResults' => 10,
  'orderBy' => 'startTime',
  'singleEvents' => true,
  'timeMin' => date('c'),
  'timeMax' => date('Y-m-d\TH:i:sP', strtotime("+14 days"))
);
$results = $service->events->listEvents(Config::read("google_calendar_id"), $optParams);
$events = $results->getItems();

// Create the image

$im = imagecreatetruecolor(800, 600);

// Create some colors
$white = imagecolorallocate($im, 255, 255, 255);
$black = imagecolorallocate($im, 0, 0, 0);
imagefilledrectangle($im, 0, 0, 800, 600, $white);

$font = 'arial.ttf';
$fontBold = 'arial_bold.ttf';

$lineSpacing = 0;
$lastDay = "";
if (empty($events)) {
    imagettftext($im, 12, 0, 10, $lineSpacing, $black, $fontBold, "Keine Termine gefunden");
} else {
    foreach ($events as $event) {
        
        // All Day Events or time based events?
        if($event->getStart()->getDate() != null) {
            $time = strtotime($event->getStart()->getDate());
            $theDate = strftime ("%A, %d.%m.%Y", $time);    
            $time = "den ganzen Tag";
        } else  {
            $time = strtotime($event->getStart()->getDateTime());
            $theDate = strftime ("%A, %d.%m.%Y", $time);
            $time = strftime ("%H:%M Uhr", $time);
        }
        
        // Today?
        if(strpos($theDate, date("d.m.Y"))) {
            $theDate = "Heute, ". $theDate;
        }
        
        // Print a Headline
        if($lastDate != $theDate) {
            $lineSpacing += 50;
            imagettftext($im, 12, 0, 10, $lineSpacing, $black, $fontBold, $theDate);
        }
        
        $lineSpacing += 25; // line spacing
        
        // Print the String
        imagettftext($im, 12, 0, 10, $lineSpacing, $black, $font, $time ." - ". $event->getSummary());
        
        $lastDate = $theDate;
    }
}

header('Content-type: image/png');
imagepng($im);
imagedestroy($im);
?>
