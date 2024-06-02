window.addEventListener("load", () => {
    // most of this code is directly copied from gradeSubmission.js
    // should we pull it out into a common file?
    function gradeDeliverable() {
        const container = this.closest("tr");
        const input = container.querySelector("input");
        if (!input.checkValidity()) {
            alert("Points have an invalid value (beyond max or below zero).");
            input.value = input.dataset.value;
            return;
        }
        let points = input.value;
        if (!points) {
            points = 0;
        }
        const hasMarkDown = 0;
        const comment = container.querySelector("textarea").value;
        const shifted = encodeURIComponent(MARKDOWN.ceasarShift(comment));
        const delivery_id = container.dataset.id;

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
});
