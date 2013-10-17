<?php

use WebFW\Core\Framework;
use WebFW\CMS\Controller;

/**
 * @var $htmlHead string
 * @var $htmlBody string
 * @var $controller Controller
 */

?>
<!DOCTYPE html>

<html>
<head>
    <?=$htmlHead; ?>
</head>
<body>
    <div class="container">

        <header>
            <div class="left">WebFW CMS</div>
            <?=Framework::runComponent('UserActions', '\\WebFW\\CMS\\Components\\', null, $controller); ?>
        </header>

        <?=Framework::runComponent('Navigation', '\\WebFW\\CMS\\Components\\', null, $controller); ?>

        <div class="content">

            <?php echo $htmlBody; ?>

        </div>

        <footer>
            <div class="right"><span>Powered by <a href="https://github.com/mvodanovic/webFW">WebFW Framework</a></span></div>
        </footer>

    </div>
</body>
</html>
