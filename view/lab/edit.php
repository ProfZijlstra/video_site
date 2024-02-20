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
    <link rel="stylesheet" href="res/css/quiz-1.4.css">
    <script src="res/js/lib/prism.js"></script>
    <script src="res/js/markdown-1.1.js"></script>
    <style>
        main#editLab {
            #updateLab .lab {
                margin-top: 20px;
                margin-bottom: 0px;
                text-align: left;
            }

            #content p.warning {
                font-weight: bold;
                text-align: center;
            }

            div.qcontainer {
                text-align: left;
                border-top: none;
                margin-top: 0px;
                position: relative;
            }

            .textContainer {
                width: 100%;
            }

            .textContainer textarea {
                width: 100%;
                height: 100px;
                resize: vertical;
            }

            #attachment {
                display: none;
            }

            .attachHeader {
                position: relative;
                margin-bottom: 0px;

                i.fa-paperclip {
                    position: absolute;
                    top: 0;
                    right: 0;
                    cursor: pointer;
                }
            }

            .attachment {
                margin-bottom: 3px;
                padding: 3px 5px;
                border: 1px solid var(--border);
                background-color: var(--white);
                position: relative;

                i.fa-xmark {
                    position: absolute;
                    top: 5px;
                    right: 8px;
                    cursor: pointer;
                }
            }
        }

        dialog {
            overflow: visible;

            #close-overlay {
                position: absolute;
                top: -10px;
                right: -10px;
            }
        }
    </style>
    <script>
        window.addEventListener("load", () => {
            // auto update on detail change
            function updateDetails() {
                const form = document.forms.updateLab;
                const id = form.dataset.id;
                const visible = form.visible.checked ? 1 : 0;
                const name = encodeURIComponent(form.name.value);
                const day_id = form.day_id.value;
                const startdate = form.startdate.value;
                const starttime = form.starttime.value;
                const stopdate = form.stopdate.value;
                const stoptime = form.stoptime.value;
                const points = form.points.value;
                const type = form.type.value;
                const hasMarkDown = form.hasMarkDown.value;
                const desc = form.desc.value;

                fetch(`../${id}`, {
                    method: "PUT",
                    body: `visible=${visible}&name=${name}&day_id=${day_id}&startdate=${startdate}&starttime=${starttime}&stopdate=${stopdate}&stoptime=${stoptime}&points=${points}&type=${type}&hasMarkDown=${hasMarkDown}&desc=${desc}`,
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                });
            }
            const form = document.forms.updateLab;
            const inputs = form.querySelectorAll("input, select, textarea");
            inputs.forEach(input => {
                input.addEventListener("change", updateDetails);
            });

            // markdown related functions
            function mdToggle() {
                const descMarkDown = document.getElementById("descMarkDown");
                descMarkDown.value = descMarkDown.value == "1" ? "0" : "1";
            }
            // enable markdown previews
            MARKDOWN.enablePreview("../../markdown");
            MARKDOWN.activateButtons(mdToggle);

            // enable delete button
            document.getElementById("delBtn").addEventListener("click", (e) => {
                // TODO check if there are any submissions
                if (confirm("Are you sure you want to delete this lab?")) {
                    fetch(`../${document.forms.delLab.dataset.id}`, {
                        method: "DELETE"
                    }).then(() => {
                        window.location = "../../lab";
                    });
                }
            });

            // enable add attachment button
            document.getElementById("attachBtn").addEventListener("click", () => {
                document.getElementById("attachment").click();
            });
            document.getElementById("attachment").addEventListener("change", function() {
                // upload attachment
                const spinner = document.getElementById("attachSpin");
                spinner.classList.add("rotate");
                const data = new FormData();
                data.append("attachment", this.files[0]);
                fetch("attach", {
                        method: "POST",
                        body: data
                    })
                    .then((response) => response.json())
                    .then((data) => {
                        if (data.error) {
                            alert(data.error);
                        } else {
                            // TODO add attachment to list in DOM
                            alert("Attachment added");
                        }
                        spinner.classList.remove('rotate');
                    });
            });
        });
    </script>
</head>

