<?php
    use WebFW\Framework\Core\Exception;
    use WebFW\Framework\Core\Config;
    /** @var $this Exception */
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <title><?=htmlspecialchars($this->caption); ?></title>
    </head>
    <body style="width: 1000px; margin: 0 auto; font-family: 'Verdana', sans-serif;">
        <div style="border: 3px solid red; margin-top: 20px; overflow-x: auto; padding-left: 10px; padding-right: 10px; background-color: #F9966B; font-size: 0.9em; -moz-border-radius: 15px; border-radius: 15px;">
            <h1><?=htmlspecialchars($this->caption); ?></h1>
        </div>
<?php if (Config::get('Debug', 'showInfo') === true): ?>
        <div style="white-space: nowrap; font-family: 'Courier New', monospace; overflow-x: auto; border: 3px solid #9E9E9E; background-color: #DEDEDE; padding: 10px; margin-top: 20px; -moz-border-radius: 15px; border-radius: 15px;">
            <h3 style="margin-bottom: 20px;"><?php echo get_class($this); ?></h3>
            <div><span style="font-weight: bold;">At:</span> <?php echo htmlspecialchars($this->file), ':', $this->line; ?></div>
            <div><span style="font-weight: bold;">Message:</span></div>
            <div style="white-space: pre; margin-bottom: 10px;"><?php echo htmlspecialchars($this->message); ?></div>

<?php if ($this instanceof Exception): ?>
        <h5 style="margin-bottom: 5px;">Debug backtrace:</h5>
        <div style="white-space: pre; font-size: 0.8em;"><?=implode("\n", $this->getDebugBacktrace()); ?></div>

        <h5 style="margin-bottom: 5px;">Exceptions thrown:</h5>
        <div style="white-space: pre; font-size: 0.8em;"><?=implode("\n", $this->getChainedExceptions()); ?></div>
<?php endif; ?>

        </div>
<?php endif; ?>
    </body>
</html>
