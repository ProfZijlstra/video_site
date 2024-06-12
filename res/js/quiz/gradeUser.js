window.addEventListener("load", () => {   
    // hookup markdown previews
    MARKDOWN.enablePreview("../../../markdown");
    MARKDOWN.activateButtons(saveGrading);

    // hookup comment and point submission 
    const user_id = document.getElementById('user').dataset.user_id;
    function saveGrading() {
        const qc = this.closest('.qcontainer');
        const commentArea = qc.querySelector('textarea.comment');
        const shifted = MARKDOWN.ceasarShift(commentArea.value);
        const points = qc.querySelector('input.points').value;
        const question_id = qc.querySelector('div.question').dataset.id
        const answer_id = commentArea.dataset.id;
        const cmntHasMd = qc.querySelector('i.fa-markdown').classList.contains('active') ? 1 : 0;

        const data = new FormData();
        data.append("comment", shifted);
        data.append("points", points);
        data.append("answer_id", answer_id);
        data.append("question_id", question_id);
        data.append("user_id", user_id);
        data.append("cmntHasMD", cmntHasMd);

        fetch(`grade`, {
            method : "POST",
            body : data
        })
        .then((response) => response.json())
        .then((data) => {
            commentArea.dataset.id = data.answer_id;
        });

    }
    const areas = document.querySelectorAll('div.qcontainer textarea.comment');
    for (const area of areas) {
        area.onchange = saveGrading;
    }
    const inputs = document.querySelectorAll('div.qcontainer input.points');
    for (const input of inputs) {
        input.onchange = saveGrading;
    }
});
