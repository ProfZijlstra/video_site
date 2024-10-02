<!DOCTYPE html>
<html>

<head>
    <title>Course Offerings</title>
    <meta charset="utf-8" />
    <meta name=viewport content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="res/css/lib/font-awesome-all.min.css">
    <link rel="stylesheet" href="res/css/common-1.3.css">
    <link rel="stylesheet" href="res/css/adm-1.0.css">
    <style>
        #content {
            text-align: center;
        }
        form {
            display: inline;
        }
    </style>
</head>

<body>
    <?php include("header.php"); ?>
    <main>
        <div id="content">
            <p>
                <?= $first ?> <?= $last ?> would like to observe <?= $course ?> <?= $block ?>
            </p>
            <form method="POST">
                <input type="hidden" name="uid" value="<?= $user_id ?>" />
                <input type="hidden" name="oid" value="<?= $offering_id ?>" />
                <input type="hidden" name="allow" value="1" />
                <button>Allow Request</button>
            </form>
            <form method="POST">
                <input type="hidden" name="uid" value="<?= $user_id ?>" />
                <input type="hidden" name="oid" value="<?= $offering_id ?>" />
                <input type="hidden" name="allow" value="0" />
                <button>Deny Request</button>
            </form>
        </div>
    </main>
</body>

</html>