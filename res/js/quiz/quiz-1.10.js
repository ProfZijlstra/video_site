window.addEventListener("load", () => {
    // data needed by multiple functions
    const quiz_id = document.getElementById('quiz_id').dataset.id;

    // timer code
    COUNTDOWN.start(() => window.location.reload());

    // enable markdown previews
    MARKDOWN.enablePreview("../markdown", false, true);
    MARKDOWN.activateButtons(saveQuestionChange)

    // user_id if present
    const user_id = document.getElementById("user_id")?.value;

    // automatically save changes to answers
    function saveQuestionChange() {
        let parent = this.closest('div.answer');
        const text = parent.querySelector("textarea.answer");

        const qid = parent.previousElementSibling.dataset.id;
        const aid = text.dataset.id;
        const shifted = MARKDOWN.ceasarShift(text.value);
        const answer = encodeURIComponent(shifted);
        const hasMD = text.parentNode.querySelector("i")
            .classList.contains("active") ? 1 : 0;

        let url = `../${quiz_id}/question/${qid}/text`;
        if (user_id) {
            url += `../?student=${user_id}`;
        }
        fetch(url, {
            method: "POST",
            body: `answer=${answer}&answer_id=${aid}&hasMarkDown=${hasMD}`,
            headers:
                { 'Content-Type': 'application/x-www-form-urlencoded' },
        })
            .then((response) => response.json())
            .then((data) => {
                text.dataset.id = data.answer_id;
            });
    }
    const areas = document.querySelectorAll('.qcontainer textarea');
    for (const area of areas) {
        area.onchange = saveQuestionChange;
    }

    // enable image question image uploads
    function uploadImage() {
        const parent = this.closest("div.answer");
        const img = parent.querySelector('img.answer');
        // if there already is an image, get the answer_id from it
        let aid = false;
        if (!img.classList.contains('hide')) {
            aid = img.dataset.id;
        }
        const qid = parent.previousElementSibling.dataset.id;
        const spinner = parent.querySelector('i.fa-circle-notch');
        const anchor = parent.querySelector('a');
        spinner.classList.add('rotate');
        const data = new FormData();
        data.append("answer_id", aid);
        data.append("image", this.files[0]);

        let url = `../${quiz_id}/question/${qid}/image`;
        if (user_id) {
            url += `?student=${user_id}`;
        }
        fetch(url, {
            method: "POST",
            body: data
        })
            .then((response) => response.json())
            .then((data) => {
                if (data.error) {
                    alert(data.error);
                } else {
                    img.src = data.dst;
                    img.dataset.id = data.answer_id;
                    img.classList.remove('hide');
                    anchor.href = data.dst;
                    const name = data.dst.split('/').pop();
                    anchor.innerText = name;
                    const trash = this.parentNode.querySelector("i.fa-trash-can");
                    trash.dataset.id = data.answer_id;
                    trash.classList.remove("hide");
                }
                spinner.classList.remove('rotate');
            });

    }
    const files = document.querySelectorAll("div.answer input[type=file]");
    for (const file of files) {
        file.onchange = uploadImage;
    }
    const uploads = document.querySelectorAll("i.fa-upload");
    for (const upload of uploads) {
        upload.onclick = () => {
            upload.parentNode.querySelector("input[type=file]").click();
        }
    }

    // Hook up the camera functions
    CAMERA.init(`${quiz_id}/question`);

    // make back button also send 'finish' signal
    document.getElementById('back').onclick = function() {
        document.getElementById('finish').click();
    }

    document.forms['finishQuiz'].onsubmit = function(evt) {
        evt.preventDefault();
        setTimeout(() => this.submit(), 500);
    };

    // Hook up the delete functions
    document.querySelectorAll("i.fa-trash-can").forEach(trash => {
        trash.addEventListener("click", deleteFile);
    });
    function deleteFile() {
        const id = this.dataset.id;
        let url = `../${quiz_id}/delivery/${id}`;
        fetch(url, {
            method: "DELETE",
        })
            .then(response => {
                if (!response.ok) {
                    alert("Deleting file failed.");
                }
                return response.json();
            })
            .then(data => {
                if (data.error) {
                    alert(data.error);
                    return;
                }
                // remove the file from the DOM
                const parent = this.closest("div.camContainer");
                const link = parent.querySelector("a.fileLink");
                link.removeAttribute("href");
                link.textContent = "";
                this.classList.add("hide");

                // for images, remove the image
                const img = parent.querySelector("img.answer");
                if (img) {
                    img.removeAttribute("src");
                    img.classList.remove("show");
                    img.classList.add("hide");
                    img.dataset.id = "";
                }
            });
    }
});
