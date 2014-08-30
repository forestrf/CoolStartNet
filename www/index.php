<?php

# This file is part of CoolStart.net.
#
#	 CoolStart.net is free software: you can redistribute it and/or modify
#	 it under the terms of the GNU Affero General Public License as published by
#	 the Free Software Foundation, either version 3 of the License, or
#	 (at your option) any later version.
#
#	 CoolStart.net is distributed in the hope that it will be useful,
#	 but WITHOUT ANY WARRANTY; without even the implied warranty of
#	 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
#	 GNU Affero General Public License for more details.
#
#	 You should have received a copy of the GNU Affero General Public License
#	 along with CoolStart.net.  If not, see <http://www.gnu.org/licenses/>.


require_once 'php/functions/generic.php';
$db = open_db_session();
if(!isset($_SESSION['user'])){
	// Not logged. Use default user.
	$_SESSION['user'] = $db -> check_nick_password(DEFAULT_USER_NICK, DEFAULT_USER_PASSWORD);
	$accessTokens = $db->getAllAccessToken();
	foreach($accessTokens as $service => $accessToken){
		$_SESSION['user'][$service] = $accessToken;
	}
}

require_once 'php/renderer.php';

?>