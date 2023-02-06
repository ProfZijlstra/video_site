window.addEventListener("load", () => {    
    // hide overlay and any/all modal(s)
    function hide() {
        overlay.classList.remove("visible");
        const modals = document.querySelectorAll(".modal");
        for (const modal of modals) {
            modal.classList.add("hide");
        }
    }
    document.getElementById("close-overlay").onclick = hide;
    document.getElementById("overlay").onclick = function (evt) {
        if (evt.target == this) {
            hide();
        }
    };

    // show add question modal
    document.getElementById('addQuestion').onclick = function() {
        overlay.classList.add("visible");
        document.getElementById("add_question_modal").classList.remove("hide");
        document.getElementById("addQuestionText").focus();
    };

    // make question type select work
    const md_answer = document.getElementById("md_answer");
    const img_answer = document.getElementById("img_answer");
    const add_form = document.getElementById('add_form');
    document.querySelector('#typeSelect select').onchange = function() {
        add_form.elements.type.value = this.value;
        if (this.value == "image") {
            md_answer.style.display = "none";
            img_answer.style.display = "block"
        } else {
            img_answer.style.display = "none"
            md_answer.style.display = "block";
        }
    };

    // ceasar shift question text and model answer text when on add submit
    document.getElementById('add_form').onsubmit = function() {
        const qtext = this.elements.text;
        if (qtext.value == "") {
            return false;
        } 
        const atext = this.elements.model_answer;
        const qshifted = MARKDOWN.ceasarShift(qtext.value);
        const ashifted = MARKDOWN.ceasarShift(atext.value);
        qtext.value = qshifted;
        atext.value = ashifted;
    }

    // change quiz status when a checkbox is clicked
    document.getElementById("visible").onchange = function updateStatus() {
        this.value = this.value == 0 ? 1 : 0;
        const parent = this.parentNode.parentNode;
        const id = parent.dataset.id;
        const visible = parent.querySelector('.visible').value;

        fetch(`status`, {
            method : "POST",
            body : `visible=${visible}`,
            headers :
                {'Content-Type' : 'application/x-www-form-urlencoded'},
        });
    }

    // update quiz details on change
    function updateDetails() {
        const form = document.getElementById("updateQuiz");
        const id = form.dataset.id;
        const day_id = form.querySelector("select").value;
        const name = form.querySelector("input.name").value;
        const startdate = form.querySelector("input.startdate").value;
        const starttime = form.querySelector("input.starttime").value;
        const stopdate = form.querySelector("input.stopdate").value;
        const stoptime = form.querySelector("input.stoptime").value;

        fetch(`../${id}`, {
            method : "POST",
            body : `day_id=${day_id}&name=${name}&startdate=${startdate}&starttime=${starttime}&stopdate=${stopdate}&stoptime=${stoptime}`,
            headers :
                {'Content-Type' : 'application/x-www-form-urlencoded'},
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

    // automatically save changes to points, text and model answer
    function saveQuestionChange() {
        let parent = this.parentNode;
        while (!parent.classList.contains('qcontainer')) {
            parent = parent.parentNode;
        }

        const id = parent.querySelector('.question').dataset.id;
        const type = parent.querySelector('.qType').dataset.type;
        const text = parent.querySelector('textarea.text').value;
        const points = parent.querySelector(".points input").value;
        const qshifted = MARKDOWN.ceasarShift(text);

        let body = `type=${type}&text=${qshifted}&points=${points}`;
        if (type == "markdown") {
            const model_answer = parent.querySelector('textarea.model_answer').value;
            const ashifted = MARKDOWN.ceasarShift(model_answer);
            body += `&model_answer=${ashifted}`;
        } else if (type == "image") {
            const src = parent.querySelector("img").getAttribute('src');
            body += `&model_answer=${src}`;
        }

        fetch(`question/${id}`, {
            method : "POST",
            body : body,
            headers :
                {'Content-Type' : 'application/x-www-form-urlencoded'},
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
        const img = this.parentNode.parentNode.querySelector('img');
        const qid = this.parentNode.parentNode.dataset.id;
        const spinner = this.parentNode.querySelector('i.fa-circle-notch');
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
    const files = document.querySelectorAll("div.question input[type=file]");
    for (const file of files) {
        file.onchange = uploadReplacement;
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
        del.onclick = deleteQuestion;
    }
});
