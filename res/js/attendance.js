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
        const day_id = this.parentNode.dataset.day_id;;
        document.getElementById("day_id").value = day_id;
        overlay.classList.add("visible");
    }
    const addBtns = document.getElementsByClassName("fa-plus-square");
    for (const btn of addBtns) {
        btn.onclick = addMeeting;
    }
});