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
    const exportModal = document.getElementById("exportDialog");
    document.getElementById("exportBtn").onclick = function() {
        const select = document.getElementById('stype');
        if (dayAbbr.match(/W\dD6/)) {
            select.value = "SAT";
        } else {
            select.value = stype;
        }
        document.getElementById("camsPwd").focus();
        exportModal.showModal();
    };

    // when page opens show the export modal if the URL has #export
    if (window.location.hash == "#export") {
        document.getElementById("exportBtn").click();
    }

    document.getElementById("closeExportDialog").onclick = () => {
        exportModal.close();
    };

    document.getElementById("doExport").onclick = function() {
        this.disabled = true;
        document.getElementById("exportSpinner").classList.add("rotate");
    };
});
