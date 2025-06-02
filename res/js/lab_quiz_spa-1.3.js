window.addEventListener("load", () => {
    // global state in this module
    // get the deliveryId from the URL
    const url = window.location.pathname;
    const lastSlash = url.lastIndexOf('/');
    const urlNoDelivNum = url.substring(0, lastSlash);
    let delivId = parseInt(url.substring(lastSlash + 1));

    const delivBtns = document.querySelectorAll('span.delivNum, span.questNum');
    const delivs = document.querySelectorAll('div.deliverables, div.qcontainer');
    const chevLeft = document.getElementById("chevLeft");
    const chevRight = document.getElementById("chevRight");

    function switchDeliv(id) {
        delivId = parseInt(id);
        if (id == "1") {
            chevLeft.classList.remove("active");
        } else {
            chevLeft.classList.add("active");
        }
        const elem = document.getElementById("db" + id);
        if (elem.nextElementSibling == chevRight) {
            chevRight.classList.remove("active");
        } else {
            chevRight.classList.add("active");
        }
        const buttonId = "db" + id;
        const deliverableId = "d" + id;
        for (const db of delivBtns) {
            if (db.id == buttonId) {
                db.classList.add('active');
            } else {
                db.classList.remove('active');
            }
        }
        for (const d of delivs) {
            if (d.id == deliverableId) {
                d.classList.remove('hide');
                const select = d.querySelector('select');
                if (select) { // for labs
                    select.focus();
                } else { // for quizzes
                    d.querySelector("textarea")?.focus();
                }
            } else {
                d.classList.add('hide');
            }
        }
    }

    function clickDeliv() {
        const id = this.textContent;
        switchDeliv(id);
        window.history.pushState({ "id": id }, '', urlNoDelivNum + '/' + id);
    }
    delivBtns.forEach(e => e.onmousedown = clickDeliv);

    function goClickDeliv(id) {
        const elem = document.getElementById("db" + id);
        if (!elem) {
            return;
        }
        elem.onmousedown();
    }
    chevLeft.onmousedown = function() {
        goClickDeliv(delivId - 1);
    };
    chevRight.onmousedown = function() {
        goClickDeliv(delivId + 1);
    };

    // make browser back button work properly
    window.addEventListener('popstate', (e) => {
        const state = e.state;
        switchDeliv(state.id);
    });

    // keyboard shortcuts
    document.addEventListener('keydown', (e) => {
        switch (e.code) {
            case "Period":
                goClickDeliv(delivId + 1);
                break
            case "Comma":
                goClickDeliv(delivId - 1);
                break;
        }
    });
});
