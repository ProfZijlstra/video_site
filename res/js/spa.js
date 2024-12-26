window.addEventListener("load", () => {
    // global state in this module
    let delivId = 1;

    // aquire emelents onto which we're going to hook
    const multiPageBtn = document.getElementById('multiPage');
    const singlePageBtn = document.getElementById('singlePage');
    const keyShortCuts = document.getElementById("keyShortCuts");
    const multiHeader = document.querySelector("h2.multi");
    const singleHeader = document.querySelector("h2.single");
    const backLink = document.getElementById("back");
    const navBack = document.querySelectorAll("nav.back")[1];
    const delivBtns = document.querySelectorAll('span.delivNum, span.questNum');
    const delivs = document.querySelectorAll('div.deliverables, div.qcontainer');
    const chevLeft = document.getElementById("chevLeft");
    const chevRight = document.getElementById("chevRight");
    const finish = document.querySelector("div.finish");
    const finForm = document.getElementById("finishQuiz");

    // helper functions to work with the URL
    function urlNoDelivNum() {
        const url = window.location + "";
        const i = url.lastIndexOf('/');
        return url.substring(0, i);
    }

    // actual switching logic starts here
    function toSpa(e, hist = true) {
        multiPageBtn.classList.add('hide');
        singlePageBtn.classList.remove('hide');
        multiHeader.classList.remove('hide');
        singleHeader.classList.add('hide');
        backLink.setAttribute("href", "../../lab");
        navBack?.classList.add('hide');
        keyShortCuts.classList.remove('hide');
        switchDeliv(1);
        window.localStorage.setItem("view", "multi");
        window.scrollTo(0, 0);

        if (!hist) {
            return;
        }
        window.history.pushState({ "id": 1 }, '', window.location + "/1");
        if (finForm) { // for the quiz
            const action = finForm.getAttribute("action");
            finForm.setAttribute("action", "../" + action);
        }
    };
    multiPageBtn.onmousedown = toSpa;

    function fromSpa(e, hist = true) {
        multiPageBtn.classList.remove('hide');
        singlePageBtn.classList.add('hide');
        multiHeader.classList.add('hide');
        singleHeader.classList.remove('hide');
        backLink.setAttribute("href", "../lab");
        navBack?.classList.remove('hide');
        keyShortCuts.classList.add('hide');
        delivs.forEach(e => e.classList.remove('hide'));
        window.localStorage.setItem("view", "single");

        if (!hist) {
            return;
        }
        window.history.pushState(null, '', urlNoDelivNum());
        if (finForm) { // for the quiz
            const action = finForm.getAttribute("action");
            finForm.setAttribute("action", action.substring(3));
        }
    };
    singlePageBtn.onmousedown = fromSpa;

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
        // quiz finish button
        if (id < delivs.length) {
            finish?.classList.add("hide");
        } else {
            finish?.classList.remove("hide");
        }
    }
    function clickDeliv() {
        const id = this.textContent;
        switchDeliv(id);
        window.history.pushState({ "id": id }, '', urlNoDelivNum() + '/' + id);
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
        if (state && state.id) {
            toSpa(null, false);
            switchDeliv(state.id);
        } else {
            fromSpa(null, false);
        }
    });

    // switch to SPA if user preference indicates it
    const view = window.localStorage.getItem("view");
    const selected = document.querySelector("body").dataset.selected;
    if (view && view == "multi" && !selected) {
        toSpa();
    }

    // keyboard shortcuts in SPA mode
    document.addEventListener('keydown', (e) => {
        if (window.localStorage.getItem("view") != "multi"
            || !e.ctrlKey) {
            return;
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
});
