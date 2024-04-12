window.addEventListener("load", () => {
    const submission = document.querySelector("h2");
    let submission_id = submission.dataset.id;
    const group = submission.dataset.group;
    const user_id = submission.dataset.user;

    function gradeForNotDelivered(container, points, hasMarkDown, comment) {
        const deliverable_id = container.dataset.deliverable;
        const data = new URLSearchParams();
        data.append("submission_id", submission_id);
        data.append("points", points);
        data.append("hasMarkDown", hasMarkDown);
        data.append("comment", comment);
        data.append("group", group);
        data.append("user_id", user_id ? user_id : "");

        fetch(`../delivery/${deliverable_id}`, {
            method: "POST",
            body: data,
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            }
        })
            .then(response =>  {
                if (!response.ok) {
                    throw new Error("Grading undelivered delivery failed.");
                }
                return response.json();
            })
            .then(data => {
                if (data.error) {
                    throw new Error(data.error);
                }
                submission_id = data.submission_id;
                container.dataset.id = data.id;
            })
            .catch(error => {
                alert(error);
            });
    }

    function gradeDeliverable() {
        const container = this.closest("div.dcontainer");
        const input = container.querySelector("input");
        if (!input.checkValidity()) {
            alert("Points have an invalid value (beyond max or below zero).");
            input.value = input.dataset.value;
            return;
        }
        const points = input.value;
        const hasMarkDown = container.querySelector("i.fa-markdown").classList.contains("active");
        const comment = container.querySelector("textarea").value;
        const shifted = encodeURIComponent(MARKDOWN.ceasarShift(comment));
        const delivery_id = container.dataset.id;
        if (!delivery_id) {
            gradeForNotDelivered(container, points, hasMarkDown, shifted);
            return;
        }

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
            .then(response =>  {
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
    function mdToggle() {
        gradeDeliverable.call(this);
    }
    MARKDOWN.enablePreview("../markdown");
    MARKDOWN.activateButtons(mdToggle);
});
