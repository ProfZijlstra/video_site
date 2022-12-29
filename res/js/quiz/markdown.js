const MARKDOWN = (function() {

    function enablePreview(url) {
        function getHtmlForMarkdown() {
            const parent = this.parentNode.parentNode;
            const markdown = parent.previousElementSibling.value;
            const area = parent.querySelector('.previewArea');
            area.replaceChildren();
            const container = document.createElement('div');
            container.className = 'pcontainer';
            area.appendChild(container);
    
            fetch(url + "?markdown=" + encodeURIComponent(markdown))
                .then((response) => response.text())
                .then((data) => {
                    container.innerHTML = data;
                    Prism.highlightAllUnder(container);
                });
        }
        const buttons = document.querySelectorAll('.previewBtn button' );
        for (const button of buttons) {
            button.onclick = getHtmlForMarkdown;
        }    
    }

    return { enablePreview };
})()