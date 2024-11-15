
window.addEventListener("load", () => {
    // used by click upload
    let icon = null;
    // the following used by openPath
    let openCallBack = null;

    function openDir() {
        const icon = this.querySelector("i");
        icon.classList.toggle("fa-folder");
        icon.classList.toggle("fa-folder-open");
        const refreshIcon = this.parentNode.querySelector("i.refresh");

        if (this.dataset.isOpen) {
            refreshIcon.classList.add("hide");
            delete this.dataset.isOpen;
            this.parentNode.querySelector('div.listing').remove();
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
            .then(response =>  {
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
                this.parentNode.appendChild(listing);
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

    function refresh(dir) {
        const parent = dir.closest("div.dir");
        const listing = parent.querySelector("div.listing");
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

    function openPath(path) {
        const dirs = path.split("/");
        dirs.pop(); // don't need to open deepest dir
        const pathListing = document.querySelector(".listing");
        openPathRec(dirs, pathListing);
    }
    function openPathRec(dirs, pathListing) {
        const dir = dirs.shift();

        // remove empty strings from split
        while (dir == "" && dirs.length) {
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
                if (child.firstElementChild.dataset.isOpen) {
                    refresh(child.firstElementChild);
                } else {
                    child.firstElementChild.click();
                }
                return;
            }

        }

    }

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
            .then(response =>  {
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

    function clickRemFile() {
        const file = this.dataset.loc;
        if(!confirm("Delete the following file?\n\n" + file)) {
            return;
        }

        const data = new FormData();
        data.append("location", file);

        fetch("file/deleteFile", {
            method: "POST",
            body: data
        })
            .then(response =>  {
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

    function clickRemDir() {
        const file = this.dataset.loc;
        if(!confirm("Delete the following directory?\n\n" + file)) {
            return;
        }

        const data = new FormData();
        data.append("location", file);

        fetch("file/deleteDir", {
            method: "POST",
            body: data
        })
            .then(response =>  {
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

    const makeDirDialog = document.getElementById("makeDirDialog");
    document.getElementById("addDir").onclick = function() {
        makeDirDialog.showModal();
    };
    document.getElementById("closeMakeDir").onclick = function() {
        makeDirDialog.close();
    }
    document.getElementById("makeDirForm").onsubmit = function() {
        const dir = document.getElementById("makeDirField").value;
        const data = new FormData();
        data.append("dir", dir);

        fetch("file/makeDir", {
            method: "POST",
            body: data
        })
            .then(response => {
                if (!response.redirected) { // on success post redirects
                    alert("Creating directory failed");
                    return;
                }
                makeDirDialog.close();
                openPath(dir);
            });

        return false; // don't actually submit the form
    };
});

