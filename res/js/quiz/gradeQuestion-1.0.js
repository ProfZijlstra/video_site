window.addEventListener("load", () => {    
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
        const comment = encodeURIComponent(shifted);

        fetch(`grade`, {
            method : "POST",
            body : `comment=${comment}&points=${points}&answer_ids=${answer_ids}`,
            headers :
                {'Content-Type' : 'application/x-www-form-urlencoded'},
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
