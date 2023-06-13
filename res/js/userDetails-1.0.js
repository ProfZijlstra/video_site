window.addEventListener("load", () => {
    const uid = document.getElementById("uid").value;

    function updateUser() {
        form = new URLSearchParams();
        form.append("first", document.getElementById('first').value);
        form.append("knownAs", document.getElementById('knownAs').value);
        form.append("last", document.getElementById('last').value);
        form.append("email", document.getElementById('email').value);
        form.append("studentID", document.getElementById('studentID').value);
        form.append("teamsName", document.getElementById('teamsName').value);
        form.append("isAdmin", document.getElementById('isAdmin').value);
        form.append("isFaculty", document.getElementById('isFaculty').value);
        form.append("active", document.getElementById('active').value);

        fetch(`${uid}`, {
            method : "POST",
            body : form.toString(),
            headers :
                {'Content-Type' : 'application/x-www-form-urlencoded'},
        })
        .then(response => response.json())
        .then(json => {
            if (json) {
                document.getElementById('error').innerText = json.msg;
            }
        });
    }
    [
        "first", 
        "knownAs", 
        "last", 
        "email", 
        "studentID", 
        "teamsName", 
        "isAdmin", 
        "isFaculty", 
        "active"
    ].forEach(e => { 
        document.getElementById(e).onchange = updateUser; 
    });

    document.getElementById("pass").onchange = function() {
        form = new URLSearchParams();
        form.append("pass", document.getElementById('pass').value);
        fetch(`${uid}/pass`, {
            method : "POST",
            body : form.toString(),
            headers :
                {'Content-Type' : 'application/x-www-form-urlencoded'},
        });
    }

    document.querySelector(".fa-arrow-left").addEventListener("click", () => {
        // wait a 500ms to make sure that the onchange save handlers have run
        setTimeout(() => window.history.go(-1), 500);
    });
});
