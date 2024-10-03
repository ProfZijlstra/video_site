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
        const input = qc.querySelector('input.points');
        if (!input.checkValidity()) {
            const ans = confirm("Did you intend to go beyond max or below zero?");
            if (!ans) {
                input.value = input.dataset.value;
                setTimeout(() => input.focus(), 100);
                return false;
            }
        }
        const points = input.value ? input.value : 0;
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
    function nextPrevPoints(evt) {
        let t = evt.target.parentElement.parentElement;
        if (evt.key == "n" || evt.key == "N") {
            evt.preventDefault();
            t = t.nextElementSibling;
            t?.querySelector("input.points")?.focus();
        } else if (evt.key == "p" || evt.key == "P") {
            evt.preventDefault();
            t = t.previousElementSibling;
            t?.querySelector("input.points")?.focus();
        }
    }
    const areas = document.querySelectorAll('div.qcontainer textarea.comment');
    for (const area of areas) {
        area.onchange = saveGrading;
    }
    const inputs = document.querySelectorAll('div.qcontainer input.points');
    for (const input of inputs) {
        input.onchange = saveGrading;
        input.addEventListener("keydown", nextPrevPoints);
    }

});
