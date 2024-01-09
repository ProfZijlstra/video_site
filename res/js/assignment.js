
  window.addEventListener("load", () => {
      // hide overlay and any/all modal(s)
      function hide() {
          overlay.classList.remove("visible");
          const modals = document.querySelectorAll(".modal");
          for (const modal of modals) {
              modal.classList.add("hide");
          }
      }
      document.getElementById("close-overlay").onclick = hide;
      document.getElementById("overlay").onclick = function(evt) {
          if (evt.target == this) {
              hide();
          }
      };

      // show add quiz modal
      function showModal(evt) {
          const day_id = this.parentNode.dataset.day_id;
          const date = this.parentNode.dataset.date;
          document.getElementById('day_id').value = day_id;
          document.getElementById('startdate').value = date;
          document.getElementById('stopdate').value = date;
          overlay.classList.add("visible");
          document.getElementById("add_modal").classList.remove("hide");
          evt.stopPropagation();
          document.getElementById('name').focus();
      };
      const adds = document.querySelectorAll("div.data i.fa-plus-square");
      for (const add of adds) {
          add.onclick = showModal;
      }
  });
