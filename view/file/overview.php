<!DOCTYPE html>
<html>

    <head>
        <title><?= $block ?> Files</title>
        <meta charset="utf-8" />
        <meta name=viewport content="width=device-width, initial-scale=1">
        <link rel="stylesheet" href="res/css/lib/font-awesome-all.min.css">
        <link rel="stylesheet" href="res/css/common-1.3.css">
        <link rel="stylesheet" href="res/css/adm-1.0.css">
        <link rel="stylesheet" href="res/css/file.css">
        <script src="res/js/file.js"></script>
    </head>

    <body>
        <?php include 'header.php'; ?>
        <main>
            <?php include 'areas.php'; ?>

            <nav class="tools">
                <?php if (hasMinAuth('instructor')) { ?>
                <i title="Make Directory" id="addDir" class="fa-solid fa-folder-plus"></i>
                <?php } ?>
            </nav>

            <div id="content">
                <h1>Files</h1>
                <?php if (hasMinAuth('instructor')) { ?>
                <div>Students only see the contents of public</div>
                <?php } ?>
                <div class="files">
                    <?php include 'listing.php'; ?>
                </div>

            </div>
        </main>
    </body>

</html>
