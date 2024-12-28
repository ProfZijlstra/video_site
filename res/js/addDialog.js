
window.addEventListener("load", () => {
    // close dialog
    document.getElementById("closeAddDialog").onclick = () => {
        document.getElementById('add_modal').close();
    };

    // show add modal
    function showModal(evt) {
        const day_id = this.parentNode.dataset.day_id;
        const date = this.parentNode.dataset.date;
        let next = this.parentNode.dataset.next;
        if (next == undefined) {
            next = date;
        }
        document.getElementById('day_id').value = day_id;
        document.getElementById('startdate').value = date;
        document.getElementById('stopdate').value = next;
        document.getElementById('add_modal').showModal();
        evt.stopPropagation();
        document.getElementById('name').focus();
    };
    const adds = document.querySelectorAll("div.data i.fa-plus-square");
    for (const add of adds) {
        add.onclick = showModal;
    }
});
