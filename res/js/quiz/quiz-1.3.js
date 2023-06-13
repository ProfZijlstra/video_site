window.addEventListener("load", () => {    
    // data needed by multiple functions
    const quiz_id = document.getElementById('quiz_id').dataset.id;

    // timer code
    COUNTDOWN.start(() => window.location.reload());

    // enable markdown previews
    MARKDOWN.enablePreview("../markdown");

    // automatically save changes to answers
    function saveQuestionChange() {
        const textarea = this;
        const parent = this.parentNode;
        const qid = parent.dataset.id;
        const text = parent.querySelector('textarea.answer');
        const aid = text.dataset.id;
        const shifted = MARKDOWN.ceasarShift(text.value);
        const answer = encodeURIComponent(shifted);

        fetch(`${quiz_id}/question/${qid}/markdown`, {
            method : "POST",
            body : `answer=${answer}&answer_id=${aid}`,
            headers :
                {'Content-Type' : 'application/x-www-form-urlencoded'},
        })
        .then((response) => response.json())
        .then((data) => {
            textarea.dataset.id = data.answer_id;
        });
    }
    const areas = document.querySelectorAll('.qcontainer textarea');
    for (const area of areas) {
        area.onchange = saveQuestionChange;
    }

    // enable image question image uploads
    function uploadImage() {
        const img = this.parentNode.parentNode.querySelector('img');
        // if there already is an image, get the answer_id from it
        let aid = false;
        if (!img.classList.contains('hide')) {
            aid = img.dataset.id;
        }
        const qid = this.parentNode.parentNode.dataset.id;
        const spinner = this.parentNode.querySelector('i.fa-circle-notch');
        const label = this.parentNode.querySelector('label');
        spinner.classList.add('rotate');
        const data = new FormData();
        data.append("answer_id", aid);
        data.append("image", this.files[0]);

        fetch(`${quiz_id}/question/${qid}/image`, {
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
                label.innerText = "Upload Replacement";
            }
            spinner.classList.remove('rotate');    
        });

    }
    const files = document.querySelectorAll("div.question input[type=file]");
    for (const file of files) {
        file.onchange = uploadImage;
    }
    

    // make back button work
    document.getElementById('back').onclick = function() {
        document.getElementById('finish').click();
    }
});            