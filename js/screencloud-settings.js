console.log("loading settings scripts...");

document.addEventListener("DOMContentLoaded", function () {
  // SAVE POPUP

  var form = document.querySelector('form[action="options.php"]');
  var inputs = form.querySelectorAll("input, select, textarea");
  var isFormChanged = false;

  // Function to mark form as changed
  function markFormAsChanged() {
    isFormChanged = true;
  }

  // Add change event listener to all existing form inputs
  inputs.forEach(function (input) {
    input.addEventListener("change", markFormAsChanged);
  });

  // Monitor for the add connection button click
  document
    .getElementById("addConnection")
    .addEventListener("click", function () {
      markFormAsChanged();
      // Attach change listener to newly added fields
      setTimeout(function () {
        var newInputs = form.querySelectorAll("input, select, textarea");
        newInputs.forEach(function (input) {
          input.removeEventListener("change", markFormAsChanged); // Avoid duplicate listeners
          input.addEventListener("change", markFormAsChanged);
        });

        var deleteButton = document.querySelector(
          `#screencloud-connections fieldset:last-child button.delete`
        );
        deleteButton.addEventListener("click", function () {
          removeConnection(deleteButton);
        });
      }, 100); // Small delay to ensure DOM is updated
    });

  // Monitor for delete buttons clicks in dynamic connections
  document
    .getElementById("screencloud-connections")
    .addEventListener("click", function (e) {
      if (e.target && e.target.matches("button.delete")) {
        markFormAsChanged();
      }
    });

  // Before unload event
  window.addEventListener("beforeunload", function (event) {
    if (isFormChanged) {
      var confirmationMessage =
        "You have unsaved changes. Are you sure you want to leave without saving?";
      event.returnValue = confirmationMessage;
      return confirmationMessage;
    }
  });

  // Handle save/discard decision
  form.addEventListener("submit", function () {
    isFormChanged = false; // Reset the flag when form is submitted
  });

  // SETTINGS LOGIC

  document
    .getElementById("addConnection")
    .addEventListener("click", function () {
      var container = document.getElementById("screencloud-connections");
      var index = container.getElementsByTagName("fieldset").length;
      var fieldset = document.createElement("fieldset");
      fieldset.innerHTML = `
            <legend>Connection ${index + 1}</legend>
            <label><span>Name:</span> <input type="text" name="screencloud_plugin_settings[connections][${index}][name]" required></label>
            <label><span>Webhook URL:</span> <input type="text" name="screencloud_plugin_settings[connections][${index}][webhook_url]" required></label>
            <label><span>API Key:</span> <input type="text" name="screencloud_plugin_settings[connections][${index}][api_key]" required></label>
            <button type="button" class="button delete">Delete</button>
        `;

      var addButton = document.getElementById("addConnection");
      container.insertBefore(fieldset, addButton);
      // Attach the delete listener to the new delete button
      var deleteButton = fieldset.querySelector("button.delete");
      console.log("deleteButton", deleteButton);
      deleteButton.addEventListener("click", function () {
        removeConnection(deleteButton);
      });
    });

  function removeConnection(button) {
    var fieldset = button.parentNode;
    fieldset.parentNode.removeChild(fieldset);
  }

  // Attach delete listener to all initial delete buttons
  var deleteButtons = document.querySelectorAll(
    "#screencloud-connections button.delete"
  );
  deleteButtons.forEach(function (button) {
    button.addEventListener("click", function () {
      removeConnection(button);
    });
  });
});
