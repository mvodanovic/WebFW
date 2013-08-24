<?php

use WebFW\Core\Classes\HTML\Link;
use WebFW\Core\Classes\HTML\Base\BaseHTMLItem;
use WebFW\Core\Router;

?>
<div class="right"><?=Link::get('Logout', Router::URL('CMSLogin', 'doLogout', '\\WebFW\\CMS\\'), BaseHTMLItem::IMAGE_LOGOUT); ?></div>
<div class="right"><?=$loginMessage; ?></div>
