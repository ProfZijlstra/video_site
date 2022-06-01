window.addEventListener("load", () => {
    'use strict';
    document.getElementById("days").onclick = function(e) {
        if (e.target.classList.contains("data")) {
            e.target.querySelector("a").click();
        }
    }
});
