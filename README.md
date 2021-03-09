# Digital Photoframe Script 
Script to providee a digital photoframe / digitaler bilderrahmen (de). 
My requirement was to be able to send photos by email and get them imported into the galery. 
The story behind that is that i would like to give my family the chance to also send pictures by mail to the galery. 
The Galery is displayed on an old Tablet / iPad which i would like to give to my grandparents as a christmas present.

I havent found any App in the AppStore that could provide such a functionality. Also i wanted to be able to store the data in my own webspace.

# Advantages of the Solution
Also your family can send pictures by email to a dedicated adress.
No reason to update the images on the iPad. The galery is auto refreshing so my grandparents do not need to press any button.
# Technical Requirements
Its quiet easy if you already have a Website where you can run the gallery and create mail accounts

## Email Account
Dedicated Mail Account to retrieve the Images.

## Cronjob
The "mailimport.php" should be executed via Cronjob to import the mails on a frequent basis. 
It also does the housekeeping and moves old items to a different folder.

## Google Calendar Integration

- You need to create a Service Account in the "Google Developer Console". Not OAuth 2.0!
- Download the JSON files containing the credentials from the Developer Console
- Copy the email address of that service account (xyz@sincere-hybrid-148116.iam.gserviceaccount.com) and grant permission to the Google Calendar you would like to view. This can be done inside "Gooogle Calendar".
- You need to obtain a copy of the Google API Client and put it in the root directory: https://github.com/googleapis/google-api-php-client
```
root
- src
-- aliases.php
-- ....
- vendor
-- autoload.php
-- ....
- lib
- archive
- index.php
```
# History
The first version of this script was based on mre/unicorn lib, which i've forked and published here:
* https://github.com/phreakadelle/unicorn
