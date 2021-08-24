window.addEventListener("load", () => {
    'use strict';
    document.getElementById("days").onclick = function (e) {
        if (e.target.tagName == "TD") {
            e.target.querySelector("a").click();
        }
    }
});
