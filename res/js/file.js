
window.addEventListener("load", () => {
    // used by click upload
    let icon = null;
    // the following used by openPath
    let openCallBack = null;

    // code for opening a directory
    function openDir() {
        const icon = this.querySelector("i");
        icon.classList.toggle("fa-folder");
        icon.classList.toggle("fa-folder-open");
        const refreshIcon = this.parentNode.querySelector("i.refresh");

        if (this.dataset.isOpen) {
            refreshIcon.classList.add("hide");
            delete this.dataset.isOpen;
            this.parentNode.parentNode.querySelector('div.listing').remove();
            return;
        }
        refreshIcon.classList.remove("hide");
        this.dataset.isOpen = true;

        const dir = this.dataset.dir;
        const spinner = this.querySelector("i.spinner");
        spinner.classList.add('rotate');
        const data = new URLSearchParams();
        data.append("dir", dir);

        const url = "file/dir?"
        fetch(url + data)
            .then(response => {
                if (!response.ok) {
                    throw new Error(action + "Fetching directory failed.");
                }
                return response.text();
            })
            .then(data => {
                const parser = new DOMParser();
                const doc = parser.parseFromString(data, "text/html");
                const listing = doc.querySelector('div.listing');
                listing.querySelectorAll("span.dir").forEach(
                    e => e.onclick = openDir
                );
                listing.querySelectorAll("i.fa-link").forEach(
                    e => e.onclick = copyLink
                );
                listing.querySelectorAll("i.upload").forEach(
                    e => e.onclick = clickUpload
                );
                listing.querySelectorAll("i.remFile").forEach(
                    e => e.onclick = clickRemFile
                );
                listing.querySelectorAll("i.remDir").forEach(
                    e => e.onclick = clickRemDir
                );
                listing.querySelectorAll("i.refresh").forEach(
                    e => e.onclick = clickRefresh
                );
                listing.querySelectorAll("i.rename").forEach(
                    e => e.onclick = clickRename
                );
                listing.querySelectorAll("div.file").forEach(
                    e => e.onclick = clickLine
                );
                this.parentNode.parentNode.appendChild(listing);
                spinner.classList.remove('rotate');

                if (openCallBack) {
                    openCallBack();
                }
            })
            .catch(error => {
                alert(error);
                spinner.classList.remove('rotate');
            });
    }
    document.querySelectorAll("span.dir").forEach(e => e.onclick = openDir);

    // refreshing a directory
    function refresh(dir) {
        const parent = dir.closest("div.dir");
        const listing = parent.querySelector("div.listing");
        // double refresh can cause lisiting to be gone
        if (!listing) {
            return;
        }
        const icon = parent.querySelector("i");
        icon.classList.toggle("fa-folder");
        icon.classList.toggle("fa-folder-open");
        listing.remove();
        delete dir.dataset.isOpen;
        dir.click();
    }
    function clickRefresh() {
        const parent = this.parentNode;
        const dir = parent.previousElementSibling;
        refresh(dir);
    }
    document.querySelectorAll("i.refresh").forEach(e => e.onclick = clickRefresh);

    // opening all the directories on a path
    function openPath(path) {
        const dirs = path.split("/");
        dirs.pop(); // don't need to open deepest dir
        const pathListing = document.querySelector(".listing");
        openPathRec(dirs, pathListing);
    }
    function openPathRec(dirs, pathListing) {
        let dir = dirs.shift();

        // remove empty strings from split
        while (dir == "" && dirs.length > 0) {
            dir = dirs.shift();
        }
        // base case exit
        if (!dir) {
            openCallBack = null;
            return;
        }
        for (const child of pathListing.children) {
            if (child.classList.contains("dir") && child.dataset.dir == dir) {
                openCallBack = function() {
                    const nextListing = child.querySelector(".listing");
                    openPathRec(dirs, nextListing);
                }
                const dir = child.querySelector("span.dir");
                if (dir.dataset.isOpen) {
                    refresh(dir);
                } else {
                    dir.click();
                }
                return;
            }

        }

    }

    // copying a link
    function showCopied(elem) {
        return function() {
            elem.classList.remove('fa-link');
            elem.classList.add('fa-check');
            setTimeout(function() {
                elem.classList.remove('fa-check');
                elem.classList.add('fa-link');
            }, 1000);
        }
    }
    function copyLink() {
        navigator.clipboard.writeText(this.dataset.link)
            .then(showCopied(this));
    }
    document.querySelectorAll("i.fa-link").forEach(e => e.onclick = copyLink);

    // uploading a file
    function clickUpload() {
        icon = this;
        document.getElementById("uploadLocation").value = this.dataset.loc;
        document.getElementById("uploadFile").click();
    }
    document.querySelectorAll("i.upload").forEach(e => e.onclick = clickUpload);
    function sendFile() {
        const parent = icon.closest("div.dir");
        const data = new FormData();
        data.append("location", document.getElementById("uploadLocation").value);
        data.append("file", this.files[0]);

        const dir = parent.querySelector("span.dir")
        const spinner = parent.querySelector("i.spinner");
        spinner.classList.add('rotate');

        fetch("file/upload", {
            method: "POST",
            body: data,
        })
            .then(response => {
                spinner.classList.remove('rotate');
                if (!response.ok) {
                    alert("Uploading file failed.");
                }
                return response.json();
            })
            .then(data => {
                if (data.error) {
                    alert(data.error);
                } else {
                    // get listing again
                    refresh(dir);
                }
            });
    }
    document.getElementById("uploadFile").onchange = sendFile;

    // deleting a file
    function clickRemFile() {
        const file = this.dataset.loc;
        if (!confirm("Delete the following file?\n\n" + file)) {
            return;
        }

        const data = new FormData();
        data.append("location", file);

        fetch("file/deleteFile", {
            method: "POST",
            body: data
        })
            .then(response => {
                if (!response.ok) {
                    alert("Deleting file failed.");
                }
                const parent = this.closest("div.dir");
                const dir = parent.querySelector("span.dir");
                refresh(dir);
            });
    }
    document.querySelectorAll("i.remFile").forEach(
        e => e.onclick = clickRemFile
    );

    // deleting a directory
    function clickRemDir() {
        const file = this.dataset.loc;
        if (!confirm("Delete the following directory?\n\n" + file)) {
            return;
        }

        const data = new FormData();
        data.append("location", file);

        fetch("file/deleteDir", {
            method: "POST",
            body: data
        })
            .then(response => {
                if (!response.ok) {
                    alert("Deleting directory failed.\n\n"
                        + "Only empty directories can be deleted");
                    return;
                }
                // delete listing
                const parent = this.closest("div.dir").parentNode.closest("div.dir");
                const dir = parent.querySelector("span.dir");
                refresh(dir);
            });

    }
    document.querySelectorAll("i.remDir").forEach(
        e => e.onclick = clickRemDir
    );

    // making a directory
    const makeDirDialog = document.getElementById("makeDirDialog");
    const addDirBtn = document.getElementById("addDir");
    if (addDirBtn) {
        addDirBtn.onclick = function() {
            makeDirDialog.showModal();
        };
    }
    document.getElementById("closeMakeDir").onclick = function() {
        makeDirDialog.close();
    }
    document.getElementById("makeDirForm").onsubmit = function() {
        const input = document.getElementById("makeDirField");
        const dir = input.value;
        const data = new FormData();
        data.append("dir", dir);

        fetch("file/makeDir", {
            method: "POST",
            body: data
        })
            .then(response => {
                if (!response.ok) { // on success post redirects
                    alert("Creating directory failed");
                    return false;
                }
                makeDirDialog.close();
                openPath(dir);
                input.value = "";
            });

        return false; // don't actually submit the form
    };

    // renaming (or moving) a file or directory
    const renameDialog = document.getElementById("renameDialog");
    function clickRename() {
        renameDialog.showModal();
        document.getElementById("renameSrc").value = this.dataset.loc;
        document.getElementById("renameDst").value = this.dataset.loc;
    }
    document.querySelectorAll("i.rename").forEach(
        e => e.onclick = renameClick
    );
    document.getElementById("closeRenameDialog").onclick =
        () => renameDialog.close();
    document.getElementById("renameForm").onsubmit = function() {
        renameDialog.close();
        const src = document.getElementById("renameSrc").value;
        const dst = document.getElementById("renameDst").value;
        const parentDir = /\.\./;
        if (src.match(parentDir)) {
            alert("Rename source should not contain ../");
            return false;
        }
        if (dst.match(parentDir)) {
            alert("Rename destination should not contain ../");
            return false;
        }
        const data = new FormData();
        data.append("src", src);
        data.append("dst", dst);

        fetch("file/rename", {
            method: "POST",
            body: data
        }).then(response => {
            if (!response.ok) {
                alert("Renaming file failed");
                return false;
            }
            openPath(src);

            // check if we should also open dst
            const srcDirs = src.split('/');
            const dstDirs = dst.split('/');
            srcDirs.pop();
            dstDirs.pop();
            const srcDir = srcDirs.join('/');
            const dstDir = dstDirs.join('/');
            if (dstDir != srcDir) {
                openPath(dst);
            }
        });

        return false; // don't actually submit the form
    };

    function clickLine(evt) {
        if (this != evt.target) {
            return;
        }
        const href = this.querySelector("a");
        if (href) {
            href.click();
            return;
        }
        const dir = this.querySelector("span.dir");
        if (dir) {
            dir.click();
        }
    }
    document.querySelectorAll("div.file").forEach(e => e.onclick = clickLine);
});

