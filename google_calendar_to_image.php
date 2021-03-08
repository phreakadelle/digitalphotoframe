<?php
require_once ("config.php");
use swa\digitalphotoframe\lib\Config;

require __DIR__ . '/vendor/autoload.php';

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
$im = imagecreate(800, 600);

// White background, blue text
$bg = imagecolorallocate($im, 255, 255, 255);
$textcolor = imagecolorallocate($im, 0, 0, 0);

$counter = 5;
if (empty($events)) {
    print "No upcoming events found.\n";
} else {
    foreach ($events as $event) {
        
        if($event->getStart()->getDate() != null) {
            $time = strtotime($event->getStart()->getDate());
            $theDate = strftime ("%A, %d.%m.%Y den ganzen Tag", $time);    
        } else  {
            $time = strtotime($event->getStart()->getDateTime());
            $theDate = strftime ("%A, %d.%m.%Y um %H:%M Uhr", $time);
        }
        // Heute?
        if(strpos($theDate, date("d.m.Y"))) {
            $theDate = "Heute, ". $theDate;
        }
        imagestring($im, 15, 5, $counter, $theDate ." - " . $event->getSummary(), $textcolor);
        $counter += 25;
    }
}

header('Content-type: image/png');

imagepng($im);
imagedestroy($im);
 
?>
