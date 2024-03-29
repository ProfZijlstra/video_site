<!DOCTYPE html>
<!--
    Created on : Aug 29, 2014, 13:00:01 PM
    Author     : mzijlstra 
-->
<html>
    <head>
        <title>Login</title>
        <meta charset="UTF-8">
        <meta name=viewport content="width=device-width, initial-scale=1">
        <style>
            body {
                background-color: white;
                font-size: 12px;
                background-color: #eee;
            }
            .central {
                position: absolute;
                top: 25%;
                left: 50%;
                width: 400px;
                margin-left: -200px;
                border: 1px solid black;
                padding: 15px;
                box-shadow: 4px 4px 8px 1px rgba(0, 0, 0, 0.2), -4px -4px 8px 1px rgba(0, 0, 0, 0.2), 6px -6px 20px 0px rgba(0, 0, 0, 0.19), -6px 6px 20px 0px rgba(0, 0, 0, 0.19);
                background-color: #FFF;
            }
            h1 {
                font-size: 450%;
                text-align: center;
                margin-bottom: 2px;
                text-align: center;
            }
            h2 {
                font-size: 170%;
                margin-top: 0px;
                text-align: center;
            }
            .container {
                border: 2px solid white;
                padding: 10px;
            }
            .error {
                font-size: 15px;
                margin-bottom: 10px;
                text-align: center;
                font-weight: bold;
            }
            input[type=text], input[type=password] { 
                background-color: white;
                margin-bottom: 5px;
                width: 375px;
                border: 1px solid #336;
            }
            #lgn_right {
                text-align: right;
            }
            input[type=submit] {
                border: 1px solid black;
                position: relative;
                padding: 2px 5px;
                left: 4px;
            }
            input:focus {
                box-shadow: 4px 4px 8px 1px rgba(0, 0, 0, 0.2), -4px -4px 8px 1px rgba(0, 0, 0, 0.2);
            }
            @media screen and (max-width: 900px) {
                .central {
                    top: 10%;
                    width: 90%;
                    margin-left: 0px;
                    left: 2px;
                }
                input[type=text], input[type=password] {  
                    width: 100%;
                }
            }
        </style>
    </head>
    <body>
        <div class="central">
            <h1>Lecture Videos</h1>
            <div class="container">
                <form action="login" method="post">
                    <?php if (isset($error)) : ?>
                        <div class="error"><?= $error ?></div>
                        <br />
                    <?php endif; ?>
                    <input type="text" name="email" placeholder="Email or Username" autofocus />
                    <br />
                    <input type="password" name="pass" placeholder="Password" />
                    <br />
                    <div id="lgn_right"><input type="submit" value="Login" /></div>
                </form>
            </div>
        </div>
    </body>
</html>
