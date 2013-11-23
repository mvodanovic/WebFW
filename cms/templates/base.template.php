<?php

use WebFW\CMS\Components\Navigation;
use WebFW\CMS\Components\UserActions;
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
<body data-selected-menu-item="<?=$controller instanceof Controller ? htmlspecialchars($controller->getSelectedMenuItem()) : ''; ?>">
    <div class="container">

        <header>
            <div class="left">WebFW CMS</div>
            <?=Framework::runComponent(UserActions::className(), null, $controller); ?>
        </header>

        <?=Framework::runComponent(Navigation::className(), null, $controller); ?>

        <div class="content">

            <?php echo $htmlBody; ?>

        </div>

        <footer>
            <div class="right"><span>Powered by <a href="https://github.com/mvodanovic/webFW">WebFW Framework</a></span></div>
        </footer>

    </div>
</body>
</html>
