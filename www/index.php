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


header('Content-Type: text/html; charset=UTF-8');

require_once 'php/functions/generic.php';
$db = open_db_session();


// TO DO: save the render to the database (cache it). Update it (to a blank page) when a widget update affects a cache.
// If the cache is a blank page, regenerate cache and serve it. Otherwise serve cache.

require_once 'php/lib/renderer.php';

echo render($db, false);
?>