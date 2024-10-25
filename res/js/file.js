
window.addEventListener("load", () => {
    function openDir() {
        const icon = this.querySelector("i");
        icon.classList.toggle("fa-folder");
        icon.classList.toggle("fa-folder-open");

        if (this.dataset.isOpen) {
            delete this.dataset.isOpen;
            this.parentNode.querySelector('div.listing').remove();
            return;
        }
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
                this.parentNode.appendChild(listing);
                spinner.classList.remove('rotate');
            })
            .catch(error => {
                alert(error);
                spinner.classList.remove('rotate');
            });
    }
    document.querySelectorAll("span.dir").forEach(e => e.onclick = openDir);

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

});

