window.addEventListener("load", () => {   
    // hookup markdown previews
    MARKDOWN.enablePreview("../../../markdown");

    // hookup comment and point submission 
    const user_id = document.getElementById('user').dataset.user_id;
    function saveGrading() {
        const qc = this.parentNode.parentNode;
        const commentArea = qc.querySelector('textarea.comment');
        const shifted = MARKDOWN.ceasarShift(commentArea.value);
        const comment = encodeURIComponent(shifted);
        const points = qc.querySelector('input.points').value;
        const question_id = qc.querySelector('div.question').dataset.id
        const answer_id = commentArea.dataset.id;

        fetch(`grade`, {
            method : "POST",
            body : `comment=${comment}&points=${points}&answer_id=${answer_id}&question_id=${question_id}&user_id=${user_id}`,
            headers :
                {'Content-Type' : 'application/x-www-form-urlencoded'},
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
