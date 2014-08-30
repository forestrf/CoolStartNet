<?php
require_once '../php/functions/generic.php';
$db = open_db_session();

require_once 'dropbox-functions.php';

try {
   list($accessToken, $userId, $urlState) = getWebAuth()->finish($_GET);
   assert($urlState === null);  // Since we didn't pass anything in start()
}
catch (dbx\WebAuthException_BadRequest $ex) {
   error_log("/dropbox-auth-finish: bad request: " . $ex->getMessage());
   // Respond with an HTTP 400 and display error page...
}
catch (dbx\WebAuthException_BadState $ex) {
   // Auth session expired.  Restart the auth process.
   header('Location: /dropbox-auth-start');
}
catch (dbx\WebAuthException_Csrf $ex) {
   error_log("/dropbox-auth-finish: CSRF mismatch: " . $ex->getMessage());
   // Respond with HTTP 403 and display error page...
}
catch (dbx\WebAuthException_NotApproved $ex) {
   error_log("/dropbox-auth-finish: not approved: " . $ex->getMessage());
}
catch (dbx\WebAuthException_Provider $ex) {
   error_log("/dropbox-auth-finish: error redirect from Dropbox: " . $ex->getMessage());
}
catch (dbx\Exception $ex) {
   error_log("/dropbox-auth-finish: error communicating with Dropbox API: " . $ex->getMessage());
}

// All ok
// Save the token with the user
$db->setDropboxAccessToken($accessToken);
$_SESSION['user']['dropbox_accessToken'] = $accessToken;


// We can now use $accessToken to make API requests.
//$dbxClient = new dbx\Client($_SESSION['user']['dropbox_accessToken'], DROPBOX_APP_NAME);