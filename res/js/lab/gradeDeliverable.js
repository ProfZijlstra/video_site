window.addEventListener("load", () => {
    // enable markdown previews
    MARKDOWN.enablePreview("../../../markdown", true);
    MARKDOWN.activateButtons(gradeDeliverable);

    function gradeDeliverable() {
        const parent = this.parentElement;
        let pointsDiv = parent;
        let commentDiv = parent;
        if (parent.classList.contains('points')) {
            commentDiv = parent.previousElementSibling;
        } else if (parent.classList.contains('comment')) {
            pointsDiv = parent.nextElementSibling;
        } else {
            alert('Error: gradeDeliverable called with invalid element');
        }

        const input = pointsDiv.querySelector("input");
        if (!input.checkValidity()) {
            alert("Points have an invalid value (beyond max or below zero).");
            input.value = input.dataset.value;
            setTimeout(() => input.focus(), 100);
            return false;
        }
        const points = input.value ? input.value : 0;
        const mdBtn = commentDiv.querySelector("i.fa-markdown");
        const hasMarkDown = mdBtn.classList.contains("active") ? 1 : 0;
        const comment = commentDiv.querySelector("textarea").value;
        const shifted = MARKDOWN.ceasarShift(comment);
        const delivery_id = commentDiv.dataset.delivery_id;

        const data = new URLSearchParams();
        data.append("delivery_id", delivery_id);
        data.append("points", points);
        data.append("hasMarkDown", hasMarkDown);
        data.append("comment", shifted);

        fetch(`../delivery/${delivery_id}`, {
                method: "PUT",
                body: data,
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error("Grading delivery failed.");
                }
            })
            .catch(error => {
                alert(error);
            });
    }
    document.querySelectorAll("textarea, input").forEach(input => {
        input.addEventListener("change", gradeDeliverable);
    });

    // have n / p keys move focus to next / previous points input
    function nextPrevPoints(evt) {
        let t = evt.target.parentElement;
        if (evt.key == "n" || evt.key == "N") {
            evt.preventDefault();
            for (let i = 0; i < 4; i++) {
                t = t?.nextElementSibling;
            }
            t?.querySelector("input.points")?.focus();
        }
        else if (evt.key == "p" || evt.key == "P") {
            evt.preventDefault();
            for (let i = 0; i < 4; i++) {
                t = t?.previousElementSibling;
            }
            t?.querySelector("input.points")?.focus();
        }
    }
    document.querySelectorAll('input.points').forEach(input => {
        input.addEventListener("keydown", nextPrevPoints);
    });
});
