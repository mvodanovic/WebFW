<?php

use \WebFW\Core\Framework;

?>
<!DOCTYPE html>

<html>
<head>
    <title><?php echo htmlspecialchars($pageTitle); ?> - WebFW CMS</title>
    <?php echo $htmlHead; ?>
</head>
<body>
    <div class="container">

        <header>
            <div class="left">WebFW CMS</div>
            <?=Framework::runComponent('UserActions', '\\WebFW\\CMS\\Components\\'); ?>
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
