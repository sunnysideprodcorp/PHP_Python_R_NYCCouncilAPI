<?php
// generalized server email functions + email functions specific to this file uploading API
// these are utility functions written on top of PHPMailer
require_once("email.php");
require_once("upload_mail_functions.php");



// Setting up constants and useful variables from $_POST info

// MongoDB record constants
$SCRAPY_FAIL = "scrapyFail";
$NYT_FAIL = "nytFail";
$SUCCESS = "success";

// constant location parameters
$TARGET_DIR = 
$SCRAPY_DIR = 
$ADMIN_EMAIL =

// mailing address
$address = htmlspecialchars($_POST['address']);
$email = htmlspecialchars($_POST['email']);
$address_components = explode(",", $address);
$street_address = $address_components[0];

// actual location
$latitude = htmlspecialchars($_POST['latitude']);
$longitude = htmlspecialchars($_POST['longitude']);

// other relevant parameters
$license = htmlspecialchars($_POST['license']);
$time = time();



// move uploaded file to appropriate location, file name is made unique before upload
$target_file = $TARGET_DIR . $_FILES["upload"]["name"];
$success = move_uploaded_file($_FILES["upload"]["tmp_name"], $target_file);
if($success) mail("aajnielsen@gmail.com", "file uploaded", $latitude);
else  fail_email($mail, ' ', $email, $address, $short_file." FAILED TO UPlOAD" ); 

// this becomes the unique record identifier since it is unique to the time and place of the video
// (coupled with a random number generated at time file was saved, just to be safe
$filename = $target_file; 
$short_file = $_FILES["upload"]["name"];



// connect to Mongo and insert basic data
$m = new MongoClient();
$db = $m->selectDB("idling");
$collection = $db->videos;
$collection->insert(array("email" => $email, "address" => $address, "latitude" => $latitude, "longitude" => $longitude, "license" => $license, "time" => $time, "file" => $filename));




// Now on to getting details so we can contact the relevant NYC councilperson
//now first get borough with R script
$cmd = "r get_borough.R ".escapeshellcmd($latitude)." ".escapeshellcmd($longitude);
exec($cmd, $resultVar);

//then if a borough is returned (response is null if API call fails), feed into Scrapy
if(strlen($resultVar[0]) > 1)
{ 
    $borough_abbrev = substr($resultVar[0], 0, 4);
    $boroughAbbreviations = ["Manh" => 1, "Bron" => 2, "Broo" => 3, "Quee" => 4];
    $borough = array_key_exists($abbreviation, $abbreviationList) ? $boroughAbbreviations[$abbreviation] : 5;

    $cmd = "cd ".$SCRAPY_DIR." && scrapy crawl nyc -a address=".escapeshellcmd($street_address)." -a borough='$borough'"; 
    exec($cmd,  $resultVar);

    // got back informative feedback from Scrapy with a contact email address for city councilperson 
    if(strlen($resultVar[0])>3){
        // send an email to appropriate NYC councilperson and success email to the admin
        $council_mail = str_replace("mailto:", "", $resultVar[0]);
        success_email($mail, $council_mail, $email, $address, $short_file );	
        $collection->update(array("file" => $filename), array("email_success" => $SUCCESS));
    }		
    // API call failed, send email to the admin who can look up the contact info manually
    else{
        fail_email($mail, ' ', $email, $address, $short_file ); 
        $collection->update(array("file" => $filename), array("email_success" => $SCRAPY_FAIL));
    }
}
// never got a meaningful response from NYT API so no need to proceed further
// notify admin that api query failed
else{
    fail_email($mail, ' ', $email, $address, $short_file." NYT API query failed" ); 
    $collection->update(array("file" => $filename), array("email_success" => $NYT_FAIL));	
}

?>
