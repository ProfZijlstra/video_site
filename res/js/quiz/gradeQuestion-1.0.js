window.addEventListener("load", () => {    
    // enable markdown previews
    MARKDOWN.enablePreview("../../../markdown", true);
    MARKDOWN.activateButtons(saveGrading);

    // automatically save changes to comments or points
    function saveGrading() {
        const parent = this.parentElement;;
        let pointsDiv = parent;
        let commentDiv = parent;
        if (parent.classList.contains('points')) { 
            commentDiv = parent.previousElementSibling;
        } else if (parent.classList.contains('comment')) {
            pointsDiv = parent.nextElementSibling;
        } else {
            alert('Error: saveGrading called with invalid element');
        }

        const usersDiv = commentDiv.previousElementSibling.previousElementSibling;
        const answer_ids = usersDiv.querySelector('input.answer_ids').value;
        const text = commentDiv.querySelector('textarea.comment').value;
        const input = pointsDiv.querySelector('input.points');
        if (!input.checkValidity()) {
            const ans = confirm("Did you intend to go beyond max or below zero?");
            if (!ans) {
                input.value = input.dataset.value;
                setTimeout(() => input.focus(), 100);
                return;
            }
        }
        const points = input.value ? input.value : 0;
        const shifted = MARKDOWN.ceasarShift(text);
        const cmntHasMd = commentDiv.querySelector('i.fa-markdown').classList.contains('active') ? 1 : 0;

        const data = new FormData();
        data.append("comment", shifted);
        data.append("points", points);
        data.append("answer_ids", answer_ids);
        data.append("cmntHasMD", cmntHasMd);

        fetch(`grade`, {
            method : "POST",
            body : data
        });
    }
    function nextPrevPoints(evt) {
        let t = evt.target.parentElement;
        if (evt.key == "n" || evt.key == "N") {
            evt.preventDefault();
            for (let i = 0; i < 4; i++) {
                t = t?.nextElementSibling;
            }
            const next = t?.querySelector("input.points");
            if (next) {
                next.focus();
                next.scrollIntoView(true);
            }
        }
        else if (evt.key == "p" || evt.key == "P") {
            evt.preventDefault();
            for (let i = 0; i < 4; i++) {
                t = t?.previousElementSibling;
            }
            const prev = t?.querySelector("input.points");
            if (prev) {
                prev.focus();
                prev.scrollIntoView(true);
            }
        }
    }

    const areas = document.querySelectorAll('textarea.comment');
    for (const area of areas) {
        area.onchange = saveGrading;
    }
    const inputs = document.querySelectorAll('input.points');
    for (const input of inputs) {
        input.onchange = saveGrading;
        input.addEventListener("keydown", nextPrevPoints);
    }
});            
