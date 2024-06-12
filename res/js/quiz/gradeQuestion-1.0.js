window.addEventListener("load", () => {    
    // enable markdown previews
    MARKDOWN.enablePreview("../../../markdown", hidePreview);
    MARKDOWN.activateButtons(saveGrading);

    function hidePreview() {
        const mdContainer = this.closest('.mdContainer');
        mdContainer.classList.remove('active');
    }

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
        let points = pointsDiv.querySelector('input.points').value;;
        points = points ? points : 0;
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
    const areas = document.querySelectorAll('textarea.comment');
    for (const area of areas) {
        area.onchange = saveGrading;
    }
    const inputs = document.querySelectorAll('input.points');
    for (const input of inputs) {
        input.onchange = saveGrading;
    }
});            
