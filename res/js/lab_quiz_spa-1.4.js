window.addEventListener("load", () => {
    // global state in this module
    // get the deliveryId from the URL
    const w = window;
    const delivBtns = document.querySelectorAll('span.delivNum, span.questNum');
    const delivs = document.querySelectorAll('div.deliverables, div.qcontainer');
    const chevLeft = document.getElementById("chevLeft");
    const chevRight = document.getElementById("chevRight");

    const url = w.location.pathname;
    const lastSlash = url.lastIndexOf('/');
    const urlNoDelivNum = url.substring(0, lastSlash);
    let delivId = parseInt(url.substring(lastSlash + 1));
    if (!delivId || isNaN(delivId)) {
        delivId = 1; // default to first deliverable
    }

    // switch to overview if the URL indicates it
    if (delivId == 0) {
        enterOverview();
    }

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
        w.history.pushState({ "id": id }, '', urlNoDelivNum + '/' + id);
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
    w.addEventListener('popstate', (e) => {
        const state = e.state;
        let id = state?.id;
        if (!id) {
            id = 1; // default to first deliverable
        }
        switchDeliv(id);
    });

    // keyboard shortcuts
    document.addEventListener('keydown', (e) => {
        if (!e.ctrlKey) {
            return; // only if ctrl is pressed
        }
        switch (e.code) {
            case "Period":
                goClickDeliv(delivId + 1);
                break
            case "Comma":
                goClickDeliv(delivId - 1);
                break;
        }
    });

    function enterOverview() {
        w.enterOverviewBtn.classList.add('hide');
        w.exitOverviewBtn.classList.remove('hide');
        w.content.classList.add('overview');
        w.history.pushState({ "id": 0 }, '', urlNoDelivNum + '/0');
        for (const d of delivs) {
            d.classList.remove('hide');
        }
        chevLeft.classList.remove('active');
        chevRight.classList.remove('active');
        for (const d of delivBtns) {
            d.classList.add('active');
        }
    };
    w.enterOverviewBtn.onclick = enterOverview;
    w.exitOverviewBtn.onclick = function() {
        w.enterOverviewBtn.classList.remove('hide');
        w.exitOverviewBtn.classList.add('hide');
        w.content.classList.remove('overview');
        if (delivId == 0) {
            delivId = 1;
        }
        switchDeliv(delivId);
    };
});
