window.addEventListener("load", () => {
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