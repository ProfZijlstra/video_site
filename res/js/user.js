window.addEventListener("load", () => {

    const first = document.getElementById('first');
    const knownAs = document.getElementById('knownAs');
    first.onkeyup = () => {
        if (!knownAs.dataset.provided) {
            knownAs.value = first.value;
        }
    };

    knownAs.onkeyup = () => {
        knownAs.dataset.provided = knownAs.value;
    };
});
