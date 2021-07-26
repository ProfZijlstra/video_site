<!DOCTYPE html>
<!--
To change this license header, choose License Headers in Project Properties.
To change this template file, choose Tools | Templates
and open the template in the editor.
-->
<html>
    <head>
        <meta charset="UTF-8">
        <title>Users</title>
        <script src="res/js/lib/jquery-2.1.1.js"></script>
        <script>
            $(function () {
                "use strict";

                $("tr.user").click(function () {
                    var t = $(this);
                    if (t.next().length && !t.next().hasClass("user")) {
                        return; // projects already shown
                    }

                    var uid = $(t.children("td")[0]).text();
                    var nextrow = $("<tr><td colspan='8'></td></tr>");
                    nextrow.find("td").append("<table></table>");
                    var container = nextrow.find("table");
                    container.append("<tr><th>Name</th><th>Created</th><th>Accessed</th></tr>");

                    $.ajax({
                        "dataType": "json",
                        "url": "user/" + uid + "/project",
                        "success": function (data) {
                            for (var i = 0; i < data.length; i++) {
                                var proj = data[i];
                                var row = $("<tr pid='" + proj['id'] + "' class='proj'>");
                                row.append("<td class='pname'>" + proj['name'] + "</td>");
                                row.append("<td>" + proj['created'] + "</td>");
                                row.append("<td>" + proj['accessed'] + "</td>");
                                row.click(function () {
                                    var tr = $(this);
                                    window.location.assign("user/" + uid
                                            + "/project/" + tr.attr("pid"));
                                });
                                container.append(row);
                            }
                            t.after(nextrow);
                        }
                    });
                });
                $(".add button").click(function () {
                    window.location.assign("user/add");
                });
            });
        </script>
        <style>
            body {
                font-family: monospace;
                font-size: 12px;
                color: #333366; 
                padding: 0em 5em;
            }
            h1 {
                //margin-bottom: 5px;
            }
            table {
                border-collapse: collapse;
                width: 100%;
            }
            tr {
                background-color: white;
                border-bottom: 1px solid black;
            }
            tr:nth-child(odd) {
                background-color: #FAFAFF;
            }
            td {
                cursor: pointer;
                text-align: right;
                border: 1px solid black;
                padding: 0px 3px;
            }
            div.add {
                text-align: center;
                margin: 1em 0em;
            }
        </style>
    </head>
    <body>
        <h1>Users:</h1>
        <div class="add">
            <button>Add a User</button>
        </div>
        <table>
            <tr>
                <th>ID</th>
                <th>first</th>
                <th>last</th>
                <th>email</th>
                <th>created</th>
                <th>accessed</th>
                <th>active</th>
            </tr>
            <?php foreach ($users as $user) : ?>
                <tr class="user">
                    <td><?= $user['id'] ?></td>
                    <td><?= $user['firstname'] ?></td>
                    <td><?= $user['lastname'] ?></td>
                    <td><?= $user['email'] ?></td>
                    <td><?= $user['created'] ?></td>
                    <td><?= $user['accessed'] ?></td>
                    <td><?= $user['active'] ?></td>
                    <td><a href="user/<?= $user['id'] ?>">
                            <button>Edit</button>
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
        <div class="add">
            <button>Add a User</button>
        </div>
    </body>
</html>
