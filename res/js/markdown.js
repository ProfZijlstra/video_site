const MARKDOWN = (function() {
    let mdurl = "markdown";

    // we ceasar shift because dreamhost has some very solid XSS injection 
    // protection... which stops a variety of markdown submits
    function ceasarShift(text, amount = 1) {
        let result = "";
        for (const char of text) {
            const code = char.charCodeAt(0) + amount;
            result += String.fromCharCode(code);
        }
        return result;
    }

    function getHtmlForMarkdown(evt) {
        evt.preventDefault();
        const parent = this.parentNode.parentNode;
        const markdown = parent.previousElementSibling.value;
        const shifted = ceasarShift(markdown);
        const area = parent.querySelector('.previewArea');
        area.replaceChildren();
        const container = document.createElement('div');
        container.className = 'pcontainer';
        area.appendChild(container);

        fetch(mdurl + "?markdown=" + encodeURIComponent(shifted))
            .then((response) => response.text())
            .then((data) => {
                container.innerHTML = data;
                Prism.highlightAllUnder(container);
            });
    }

    function enablePreview(url) {
        mdurl = url;
        const buttons = document.querySelectorAll('button.previewBtn');
        for (const button of buttons) {
            button.onclick = getHtmlForMarkdown;
        }    
    }

    return { getHtmlForMarkdown, enablePreview, ceasarShift };
})()