window.addEventListener("load", () => {    
    // automatically save changes to comments or points
    function saveGrading() {
        const tr = this.parentNode.parentNode;
        const points = tr.querySelector('input.points').value;
        const answer_ids = tr.querySelector('input.answer_ids').value;
        const text = tr.querySelector('textarea.comment').value;
        const shifted = MARKDOWN.ceasarShift(text);
        const comment = encodeURIComponent(shifted);

        fetch(`grade`, {
            method : "POST",
            body : `comment=${comment}&points=${points}&answer_ids=${answer_ids}`,
            headers :
                {'Content-Type' : 'application/x-www-form-urlencoded'},
        });
    }
    const areas = document.querySelectorAll('table textarea.comment');
    for (const area of areas) {
        area.onchange = saveGrading;
    }
    const inputs = document.querySelectorAll('table input.points');
    for (const input of inputs) {
        input.onchange = saveGrading;
    }

    // start focus on first comment textarea
    const start = document.querySelector('textarea.comment');
    if (start) {
        start.focus();
    } else {
        document.getElementById('finish').focus();
    }

    function ensureSaveSent(evt) {
        evt.preventDefault();
        evt.stopImmediatePropagation();
        const href = this.getAttribute("href");
        setTimeout(() => window.location.href = href, 500);
    }
    for (const a of document.links) {
        a.onclick = ensureSaveSent;
    }
});            
