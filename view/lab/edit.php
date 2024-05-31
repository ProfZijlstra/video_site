<!DOCTYPE html>
<html>

<head>
    <title>Edit Lab</title>
    <meta charset="utf-8" />
    <meta name=viewport content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="res/css/lib/font-awesome-all.min.css">
    <link rel="stylesheet" href="res/css/common-1.2.css">
    <link rel="stylesheet" href="res/css/adm.css">
    <link rel="stylesheet" href="res/css/lib/prism.css">
    <link rel="stylesheet" href="res/css/lab.css">
    <script src="res/js/lib/prism.js"></script>
    <script src="res/js/markdown-1.2.js"></script>
    <script src="res/js/lab/edit.js"></script>
</head>

<body id="editLab" class="lab">
    <?php include("header.php"); ?>
    <main>
        <nav class="back" title="Back">
            <a href="../../lab">
                <i class="fa-solid fa-arrow-left"></i>
            </a>
        </nav>
        <nav class="tools">
            <a title="Preview" href="../preview?l=<?= $lab['id'] ?>">
                <i id="previewBtn" class="fa-solid fa-eye"></i>
            </a>
            <form id="delLab" data-id="<?= $lab['id'] ?>" action="del" method="POST"><i id="delBtn" title="Delete Lab" class="far fa-trash-alt"></i></form>
        </nav>
        <div id="content">
            <div class="lab">
                <form id="updateLab" action="<?= "../" . $lab['id'] ?>" method="POST" data-id="<?= $lab['id'] ?>">
                    <div class="status" data-id="<?= $lab['id'] ?>">
                        <input id="visible" name="visible" type="checkbox" class="visible" value="1" <?= $lab['visible'] ? 'checked' : '' ?> /><label for="visible"> Visible</label>
                    </div>
                    <div>
                        <label>Name:</label>
                        <input type="text" name="name" class="name" value="<?= $lab['name'] ?>" />
                    </div>
                    <div>
                        <label>Day:</label>
                        <select name="day_id" id="day_id">
                            <?php foreach ($days as $day) : ?>
                                <option value="<?= $day['id'] ?>" <?= $day['id'] == $lab['day_id'] ? "selected" : "" ?>>
                                    <?= $day['abbr'] . " - " . $day['desc'] ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label>Start:</label>
                        <input type="date" name="startdate" class="startdate" value="<?= substr($lab['start'], 0, 10) ?>" />
                        <input type="time" name="starttime" class="starttime" value="<?= substr($lab['start'], 11) ?>" />
                    </div>
                    <div>
                        <label>Stop:</label>
                        <input type="date" name="stopdate" class="stopdate" value="<?= substr($lab['stop'], 0, 10) ?>" />
                        <input type="time" name="stoptime" class="stoptime" value="<?= substr($lab['stop'], 11) ?>" />
                    </div>
                    <div>
                        <label>Type:</label>
                        <select name="type">
                            <option value="individual" <?= $lab['type'] == "individual" ? "selected" : "" ?>>Individual</option>
                            <option value="group" <?= $lab['type'] == "group" ? "selected" : "" ?>>Group</option>
                        </select>
                    </div>
                    <div title="Calculated from deliverables">
                        <label>Points:</label>
                        <input disabled id="labPoints" class="name" type="number" name="points" value="<?= $labPoints ?>" />
                    </div>
                    <h3 class="lab">Lab Description:</h3>
                    <div class="dcontainer">
                        <div class="textContainer">
                            <textarea name="desc" class="text" placeholder="Write your lab description here" data-md="Use **markdown** syntax in your text like:&#10;&#10;```javascript&#10;const code = &quot;highlighted&quot;&semi;&#10;```" data-txt="Write your lab description here"><?= $lab['desc'] ?></textarea>

                            <i title="Markdown" class="details txt fa-brands fa-markdown <?= $lab['hasMarkDown'] ? "active" : "" ?>"></i>
                            <input type="hidden" id="descMarkDown" name="hasMarkDown" value="<?= $lab['hasMarkDown'] ?>" />
                            <div class="mdContainer <?= $lab['hasMarkDown'] ? "active" : "" ?>">
                                <div class="preview"><button class="previewBtn">Preview Markdown</button></div>
                                <div class="previewArea"></div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div>
                <h3 class="<?= count($attachments) == 0 ? 'empty' : '' ?>">Attachments
                    <i id="attachBtn" title="Add attachment" class="fa-solid fa-paperclip"></i>
                    <input type="file" name="attachment" id="attachment" />
                    <i id="attachSpin" class="fa-solid fa-circle-notch"></i>
                </h3>
                <div id="attachments">
                    <?php foreach ($attachments as $attachment) : ?>
                        <?php include('view/lab/attachment.php') ?>
                    <?php endforeach; ?>
                </div>
            </div>

            <h3 class="<?= count($deliverables) == 0 ? 'empty' : '' ?>">
                Deliverables
                <i id="addDelivIcon" title="Add Deliverable" class="far fa-plus-square"></i>
            </h3>
            <?php if (!$deliverables) : ?>
                <div id="noDelivs">
                    <h2>No Deliverables Yet!</h2>
                    <p class="warning">Click the <i class="far fa-plus-square"></i> button in the top right to add at least one deliverable.</p>
                </div>
            <?php endif; ?>
            <div id="deliverables">
                <?php foreach ($deliverables as $deliv) : ?>
                    <?php include('view/lab/deliverable.php') ?>
                <?php endforeach; ?>
            </div>
    </main>
    <dialog id="addDelivDialog" class="modal">
        <i id="closeAddDialog" class="fas fa-times-circle close"></i>
        <h3>Add Deliverable</h3>
        <label>Type:</label>
        <select id="delivType" autofocus>
            <option value="txt">Text</option>
            <option value="img">Image</option>
            <option value="pdf">PDF</option>
            <option value="url">URL</option>
            <option value="zip">Code as .zip</option>
        </select>
        <div class="btn">
            <button id="addDelivBtn" data-seq="<?= count($deliverables) + 1 ?>" data-lab_id="<?= $lab['id'] ?>">Add</button>
        </div>
    </dialog>

    <dialog id="zipActionDialog" class="modal">
        <i id="closeZipDialog" class="fas fa-times-circle close"></i>
        <h3>Zip Download Actions</h3>
        <div id="zipActions">
            <!-- Get zip actions when dialog opens -->
        </div>
        <form id="zipActionForm" method="POST" action="">
            <input type="hidden" name="attachment_id" id="attachment_id" value="">
            <label>Action:</label>
            <select id="zipAction" autofocus>
                <option value="text">Watermark a plain text file</option>
                <option value="png">Watermark a .png image file</option>
            </select>
            <label>File:</label>
            <select id="fileSelect">
                <!-- Get zip file listing when dialog opens -->
            </select>
            <label>Byte:</label>
            <input type="number" id="byte" placeholder="Insert at byte" />
            <div class="btn">
                <button type="button" id="addZipActionBtn">Add Action</button>
            </div>
        </form>
    </dialog>

    <dialog id="zipCheckDialog" class="modal">
        <i id="closeCheckDialog" class="fas fa-times-circle close"></i>
        <h3>Zip Upload Checks</h3>
        <idv id="zipChecks">
            <!-- Get zip checks when dialog opens -->
        </idv>
        <form id="zipCheckForm" method="POST" action="">
            <input type="hidden" name="deliverable_id" id="deliverable_id" value="">

            <label>Type</label>
            <select name="type" id="checkType">
                    <option value="present">Is Present</option>
                    <option value="not_present">Is Not Present</option>
                    <option value="txt_wm">Text Watermark</option>
                    <option value="png_wm">.png Watermark</option>
            </select>
            <i id="checkPublic" title="Publicly announced / reported check" class="fa-solid fa-eye"></i>

            <label>File:</label>
            <input type="text" name="file">

            <label>Byte:</label>
            <input type="number" name="byte" placeholder="Check at byte">
        </form>
    </dialog>
</body>

</html>
