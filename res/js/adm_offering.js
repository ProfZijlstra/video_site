window.addEventListener("load", () => {
  // display on page summary when clicking info button
  document.getElementById("info-btn").onclick = function () {
    const e = React.createElement;
    const offering_id = document.getElementById('offering').dataset.id;
    fetch('info').then(response => response.json()).then(function (json) {
      for (const day in json) {
        const elm = document.getElementById(day).getElementsByClassName("info")[0];
        const props = json[day];
        if (day == "total") {
          props.showUsers = INFO.offeringViewers;
        } else {
          props.showUsers = INFO.dayViewers;
        }
        ReactDOM.render(e(INFO.Info, props), elm);
      }
    });
    fetch(`enrollment?offering_id=${offering_id}`).then(response => response.json()).then(json => INFO.setEnrollment(json));
  };
  document.getElementById("close-overlay").onclick = INFO.hideTables;
  document.getElementById("overlay").onclick = function (evt) {
    if (evt.target == this) {
      INFO.hideTables();
    }
  };
  document.getElementById("clone").onclick = function () {
    document.getElementById("overlay").classList.add("visible");
    document.getElementById('clone_modal').classList.remove('hide');
    document.getElementById('delete_modal').classList.add('hide');
    document.getElementById('edit_modal').classList.add('hide');
  };
  document.getElementById("delete").onclick = function () {
    document.getElementById("overlay").classList.add("visible");
    document.getElementById('clone_modal').classList.add('hide');
    document.getElementById('edit_modal').classList.add('hide');
    document.getElementById('delete_modal').classList.remove('hide');
  };
  function editDay(day_id, desc, evt) {
    evt.preventDefault();
    evt.stopPropagation();
    document.getElementById("overlay").classList.add("visible");
    document.getElementById('clone_modal').classList.add('hide');
    document.getElementById('delete_modal').classList.add('hide');
    document.getElementById('edit_modal').classList.remove('hide');
    document.getElementById('day_id').value = day_id;
    document.getElementById('day_desc').setAttribute("placeholder", desc);
  }
  document.getElementById("edit").onclick = function () {
    const divs = document.querySelectorAll("div.data");
    for (const div of divs) {
      const nextSib = div.querySelector("time");
      const text = div.querySelector(".text").innerText;
      const edit = document.createElement("i");
      edit.setAttribute("class", "far fa-edit");
      edit.onclick = editDay.bind(null, div.dataset.day_id, text);
      div.insertBefore(edit, nextSib);
    }
  };
});