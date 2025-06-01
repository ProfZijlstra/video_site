window.addEventListener("load", () => {
    document.getElementById("addQuestion").onmousedown = () => {
        document.getElementById("addQuestionDialog").showModal();
    };
    document.getElementById("closeAddDialog").onmousedown = () => {
        document.getElementById("addQuestionDialog").close();
    };
    document.getElementById("addQuestBtn").onmousedown = function() {
        const data = new FormData();
        data.append("type", document.getElementById("questionType").value);
        data.append("seq", this.dataset.seq);
        data.append("quiz_id", this.dataset.quiz_id);
        this.dataset.seq++;
        fetch("question", {
            method: "POST",
            body: data,
        })
            .then((response) => {
                if (response.ok) {
                    return response.text();
                } else {
                    throw new Error("Adding question failed");
                }
            })
            .then((html) => {
                const noqs = document.getElementById("noQuestions");
                if (noqs) {
                    noqs.classList.add("hide");
                    noqs.previousElementSibling.classList.remove("empty");
                }
                const div = document.createElement("div");
                div.innerHTML = html;

                // hook up JS
                const trash = div.querySelector("i.fa-trash-alt");
                trash.onmousedown = deleteQuestion;
                const txtas = div.querySelectorAll("textarea");
                txtas.forEach(e => {
                    e.onchange = saveQuestionChange;
                    e.addEventListener('keydown', MARKDOWN.keyEventHandler);
                    e.dataset.initialHeight = "120";
                    e.addEventListener('keydown', MARKDOWN.autoExpand);
                });
                const mds = div.querySelectorAll("i.fa-markdown");
                mds.forEach(e => e.onmousedown = MARKDOWN.toggleMarkDown);
                const previews = div.querySelectorAll("button.previewBtn");
                previews.forEach(e => e.onmousedown = MARKDOWN.getHtmlForMarkdown);
                const cameras = div.querySelectorAll("i.fa-camera");
                cameras.forEach(e => e.onmousedown = CAMERA.openCamera);
                const switches = div.querySelectorAll("div.switchCamera");
                switches.forEach(e => e.onmousedown = CAMERA.switchCamera);
                const closes = div.querySelectorAll("div.closeCamera");
                closes.forEach(e => e.onmousedown = CAMERA.closeCamera);
                const takes = div.querySelectorAll("div.takePicture");
                takes.forEach(e => e.onmousedown = CAMERA.takePicture);


                const quests = document.getElementById("questions");
                quests.appendChild(div);
                document.getElementById("addQuestionDialog").close();
                window.scrollTo(0, document.body.scrollHeight);

                // focus the (top) textarea
                div.querySelector("textarea").focus();
            })
            .catch((error) => {
                document.getElementById("addQuestionDialog").close();
                alert(error);
            });
    };

    // change quiz status when a checkbox is clicked
    document.getElementById("visible").onchange = function updateStatus() {
        this.value = this.value == 0 ? 1 : 0;
        const parent = this.parentNode.parentNode;
        const id = parent.dataset.id;
        const visible = parent.querySelector('.visible').value;

        fetch(`status`, {
            method: "POST",
            body: `visible=${visible}`,
            headers:
                { 'Content-Type': 'application/x-www-form-urlencoded' },
        });
    }

    // update quiz details on change
    function updateDetails() {
        const form = document.getElementById("updateQuiz");
        const id = form.dataset.id;
        const day_id = form.querySelector("select").value;
        const name = encodeURIComponent(form.querySelector("input.name").value);
        const startdate = form.querySelector("input.startdate").value;
        const starttime = form.querySelector("input.starttime").value;
        const stopdate = form.querySelector("input.stopdate").value;
        const stoptime = form.querySelector("input.stoptime").value;

        fetch(`../${id}`, {
            method: "POST",
            body: `day_id=${day_id}&name=${name}&startdate=${startdate}&starttime=${starttime}&stopdate=${stopdate}&stoptime=${stoptime}`,
            headers:
                { 'Content-Type': 'application/x-www-form-urlencoded' },
        });
    }
    const fields = document.querySelectorAll("#updateQuiz input");
    for (const field of fields) {
        field.onchange = updateDetails;
    }
    document.getElementById("day_id").onchange = updateDetails;

    // delete quiz
    function deleteQuiz() {
        const form = document.getElementById("delQuiz");
        const qcount = form.dataset.qcount;
        if (qcount > 0) {
            alert("Can only delete Quiz without Questions. Remove Questions First");
            return;
        }

        if (confirm("Really Delete this Quiz?")) {
            form.submit();
        }
    }
    document.getElementById("delBtn").onclick = deleteQuiz;

    // enable markdown previews
    MARKDOWN.enablePreview("../../markdown");
    MARKDOWN.activateButtons(saveQuestionChange);

    // enable taking pictures
    CAMERA.init('question/modelAns', false);

    // automatically save changes to points, text and model answer
    function saveQuestionChange() {
        const parent = this.closest('.qcontainer')
        const id = parent.querySelector('.question').dataset.id;
        const type = parent.querySelector('.qType').dataset.type;
        const text = parent.querySelector('textarea.text').value;
        const points = parent.querySelector(".points input").value;
        const qshifted = MARKDOWN.ceasarShift(text);
        const txtMdBtn = parent.querySelector("i.fa-markdown.txt");
        const hasMarkdown = txtMdBtn.classList.contains('active') ? 1 : 0;
        const data = new FormData();
        data.set("type", type);
        data.set("text", qshifted);
        data.set("points", points);
        data.set("hasMarkDown", hasMarkdown);

        if (type == "text") {
            const model_answer = parent.querySelector('.model_answer').value;
            const ashifted = MARKDOWN.ceasarShift(model_answer);
            const ansMdBtn = parent.querySelector("i.fa-markdown.mdl");
            const mdlAnsHasMD = ansMdBtn.classList.contains('active') ? 1 : 0;
            data.set("model_answer", ashifted);
            data.set("mdlAnsHasMD", mdlAnsHasMD);
        } else if (type == "image") {
            const src = parent.querySelector("img").getAttribute('src');
            data.set("model_answer", src);
        }

        fetch(`question/${id}`, {
            method: "POST",
            body: data,
        });
    }
    const areas = document.querySelectorAll('.qcontainer textarea');
    for (const area of areas) {
        area.onchange = saveQuestionChange;
    }
    const inputs = document.querySelectorAll(".about input");
    for (const input of inputs) {
        input.onchange = saveQuestionChange;
    }

    // enable image replacement
    function uploadReplacement() {
        const parent = this.closest("div.qcontainer");
        const img = parent.querySelector('.answer img');
        const qid = parent.querySelector(".question").dataset.id;
        const spinner = parent.querySelector('i.fa-circle-notch');
        spinner.classList.add('rotate');
        const data = new FormData();
        data.append("image", this.files[0]);

        fetch(`question/${qid}/modelAnswerImage`, {
            method: "POST",
            body: data
        })
            .then((response) => response.json())
            .then((data) => {
                if (data.error) {
                    alert(data.error);
                } else {
                    img.src = data.dst;
                }
                spinner.classList.remove('rotate');
            });

    }
    const files = document.querySelectorAll("div.qcontainer input[type=file]");
    for (const file of files) {
        file.onchange = uploadReplacement;
    }
    function clickUploadBtn() {
        const parent = this.closest("div");
        const file = parent.querySelector("input");
        file.click();
    }
    const uploadBtns = document.querySelectorAll("i.fa-upload");
    for (const btn of uploadBtns) {
        btn.onmousedown = clickUploadBtn;
    }


    // enable delete question
    function deleteQuestion() {
        const form = this.parentNode;
        if (confirm("Delete this Question?")) {
            form.submit();
        }
    }
    const dels = document.querySelectorAll(".about i.fa-trash-alt");
    for (const del of dels) {
        del.onmousedown = deleteQuestion;
    }
});
