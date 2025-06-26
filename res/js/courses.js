window.addEventListener('load', () => {
    const cloneDialog = document.getElementById('cloneDialog');
    function clickClone() {
        const parent = this.closest('div.offering');
        cloneDialog.querySelector('input[name="offering_id"]').value =
            parent.dataset.oid;
        const course = parent.dataset.course;
        const block = parent.dataset.block;
        document.getElementById('clone_form').action = `${course}/${block}/clone`;
        document.getElementById('daysPerLesson').value = parent.dataset.daysperlesson;
        document.getElementById('lessonsPerPart').value = parent.dataset.lessonsperpart;
        document.getElementById('parts').value = parent.dataset.lessonparts;
        document.getElementById('showDates').checked = parent.dataset.showdates;

        cloneDialog.showModal();
    }
    document.querySelectorAll('.offering .fa-copy').forEach(copy => {
        copy.addEventListener('click', clickClone);
    });
    document.getElementById('closeCloneDialog').addEventListener('click', () => {
        cloneDialog.close();
    });

    // Admin part of the code
    const createDialog = document.getElementById('createDialog');
    const deleteDialog = document.getElementById('deleteDialog');
    if (!createDialog || !deleteDialog) {
        return;
    }

    document.getElementById('createCourse').addEventListener('click', () => {
        createDialog.showModal();
    });
    document.getElementById('closeCreateDialog').addEventListener('click', () => {
        createDialog.close();
    });

    function clickDel() {
        const parent = this.closest('div.offering');
        deleteDialog.querySelector('input[name="offering_id"]').value =
            parent.dataset.oid;
        const course = parent.dataset.course;
        const block = parent.dataset.block;
        document.getElementById('deleteFrom').action = `${course}/${block}/delete`;
        deleteDialog.showModal();
    }
    document.querySelectorAll('.offering .fa-trash-alt').forEach(trash => {
        trash.addEventListener('click', clickDel);
    });
    document.getElementById('closeDeleteDialog').addEventListener('click', () => {
        deleteDialog.close();
    });
});

