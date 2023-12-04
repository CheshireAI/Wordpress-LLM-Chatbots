jQuery(document).ready(function($) {
    $(".help-icon").on("click", function() {
      alert("Help content for this field");
    });
  
    // Handle reload button click
    $(".reload-button").on("click", function(e) {
      e.preventDefault();
      location.reload();
    });
  
    // Handle clear button click
    $(".clear-button").on("click", function(e) {
      e.preventDefault();
  
      // Clear the 'character-value' paragraphs immediately
      $(".character-value").text("");
  
      // Clear all input fields
      $('input[type="text"], select').val('');
  
      // Clear all keys from the data store
      localforage.clear().then(function() {
        console.log("Local storage has been cleared");
  
        // Send a request to the server to clear meta keys
        $.ajax({
          type: "POST",
          url: characterdemo_data.ajax_url,
          data: {
            action: "clear_meta_keys",
            nonce: $("#character_nonce").val()
          },
          success: function(response) {
            console.log("Meta keys cleared on server");
          },
          error: function(error) {
            console.error("Failed to clear meta keys on server: ", error);
          }
        });
      }).catch(function(err) {
        console.error("Failed to clear local storage: ", err);
      });
    });
  
// Save the form field when the Enter key is pressed
$(".character-form input[type='text'], .character-form select").on("keydown", function(e) {
    if (e.keyCode === 13) { // Enter key
      e.preventDefault();
      var formField = $(this);
      var fieldName = formField.attr("name");
      var fieldValue = formField.val();
  
      // Perform the age validation and display an error if necessary
      if ((fieldName === "age_demo" || fieldName === "character_age_demo") && fieldValue !== "") {
        var ageValue = parseInt(fieldValue);
  
        if (isNaN(ageValue) || ageValue < 18) {
          alert("Age must be 18 or older. Please enter a valid age.");
          formField.addClass("error");
          return false;
        } else {
          formField.removeClass("error");
        }
      }
  
      // Now, send the form field data to the server with an AJAX request
      $.ajax({
        type: "POST",
        url: characterdemo_data.ajax_url,
        data: {
          action: characterdemo_data.action,
          field_name: fieldName,
          field_value: fieldValue,
          nonce: $("#character_nonce").val()
        },
        success: function(response) {
          // Update the corresponding 'character-value' paragraph with the new field value
          formField.siblings(".character-value").text(fieldValue);
        },
        error: function(error) {
          console.log(error);
        }
      });
    }
  });
  
  // Handle dropdown change event
  $(".character-form select").on("change", function() {
    var formField = $(this);
    var fieldName = formField.attr("name");
    var fieldValue = formField.val();
  
    // Now, send the form field data to the server with an AJAX request
    $.ajax({
      type: "POST",
      url: characterdemo_data.ajax_url,
      data: {
        action: characterdemo_data.action,
        field_name: fieldName,
        field_value: fieldValue,
        nonce: $("#character_nonce").val()
      },
      success: function(response) {
        // Update the corresponding 'character-value' paragraph with the new field value
        formField.siblings(".character-value").text(fieldValue);
      },
      error: function(error) {
        console.log(error);
      }
    });
  });
  
  $(".character-form").on("submit", function(e) {
    e.preventDefault(); // Prevent the default form submission action
  
    // Perform the age validation and display an error if necessary
    var ageField = $('[name="age_demo"], [meta_key="character_age_demo"]');
    var ageValue = ageField.val().trim();
  
    if (ageValue !== "" && (isNaN(parseInt(ageValue)) || parseInt(ageValue) < 18)) {
      alert("Age must be 18 or older. Please enter a valid age.");
      ageField.addClass("error");
      return false;
    } else {
      ageField.removeClass("error");
    }
  
    // Continue with saving the form
    saveFormdemo();
  });
  
    // Function to handle reading the uploaded file and displaying it
    var readURL = function(input) {
      if (input.files && input.files[0]) {
        var reader = new FileReader();
  
        reader.onload = function(e) {
          $(".profile-pic").attr("src", e.target.result);
        };
  
        reader.readAsDataURL(input.files[0]);
      }
    };
  
    $(".file-upload").on("change", function() {
      readURL(this);
      uploadPhotodemo(this.files[0]);
    });
  
    $(".upload-button").on("click", function() {
      $(".file-upload").click();
    });
  
    function uploadPhotodemo(file) {
      var formData = new FormData();
      formData.append("action", "upload_character_photo");
      formData.append("nonce", $("#character_nonce").val());
      formData.append("character_photo_demo", file);
  
      // Now, send the form data to the server with an AJAX request
      $.ajax({
        type: "POST",
        url: characterdemo_data.ajax_url,
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
          // Handle the success response if needed
        },
        error: function(error) {
          // Handle the error response if needed
          console.log(error);
        }
      });
    }
  
    function saveFormdemo() {
      var form_data = $(".character-form").serializeArray(); // This will collect all of your form data as an array of objects
  
      var saveButton = $(".save-button");
      var statusMessage = saveButton.siblings(".status-message");
  
      // Now, send the form data to the server with an AJAX request
      $.ajax({
        type: "POST",
        url: characterdemo_data.ajax_url,
        data: {
          action: characterdemo_data.action,
          form_data: form_data,
          nonce: $("#character_nonce").val()
        },
        success: function(response) {
          // Loop through the form data to update the corresponding elements on the page
          $.each(form_data, function(i, field) {
            // Find the element with the 'character-value' class that is a sibling of the input with the same name as this form field, and update its text
            $('[name="' + field.name + '"]')
              .siblings(".character-value")
              .text(field.value);
          });
  
          statusMessage.text("Your changes have been saved successfully.");
          statusMessage.removeClass("error").addClass("success");
  
          // Toggle the magicformButton
          var magicformButton = $(".magic-form-icon");
          var characterContainer = $(".character-container");
          var magicformIcon = magicformButton.find("i");
          var originalIconClass = magicformIcon.attr("data-original-icon-class");
  
          if (characterContainer.is(":visible")) {
            characterContainer.hide();
            magicformIcon.removeClass("fa-times").addClass(originalIconClass);
          } else {
            characterContainer.show();
            magicformIcon.removeClass(originalIconClass).addClass("fa-times");
          }
        },
        error: function(error) {
          console.log(error);
  
          statusMessage.text("Failed to save changes. Please try again.");
          statusMessage.removeClass("success").addClass("error");
        }
      });
  
      return false; // Prevent the form from refreshing or redirecting
    }
  });
  
  document.addEventListener("DOMContentLoaded", function() {
    var magicformButton = document.querySelector(".magic-form-icon");
    var characterContainer = document.querySelector(".character-container");
    var magicformIcon = magicformButton.querySelector("i");
  
    var originalIconClass = magicformIcon.classList[1];
    magicformIcon.setAttribute("data-original-icon-class", originalIconClass);
  
    var toggleCharacterContainer = function() {
      if (characterContainer.style.display !== "flex") {
        characterContainer.style.display = "flex";
        magicformIcon.classList.remove(originalIconClass);
        magicformIcon.classList.add("fa-times");
      } else {
        characterContainer.style.display = "none";
        magicformIcon.classList.remove("fa-times");
        magicformIcon.classList.add(originalIconClass);
      }
    };
  
    characterContainer.style.display = "none"; // Set initial state to closed
  
    magicformButton.addEventListener("click", toggleCharacterContainer);
  });
  