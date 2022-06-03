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
    fetch('/videos/user/faculty').then(response => response.json()).then(response => createCloneModal(response));
  };

  function createCloneModal(fac_users) {
    const fac_user_opts = fac_users.map(user => {
      return /*#__PURE__*/React.createElement("option", {
        value: user.id
      }, user.firstname, " ", user.lastname);
    });
    const content = document.getElementById("content");
    const offering_id = document.getElementById("offering").dataset.id;
    ReactDOM.unmountComponentAtNode(content);
    const clone = /*#__PURE__*/React.createElement("div", {
      class: "modal"
    }, /*#__PURE__*/React.createElement("h2", null, "Clone Offering"), /*#__PURE__*/React.createElement("form", {
      method: "POST",
      action: "clone"
    }, /*#__PURE__*/React.createElement("input", {
      type: "hidden",
      name: "offering_id",
      value: offering_id
    }), /*#__PURE__*/React.createElement("div", {
      class: "line"
    }, /*#__PURE__*/React.createElement("label", null, "New Block:"), /*#__PURE__*/React.createElement("input", {
      name: "block"
    })), /*#__PURE__*/React.createElement("div", {
      class: "line"
    }, /*#__PURE__*/React.createElement("label", null, "Faculty"), /*#__PURE__*/React.createElement("select", {
      name: "fac_user_id"
    }, fac_user_opts)), /*#__PURE__*/React.createElement("div", {
      class: "line"
    }, /*#__PURE__*/React.createElement("label", null, "Start Date:"), /*#__PURE__*/React.createElement("input", {
      type: "date",
      name: "date"
    })), /*#__PURE__*/React.createElement("div", {
      class: "submit"
    }, /*#__PURE__*/React.createElement("button", null, "Submit"))));
    ReactDOM.render(clone, content);
    document.getElementById("overlay").classList.add("visible");
  }

  ;

  function updValue(e) {
    e.preventDefault();
    e.stopPropagation();
  }

  function editDay(day_id, desc, evt) {
    evt.preventDefault();
    evt.stopPropagation();
    const content = document.getElementById("content");
    ReactDOM.unmountComponentAtNode(content);
    const edit = /*#__PURE__*/React.createElement("div", {
      class: "modal"
    }, /*#__PURE__*/React.createElement("h2", null, "Edit Day Title"), /*#__PURE__*/React.createElement("form", {
      method: "POST",
      action: "edit"
    }, /*#__PURE__*/React.createElement("input", {
      type: "hidden",
      name: "day_id",
      value: day_id
    }), /*#__PURE__*/React.createElement("div", {
      class: "line"
    }, /*#__PURE__*/React.createElement("label", null, "Title:"), /*#__PURE__*/React.createElement("input", {
      name: "desc",
      placeholder: desc
    })), /*#__PURE__*/React.createElement("div", {
      class: "submit"
    }, /*#__PURE__*/React.createElement("button", null, "Submit"))));
    ReactDOM.render(edit, content);
    document.getElementById("overlay").classList.add("visible");
  }

  document.getElementById("edit").onclick = function () {
    for (let w = 1; w < 5; w++) {
      for (let d = 1; d < 8; d++) {
        const td = document.getElementById(`W${w}D${d}`);
        const nextSib = td.querySelector("time");
        const text = td.querySelector(".text").innerText;
        const edit = document.createElement("i");
        edit.setAttribute("class", "far fa-edit");
        edit.onclick = editDay.bind(null, td.dataset.day_id, text);
        td.insertBefore(edit, nextSib);
      }
    }
  };
});