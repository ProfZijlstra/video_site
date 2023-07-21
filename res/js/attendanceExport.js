window.addEventListener("load", () => {
    const sessionDiv = document.getElementById('session');
    const stype = sessionDiv.dataset.stype;
    const dayAbbr = sessionDiv.dataset.day;

    const tags = document.getElementById('data').getElementsByTagName('input');
    for (const tag of tags) {
        tag.onchange = doUpdate;
    }

    function doUpdate(evt) {
        const tr = evt.target.parentNode.parentNode;
        const id = tr.dataset.id;
        const inClassFields = tr.getElementsByClassName("inClass");
        const inClass = inClassFields[0].checked;
        const commentFields = tr.getElementsByClassName("comment");
        const comment = commentFields[0].value;
        const update = {
            "id": id,
            "inClass": inClass,
            "comment": comment
        };
        fetch(`${stype}/${id}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(update)
        });
    }

    // 500 ms delay to ensure save is sent
    document.getElementById("regenBtn").onclick = function() {
        setTimeout(() => document.forms.regenForm.submit(), 500);
    };

    // show modal when exportBtn clicked
    const overlay = document.getElementById("overlay");
    document.getElementById("exportBtn").onclick = function() {
        overlay.classList.add('visible');
        const select = document.getElementById('stype');
        if (dayAbbr.match(/W\dD6/)) {
            select.value = "SAT";
        } else {
            select.value = stype; 
        }
        document.getElementById("camsPwd").focus();
    };

    // hide overlay and any/all modal(s)
    function hide() {
        overlay.classList.remove("visible");
        const modals = document.querySelectorAll(".modal");
        for (const modal of modals) {
            modal.classList.add("hide");
        }
    }
    document.getElementById("close-overlay").onclick = hide;
    document.getElementById("overlay").onclick = function (evt) {
        if (evt.target == this) {
            hide();
        }
    };

    document.getElementById("doExport").onclick = function() {
        const data = {
            "pwd": document.getElementById("camsPwd").value,
            "stype": document.getElementById('stype').value,
            "date": document.getElementById('date').value,
            "start": document.getElementById("start").value,
            "stop": document.getElementById("stop").value
        }

        fetch(`export`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        });            
    }



    
});
