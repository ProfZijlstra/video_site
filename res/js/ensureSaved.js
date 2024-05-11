window.addEventListener("load", () => {
    function ensureSaveSent(evt) {
        evt.preventDefault();
        evt.stopImmediatePropagation();
        this.removeEventListener("click", ensureSaveSent);
        setTimeout(() => this.click(), 500);
    }
    for (const a of document.links) {
        a.addEventListener("click", ensureSaveSent);
    }
});
