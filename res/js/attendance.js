window.addEventListener("load", () => {
    const overlay = document.getElementById("overlay");

    // everything for hiding the overloay
    function hide() {
        overlay.classList.remove("visible");
    }
    document.getElementById("close-overlay").onclick = hide;
    document.getElementById("overlay").onclick = function (evt) {
        if (evt.target == this) {
            hide();
        }
    };

    // showing the overlay
    function addMeeting() {
        const session_id = this.parentNode.dataset.session_id;;
        document.getElementById("session_id").value = session_id;
        overlay.classList.add("visible");
    }
    const addBtns = document.getElementsByClassName("fa-plus-square");
    for (const btn of addBtns) {
        btn.onclick = addMeeting;
    }
});