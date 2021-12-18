window.addEventListener("load", () => {
    const overlay = document.getElementById("overlay");
    document.getElementById("upload").addEventListener("click", () => {
        overlay.classList.add("visible");
    });
    function hide() {
        overlay.classList.remove("visible");
    }
    document.getElementById("close-overlay").onclick = hide;

    document.getElementById("overlay").onclick = function (evt) {
        if (evt.target == this) {
            hide();
        }
    };

    document.getElementById("upload_form").onsubmit = () => {
        const file = document.getElementById("list_file");
        if (!file.value) {
            return false;
        }
    };
});
