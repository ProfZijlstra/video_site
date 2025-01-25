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
                <div>Files uploaded in other areas (lecture, lab, quiz) are placed in their respective directories</div>
                <?php } ?>
                <div class="files">
                    <?php include 'listing.php'; ?>
                </div>

            </div>
        </main>
        <dialog id="uploadDialog" class="modal">
            <i id="closeUploadDialog" class="fas fa-times-circle close"></i>
            <h3>Upload File</h3>
            <form id="uploadForm" method="POST" action="">
                <input type="hidden" name="location" id="uploadLocation" value="">
                <input type="file" name="file" id="uploadFile" value="" autofocus>
            </form>
        </dialog>
        <dialog id="renameDialog" class="modal">
            <i id="closeRenameDialog" class="fas fa-times-circle close"></i>
            <h3>Rename</h3>
            <form id="renameForm" method="POST" action="file/rename">
                <input type="hidden" name="src" id="renameSrc">
                <input type="text" name="dst" id="renameDst" autofocus>
                <button id="submitRename" >Submit</button>
            </form>
            <div>Rename can also be used to move files</div>
        </dialog>
        <dialog id="makeDirDialog" class="modal">
            <i id="closeMakeDir" class="fas fa-times-circle close"></i>
            <h3>Make Directory</h3>
            <form id="makeDirForm" method="POST" action="file/makeDir">
                <input id="makeDirField" name="dir" placeholder="public/newDir" autofocus>
                <button id="submitMakeDir" >Submit</button>
            </form>
        </dialog>
    </body>

</html>
