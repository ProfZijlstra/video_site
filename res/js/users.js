window.addEventListener("load", () => {
    document.querySelector("table").addEventListener("click", (evt) => {
        const target = evt.target;
        const link = target.parentNode.querySelector("a");
        if (link) {
            link.click();
        }
    });
});
