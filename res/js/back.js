window.addEventListener("load", () => {    
    document.querySelector("body > main").addEventListener("click", function (evt) {
        if (evt.target.classList.contains('back') || 
            evt.target.classList.contains('fa-arrow-left')) {
                window.history.go(-1);
        }
    });
});
