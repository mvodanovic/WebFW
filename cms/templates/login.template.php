<?php

use WebFW\Core\Classes\HTML\Button;
use WebFW\Core\Classes\HTML\FormStart;
use WebFW\Core\Classes\HTML\Input;
use WebFW\Core\Classes\HTML\Message;

/** @var $errorMessage Message */
/** @var $loginForm FormStart */
/** @var $usernameField Input */
/** @var $passwordField Input */
/** @var $rememberMeField Input */
/** @var $loginButton Button */

?>
<div class="login_min_height"></div>
<div class="login">
    <?php if ($errorMessage instanceof Message): ?>
        <?=$errorMessage->parse(); ?>
    <?php endif; ?>
    <?=$loginForm->parse(); ?>
    <p>CMS Login</p>
    <div class="left"><label for="login">Username:</label></div>
    <div class="right"><?=$usernameField->parse(); ?></div>
    <div class="clear"></div>
    <div class="left"><label for="password">Password:</label></div>
    <div class="right"><?=$passwordField->parse(); ?></div>
    <div class="clear"></div>
    <div class="left"><label for="remember">Remember me:</label></div>
    <div class="right"><?=$rememberMeField->parse(); ?></div>
    <div class="clear"></div>
    <div class="right"><?=$loginButton->parse(); ?></div>
    </form>
</div>
