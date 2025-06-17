window.addEventListener('load', function() {
    const resetDialog = document.getElementById('resetDialog');
    const closeResetDialog = document.getElementById('closeResetDialog');
    const resetButton = document.querySelector('.tools i.fa-key');
    const resetForm = document.getElementById('resetForm');
    const msg = document.getElementById('msg');
    const currentPassword = document.getElementById('currentPassword');
    const newPassword = document.getElementById('newPassword');
    const confirmPassword = document.getElementById('confirmPassword');

    // modal controls
    resetButton.addEventListener('click', function() {
        resetDialog.showModal();
    });

    closeResetDialog.addEventListener('click', function() {
        resetDialog.close();
    });

    // validation logic for passwords
    confirmPassword.addEventListener('input', passwordsMatch);
    newPassword.addEventListener('input', passwordsMatch);
    function passwordsMatch() {
        if (newPassword.value !== confirmPassword.value) {
            confirmPassword.setCustomValidity("Passwords do not match.");
        } else {
            confirmPassword.setCustomValidity("");
        }
    };

    // handle password reset form submission
    resetForm.onsubmit = function() {
        const currentPass = currentPassword.value.trim();
        const newPass = newPassword.value.trim();
        const confirmPass = confirmPassword.value.trim();

        if (newPass !== confirmPass) {
            alert("New password and confirmation do not match.");
            return false;
        }

        if (newPassword.length < 8) {
            alert("New password must be at least 8 characters long.");
            return false;
        }

        const data = new FormData();
        data.append('currentPassword', currentPass);
        data.append('newPassword', newPass);
        data.append('confirmPassword', confirmPass);
        data.append('uid', document.getElementById('uid').value);
        fetch('resetPass', {
            method: 'POST',
            body: data
        })
            .then(response => response.json())
            .then(json => {
                msg.textContent = json.msg;
                if (json.success) {
                    msg.style.color = "green";
                    currentPassword.value = '';
                    newPassword.value = '';
                    confirmPassword.value = '';
                } else {
                    msg.style.color = "red";
                }
                clearMsg();
            })
            .catch(error => {
                msg.textContent = "Error: " + error.message;
                msg.style.color = "red";
                clearMsg();
            });

        resetDialog.close();
        return false;
    };

    // send knownAs change to server
    let knowAsTimer = 0;
    document.getElementById('knownAs').addEventListener('input', function() {
        clearTimeout(knowAsTimer);
        knowAsTimer = setTimeout(changeKnownAs.bind(this), 750);
    });
    function changeKnownAs() {
        const knownAs = this.value.trim();
        const data = new FormData();
        data.append('knownAs', knownAs);

        if (knownAs) {
            fetch('changeKnownAs', {
                method: 'POST',
                body: data
            })
                .then(response => response.json())
                .then(json => {
                    if (json.success) {
                        msg.textContent = "Known as updated successfully.";
                        msg.style.color = "green";
                    } else {
                        msg.textContent = "Error updating known as.";
                        msg.style.color = "red";
                    }
                    clearMsg();
                })
                .catch(error => {
                    msg.textContent = "Error: " + error.message;
                    msg.style.color = "red";
                    clearMsg();
                });
        }
    }
    function clearMsg() {
        setTimeout(() => {
            msg.textContent = '';
            msg.style.color = '';
        }, 5000);
    }
});
