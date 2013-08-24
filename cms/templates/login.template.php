<?php

use WebFW\Core\Classes\HTML\Button;
use WebFW\Core\Classes\HTML\Input;
use WebFW\Core\Classes\HTML\Message;;
use WebFW\Core\Classes\HTML\Base\BaseHTMLItem;
use WebFW\Core\Router;

?>
<div class="login_min_height"></div>
<div class="login">
    <?php if ($errorMessage !== null): ?>
    <div class="message">
        <?=Message::get($errorMessage); ?>
    </div>
    <?php endif; ?>
    <form method="post" action="<?=Router::URL('CMSLogin', 'doLogin', '\\WebFW\\CMS\\'); ?>">
    <p>CMS Login</p>
    <div>
        <?=Input::get('ctl', 'CMSLogin', 'hidden'); ?>
        <?=Input::get('action', 'doLogin', 'hidden'); ?>
        <?=Input::get('ns', '\\WebFW\\CMS\\', 'hidden'); ?>
    </div>
    <div class="left"><label for="login">Username:</label></div>
    <div class="right"><?=Input::get('login', $login, 'text', null, 'login'); ?></div>
    <div class="clear"></div>
    <div class="left"><label for="password">Password:</label></div>
    <div class="right"><?=Input::get('password', null, 'password', null, 'password'); ?></div>
    <div class="clear"></div>
    <div class="left"><label for="remember">Remember me:</label></div>
    <div class="right"><?=Input::get('remember', null, 'checkbox', null, 'remember'); ?></div>
    <div class="clear"></div>
    <div class="right"><?=Button::get(null, 'Login', BaseHTMLItem::IMAGE_LOGIN, 'submit'); ?></div>
    </form>
</div>
