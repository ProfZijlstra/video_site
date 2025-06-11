<!DOCTYPE html> <?php global $MY_BASE ?>
<html>

<head>
    <title>Edit Lab</title>
    <meta charset="utf-8" />
    <meta name=viewport content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="<?= $MY_BASE ?>/res/css/lib/font-awesome-all.min.css">
    <link rel="stylesheet" href="<?= $MY_BASE ?>/res/css/common-1.3.css">
    <link rel="stylesheet" href="<?= $MY_BASE ?>/res/css/adm-1.0.css">
    <link rel="stylesheet" href="<?= $MY_BASE ?>/res/css/lib/prism.css">
    <link rel="stylesheet" href="<?= $MY_BASE ?>/res/css/lab-1.10.css">
    <script src="<?= $MY_BASE ?>/res/js/lib/prism.js"></script>
    <script src="<?= $MY_BASE ?>/res/js/markdown-1.8.js"></script>
    <script src="<?= $MY_BASE ?>/res/js/lab/edit.js"></script>
</head>

<body id="editLab" class="lab">
    <?php include 'header.php'; ?>
    <main>
        <nav class="back" title="Back">
            <a href="../../lab">
                <i class="fa-solid fa-arrow-left"></i>
            </a>
        </nav>
        <?php include 'areas.php'; ?>
        <nav class="tools">
            <a title="Preview" href="../preview?l=<?= $lab['id'] ?>">
                <i id="previewBtn" class="fa-solid fa-eye"></i>
            </a>
            <form id="delLab" data-id="<?= $lab['id'] ?>" action="del" method="POST"><i id="delBtn" title="Delete Lab" class="far fa-trash-alt"></i></form>
        </nav>
        <div id="content">
            <div class="lab">
                <form id="updateLab" action="<?= '../'.$lab['id'] ?>" method="POST" data-id="<?= $lab['id'] ?>">
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
                            <?php foreach ($days as $day) { ?>
                                <option value="<?= $day['id'] ?>" <?= $day['id'] == $lab['day_id'] ? 'selected' : '' ?>>
                                    <?= $day['abbr'].' - '.$day['desc'] ?>
                                </option>
                            <?php } ?>
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
                            <option value="individual" <?= $lab['type'] == 'individual' ? 'selected' : '' ?>>Individual</option>
                            <option value="group" <?= $lab['type'] == 'group' ? 'selected' : '' ?>>Group</option>
                        </select>
                    </div>
                    <div title="Calculated from deliverables">
                        <label>Points:</label>
                        <input disabled id="labPoints" class="name" type="number" name="points" value="<?= $labPoints ?>" />
                    </div>
                </form>
            </div>

            <h3 class="<?= count($deliverables) == 0 ? 'empty' : '' ?>">
                Deliverables
                <i id="addDelivIcon" title="Add Deliverable" class="far fa-plus-square"></i>
            </h3>
            <?php if (! $deliverables) { ?>
                <div id="noDelivs">
                    <h2>No Deliverables Yet!</h2>
                    <p class="warning">Click the <i class="far fa-plus-square"></i> button in the top right to add at least one deliverable.</p>
                </div>
            <?php } ?>
            <div id="deliverables">
                <?php foreach ($deliverables as $deliv) { ?>
                    <?php include 'view/lab/deliverable.php'?>
                <?php } ?>
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
            <input type="number" id="byte" placeholder="Byte insertion location" />
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

            <div>
                <label>Type</label>
                <select name="type" id="checkType">
                        <option value="present">Is Present</option>
                        <option value="not_present">Is Not Present</option>
                        <option value="txt_wm">Text Watermark</option>
                        <option value="png_wm">.png Watermark</option>
                        <option value="size_lt">Size Less Than</option>
                        <option value="size_gt">Size Greater Than</option>
                </select>
            </div>

            <div id="fileField">
                <label>File:</label>
                <input id="checkFile" type="text" name="file">
            </div>

            <div id="byteField" class="hide">
                <label>Byte:</label>
                <input id="checkByte" type="number" name="byte" placeholder="Check at byte">
            </div>

            <div>
                <label>Public:</label>
                <span id="block" class="active" title="Blocking checks are public / displayed to users">
                    <i class="fa-solid fa-bullhorn"></i>
                    Publicly announce and show result of check
                </span>
            </div>

            <div class="btn">
                <button type="submit" id="addZipCheckBtn">Add Check</button>
            </div>
        </form>
    </dialog>
</body>

</html>
