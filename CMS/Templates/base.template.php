<?php

use WebFW\CMS\Components\Navigation;
use WebFW\CMS\Components\UserActions;
use WebFW\Core\Framework;
use WebFW\CMS\Controller;

/**
 * @var string $htmlHead
 * @var string $htmlBody
 * @var Controller $controller
 */

?>
<!DOCTYPE html>

<html>
<head>
    <?=$htmlHead; ?>
</head>
<body data-selected-menu-item="<?=$controller instanceof Controller ? htmlspecialchars($controller->getSelectedMenuItem()) : ''; ?>">
    <div class="container">

        <header>
            <div class="left">WebFW CMS</div>
            <?=Framework::runComponent(UserActions::className()); ?>
        </header>

        <?=Framework::runComponent(Navigation::className()); ?>

        <div class="content">

            <?php echo $htmlBody; ?>

        </div>

        <footer>
            <div class="right"><span>Powered by <a href="https://github.com/mvodanovic/webFW">WebFW Framework</a></span></div>
        </footer>

    </div>
</body>
</html>