<body>
    <?php include("header.php"); ?>
    <main id="editLab">
        <nav class="back" title="Back">
            <a href="../../lab">
                <i class="fa-solid fa-arrow-left"></i>
            </a>
        </nav>
        <nav class="tools">
            <i id="addDeliverable" title="Add Deliverable" class="far fa-plus-square"></i>
            <form id="delLab" data-id="<?= $lab['id'] ?>" data-scount="<?= 0 /* TODO submit count */ ?>" action="del" method="POST"><i id="delBtn" title="Delete Lab" class="far fa-trash-alt"></i></form>
        </nav>
        <div id="content">
            <div class="quiz">
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
                        <label>Points:</label>
                        <input class="name" type="number" name="points" value="<?= $lab['points'] ?>" />
                    </div>
                    <div>
                        <label>Type:</label>
                        <select name="type">
                            <option value="Individual" <?= $lab['type'] == "Individual" ? "selected" : "" ?>>Individual</option>
                            <option value="Group" <?= $lab['type'] == "Group" ? "selected" : "" ?>>Group</option>
                        </select>
                    </div>
                    <h3 class="lab">Lab Description:</h3>
                    <div class="qcontainer">
                        <div class="textContainer">
                            <textarea name="desc" class="text" placeholder="Write your lab description here" data-md="Use **markdown** syntax in your text like:&#10;&#10;```javascript&#10;const code = &quot;highlighted&quot;&semi;&#10;```" data-txt="Write your lab description here"><?= $lab['desc'] ?></textarea>

                            <i title="Markdown" class="txt fa-brands fa-markdown <?= $lab['hasMarkDown'] ? "active" : "" ?>"></i>
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
                <h3 class="attachHeader">Attachments
                    <i id="attachBtn" title="Add attachment" class="fa-solid fa-paperclip"></i>
                    <input type="file" name="attachment" id="attachment" />
                    <i id="attachSpin" class="fa-solid fa-circle-notch"></i>
                </h3>
                <?php foreach ($attachments as $attachment) : ?>
                    <div class="attachment">
                        <a href="<?= $attachment['file'] ?>"><?= $attachment['name'] ?></a>
                        <i title="Remove Attachment" class="fa-solid fa-xmark"></i>
                    </div>
                <?php endforeach; ?>
            </div>

            <?php if (!$deliverables) : ?>
                <h2>No Deliverables Yet!</h2>
                <p class="warning">Click the <i class="far fa-plus-square"></i> button in the top right to add at least one deliverable.</p>
            <?php endif; ?>
            <?php foreach ($deliverables as $question) : ?>
                <div class="qcontainer">
                    <div class="about">
                        <div class="seq"><?= $question['seq'] ?></div>
                        <div class="points">Points: <input type="number" value="<?= $question['points'] ?>" /></div>
                        <form action="<?= "question/" . $question['id'] . "/del" ?>" method="POST">
                            <i class="far fa-trash-alt"></i>
                        </form>
                    </div>
                    <div class="question" data-id="<?= $question['id'] ?>">
                        <div class="qType" data-type="<?= $question['type'] ?>">
                            Type:
                            <?php if ($question['type'] == "text") : ?>
                                Text
                            <?php elseif ($question['type'] == "image") : ?>
                                Image Upload
                            <?php endif; ?>
                        </div>
                        <div>Question Text:</div>
                        <div class="textContainer">
                            <textarea class="text" placeholder="Write your question text here" data-md="Use **markdown** syntax in your text like:&#10;&#10;```javascript&#10;const code = &quot;highlighted&quot;&semi;&#10;```" data-txt="Write your question text here"><?= $question['text'] ?></textarea>

                            <i title="Markdown" class="txt fa-brands fa-markdown <?= $question['hasMarkDown'] ? "active" : "" ?>"></i>
                            <div class="mdContainer <?= $question['hasMarkDown'] ? "active" : "" ?>">
                                <div class="preview"><button class="previewBtn">Preview Markdown</button></div>
                                <div class="previewArea"></div>
                            </div>
                        </div>
                        <div>Model Answer:</div>
                        <?php if ($question['type'] == 'text') : ?>
                            <div class="textContainer">
                                <textarea class="model_answer" placeholder="Write your model answer here" data-md="Use **markdown** syntax in your text like:&#10;&#10;```javascript&#10;const code = &quot;highlighted&quot;&semi;&#10;```" data-txt="Write your model answer here"><?= $question['modelAnswer'] ?></textarea>

                                <i title="Markdown" class="mdl fa-brands fa-markdown <?= $question['mdlAnsHasMD'] ? "active" : "" ?>"></i>
                                <div class="mdContainer <?= $question['mdlAnsHasMD'] ? "active" : "" ?>">
                                    <div class="preview"><button class="previewBtn">Preview Markdown</button></div>
                                    <div class="previewArea"></div>
                                </div>
                            </div>
                        <?php elseif ($question['type'] == "image") : ?>
                            <img src="<?= $question['modelAnswer'] ?>" />
                            <div>
                                Upload Replacement:
                                <input type="file" class="img_replace" />
                                <i class="fa-solid fa-circle-notch"></i>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </main>
    <dialog id="addDeliverable" class="modal">
        <i id="close-overlay" class="fas fa-times-circle"></i>
        <h3>Add Deliverable</h3>
        <div class="btn">
            <button>Add</button>
        </div>
    </dialog>
</body>

</html>
