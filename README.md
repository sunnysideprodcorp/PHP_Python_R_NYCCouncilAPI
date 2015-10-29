##Find NYC Councilperson by geographic coordinates

This script sews together the NYT [Districts API](http://developer.nytimes.com/docs/read/districts_api) and the [NYC Council Member lookup website](http://council.nyc.gov/html/members/members.shtml) to determine the most appropriate councilmember to contact based on an exact location. 

The expected use of this file is via a `POST` request to `file_upload.php` with a full line postal address in the `address` field as well as fields for `email`, `latitude`, and `longitude`. There is also an expected file upload and a `license` field expected, but these can be easily removed from the code to adapt the API to other uses. This API was originally written to match a geocoded video upload to the appropriate NYC Council member. 