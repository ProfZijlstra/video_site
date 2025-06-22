<!DOCTYPE html> <?php global $MY_BASE ?>
<html>
    <head>
        <title>Forgot Password</title>
        <meta charset="UTF-8">
        <meta name=viewport content="width=device-width, initial-scale=1">
        <link rel="stylesheet" href="<?= $MY_BASE ?>/res/css/login.css">
    </head>
    <body>
        <div class="central">
            <h1>Forgot Password</h1>
            <div class="container">
                <form action="forgot" method="post">
                    <?php if (isset($msg)) { ?>
                        <div class="error"><?= $msg ?></div>
                        <br />
                    <?php } ?>
                    <input type="text" name="email" placeholder="Email Address" autofocus />
                    <br />
                    <div id="lgn_right"><input type="submit" value="Forgot" /></div>
                </form>
            </div>
        </div>
    </body>
</html>
