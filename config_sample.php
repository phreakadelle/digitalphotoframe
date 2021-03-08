<?php

include_once 'lib/Config.php';
include_once 'lib/FolderUtil.php';

use swa\digitalphotoframe\lib\Config;

// You can set a name for your gallery which
// will be shown at the homepage.
Config::write("gallery_name", "Titel");


Config::write("image_url", "https://domain.tld/index.php");
Config::write("mail_host", "{domain.tld:993/imap/ssl}INBOX");
Config::write("mail_user", "user");
Config::write("mail_password", "password");
Config::write("image_cache", "20");
Config::write("image_locale", "de_DE");
Config::write("image_message_age", "2"); // message age in days
Config::write("image_subject_file", "subject.txt"); // file that stores the mail subjects
Config::write("image_known_senders", array( "Foo Bar <foo@domain.tld>" => "Mr. Foo"); // known senders
Config::write("mail_image_directory_current", "images/");
Config::write("mail_image_directory_archive", "archive/");
Config::write("mail_image_ignore_fileformats", array("avi", "mpg", "mpeg", "mov", "png"));


Config::write("calendar_frequence", 5);
// Google Calendar iFrame URL
Config::write("calendar_iframe_url", "");

Config::write("google_calendar_id", "xyz@group.calendar.google.com");
Config::write("google_calendar_tokenfile", "google_calendar_token.json");