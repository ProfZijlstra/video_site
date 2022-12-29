window.addEventListener("load", () => {    
    // timer code
    COUNTDOWN.start(() => window.location.refresh());

    // enable markdown previews
    MARKDOWN.enablePreview("markdown");

    // automatically save changes to answers
    function saveQuestionChange() {
        const textarea = this;
        const parent = this.parentNode;
        const qid = parent.dataset.id;
        const text = parent.querySelector('textarea.answer');
        const answer = encodeURIComponent(text.value);
        const aid = text.dataset.id;
        const quiz_id = document.getElementById('quiz_id').dataset.id;

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

    // make back button work
    document.getElementById('back').onclick = function() {
        document.getElementById('finish').click();
    }
});            