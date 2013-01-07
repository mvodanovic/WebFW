<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <title><?php echo $this->code === 404 ? '404 Not Found' : '500 Internal Server Error'; ?></title>
    </head>
    <body style="width: 1000px; margin: 0 auto; font-family: 'Verdana', sans-serif;">
        <div style="border: 3px solid red; margin-top: 20px; overflow-x: auto; padding-left: 10px; padding-right: 10px; background-color: #F9966B; font-size: 0.9em; -moz-border-radius: 15px; border-radius: 15px;">
            <h1><?php echo $this->code === 404 ? '404 Not Found' : '500 Internal Server Error'; ?></h1>
        </div>
<?php if (\Config\Specifics\Data::GetItem('SHOW_DEBUG_INFO') === true): ?>
        <div style="white-space: nowrap; font-family: 'Courier New', monospace; overflow-x: auto; border: 3px solid #9E9E9E; background-color: #DEDEDE; padding: 10px; margin-top: 20px; -moz-border-radius: 15px; border-radius: 15px;">
            <h3 style="margin-bottom: 20px;"><?php echo htmlspecialchars($this->message); ?></h3>
            <div>File: <?php echo htmlspecialchars($this->file); ?></div>
            <div>Line: <?php echo $this->line; ?></div>
            <div style="white-space: pre;">
<?php debug_print_backtrace(); ?>
            </div>
        </div>
<?php endif; ?>
    </body>
</html>
