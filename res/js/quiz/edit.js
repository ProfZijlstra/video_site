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
        const name = form.querySelector("input.name").value;
        const startdate = form.querySelector("input.startdate").value;
        const starttime = form.querySelector("input.starttime").value;
        const stopdate = form.querySelector("input.stopdate").value;
        const stoptime = form.querySelector("input.stoptime").value;

        fetch(`../${id}`, {
            method : "POST",
            body : `name=${name}&startdate=${startdate}&starttime=${starttime}&stopdate=${stopdate}&stoptime=${stoptime}`,
            headers :
                {'Content-Type' : 'application/x-www-form-urlencoded'},
        });
    }
    const fields = document.querySelectorAll("#updateQuiz input");
    for (const field of fields) {
        field.onchange = updateDetails;
    }

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
    MARKDOWN.enablePreview("../markdown");

    // automatically save changes to text and answer
    function saveQuestionChange() {
        const parent = this.parentNode;
        const id = parent.dataset.id;
        const text = parent.querySelector('textarea.text').value;
        const model_answer = parent.querySelector('textarea.model_answer').value;
        const prev = parent.previousElementSibling;
        const points = prev.querySelector(".points input").value;

        fetch(`question/${id}`, {
            method : "POST",
            body : `text=${text}&model_answer=${model_answer}&points=${points}`,
            headers :
                {'Content-Type' : 'application/x-www-form-urlencoded'},
        });
    }
    const areas = document.querySelectorAll('.qcontainer textarea');
    for (const area of areas) {
        area.onchange = saveQuestionChange;
    }
    function savePointsChange() {
        const points = this.value;
        const parent = this.parentNode.parentNode;
        const next = parent.nextElementSibling;
        const id = next.dataset.id;
        const text = next.querySelector('textarea.text').value;
        const model_answer = next.querySelector('textarea.model_answer').value;

        fetch(`question/${id}`, {
            method : "POST",
            body : `text=${text}&model_answer=${model_answer}&points=${points}`,
            headers :
                {'Content-Type' : 'application/x-www-form-urlencoded'},
        });
    }
    const inputs = document.querySelectorAll(".about input");
    for (const input of inputs) {
        input.onchange = savePointsChange;
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
