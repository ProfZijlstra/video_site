<!DOCTYPE html>
<html>

<head>
    <title>Signup MSD Pre-Enrollment Course</title>
    <meta name=viewport content="width=device-width, initial-scale=1">
    <meta charset="UTF-8">
    <link rel="stylesheet" href="res/css/lib/font-awesome-all.min.css">
    <link rel="stylesheet" href="res/css/common-1.1.css">
    <link rel="stylesheet" href="res/css/adm.css">
    <style>
        label {
            display: inline-block;
            width: 150px;
            margin-bottom: 10px;
        }
        input {
            width: 500px;
        }
        .btn {
            text-align: right;
            padding-right: 85px;
        }
        .error {
            color: red;
        }
    </style>
</head>

<body>
    <?php include("header.php"); ?>
    <main>
        <div id="content">
        <div class="error"><?= $msg ?></div>

        <form action="signup" method="post">
            <div>
                <label>Email</label>
                <input required id="email1" type="email" name="email" placeholder="Your email address"/> <br />
            </div>
            <div>
                <label>Confirm Email</label>
                <input required id="email2" type="email" name="confirm_email" placeholder="Your email address" /> <br />
                <span id="email_error" class="error"></span>
            </div>
            <div>
                <label>Given Name(s):</label>
                <input required type="text" name="first" id="first" placeholder="Including middle name as shown on passport / drivers license" /> <br />
            </div>
            <div>
                <label>Family Name(s):</label>
                <input required type="text" name="last"  placeholder="As on passport / drivers license" /> <br />
            </div>
            <div>
                <label>Password:</label>
                <input required id="pass1" type="password" name="pass" placeholder="Desired password for this site"/> <br />
            </div>
            <div>
                <label>Confirm Password:</label>
                <input required id="pass2" type="password" name="confirm_pass" placeholder="Desired password for this site"/> <br />
                <span id="pass_error" class="error"></span>
            </div>

            <div class="btn">
                <button type="submit">Enroll</button>
            </div>
        </form>
        </div>

        <script>
window.addEventListener("load", () => {
    document.forms[0].onsubmit = function() {
        let error = false;
        
        const pass1 = document.getElementById("pass1").value;
        const pass2 = document.getElementById("pass2").value;
        if (pass1 != pass2) {
            error = true;
            document.getElementById("pass_error").innerText = "Passwords are not the same"
        }

        const email1 = document.getElementById("email1").value;
        const email2 = document.getElementById("email2").value;
        if (email1 != email2) {
            error = true;
            document.getElementById("email_error").innerText = "Email addresses are not the same"
        }

        if (error) {
            return false;
        }
    }
});            
        </script>
    </main>
</body>

</html>