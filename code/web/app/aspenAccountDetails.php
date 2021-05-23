<?php
# ****************************************************************************************************************************
# * Last Edit: May 3, 2021
# * - displays the account information for the user
# *
# * 05-03-21: altered how the location information is grabbed from the site - CZ
# * 04-08-21: Base Version - CZ
# ****************************************************************************************************************************

# ****************************************************************************************************************************
# * include the helper file that holds the URL information by client
# ****************************************************************************************************************************
include_once 'config.php';

# ****************************************************************************************************************************
# * grab the passed location parameter, then find the path
# ****************************************************************************************************************************
$library = $_GET['library'];
$locationInfo = urlPath($library);
$urlPath      = $locationInfo[0];
$shortname    = $locationInfo[1];

# ****************************************************************************************************************************
# * Prep the patron information for checking - dummy out something just in case
# ****************************************************************************************************************************
$barcode = "thisisadummybarcodeincaseitisleftblank";
$pin     = 1234567890;

if (! empty($_GET['barcode'])) { $barcode = $_GET['barcode']; }
if (! empty($_GET['pin'])) { $pin = $_GET['pin']; }  

# ****************************************************************************************************************************
# * search link to the catalogue
# ****************************************************************************************************************************
$borrowHistory = $urlPath . '/API/UserAPI?method=getPatronProfile&username=' . $barcode . '&password=' . $pin;

# ****************************************************************************************************************************
# * run the report and grab the JSON
# ****************************************************************************************************************************
$jsonData = json_decode(file_get_contents($borrowHistory), true);

$accountInfo['holdsAvailable'] = 0 + $jsonData['result']['profile']['numHoldsAvailableIls'];
$accountInfo['holdsEProduct']  = 0 + $jsonData['result']['profile']['numHoldsOverDrive'];
$accountInfo['holdsILS']       = 0 + $jsonData['result']['profile']['numHoldsIls'];
$accountInfo['numCheckedOut']  = 0 + $jsonData['result']['profile']['numCheckedOutIls'];

# ****************************************************************************************************************************
# * Output to JSON
# ****************************************************************************************************************************
header('Content-Type: application/json');
echo json_encode($accountInfo);
?>