<!DOCTYPE html> <?php global $MY_BASE ?>
<html>
    <head>
        <title>Reset Password</title>
        <meta charset="UTF-8">
        <meta name=viewport content="width=device-width, initial-scale=1">
        <link rel="stylesheet" href="<?= $MY_BASE ?>/res/css/login.css">
    </head>
    <body>
        <div class="central">
            <h1>Reset Password</h1>
            <div class="container">
                <form action="login" method="reset">
                    <?php if (isset($msg)) { ?>
                        <div class="error"><?= $msg ?></div>
                        <br />
                    <?php } ?>
                    <input type="hidden" name="token" value="<?= $token ?>" />
                    <input autofocus required minLength="8" type="password" name="newPassword" placeholder="New Password" />
                    <br />
                    <input required type="password" name="confirmPassword" placeholder="Confirm Password" />
                    <br />
                    <div id="lgn_right"><input type="submit" value="Reset" /></div>
                </form>
            </div>
        </div>
    </body>
</html>
