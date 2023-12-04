jQuery(document).ready(function($) {
  // Function to send AJAX request
  function sendAjaxRequest(url, data, onSuccess, onError) {
    $.ajax({
      type: "POST",
      url: url,
      data: data,
      success: onSuccess,
      error: onError
    });
  }

  $(".help-icon").on("click", function() {
    alert("Help content for this field");
  });

  // Handle reload button click
  $(".reload-button").on("click", function(e) {
    e.preventDefault();
    location.href = location.href + "?timestamp=" + new Date().getTime();
  });

  // Handle clear button click
  $(".clear-button").on("click", function(e) {
    e.preventDefault();

    $(".character-value").text("");
    $('input[type="text"], select').val('');

    localforage.clear().then(function() {
      console.log("Local storage has been cleared");

      sendAjaxRequest(
        character_data.ajax_url,
        {
          action: "clear_meta_keys",
          nonce: $("#character_nonce").val()
        },
        function(response) {
          console.log("Meta keys cleared on server");
        },
        function(error) {
          console.error("Failed to clear meta keys on server: ", error);
        }
      );
    }).catch(function(err) {
      console.error("Failed to clear local storage: ", err);
    });
  });

  //Saving character form on Enter and Drop Down
  function updateMetaKey(fieldElement) {
    var formField = $(fieldElement);
    var fieldName = formField.attr("name");
    var fieldValue = formField.val();
  
    // Age field validation
    if ((fieldName === "age" || fieldName === "character_age") && fieldValue !== "") {
      var ageValue = parseInt(fieldValue);
      if (isNaN(ageValue) || ageValue < 18) {
        alert("Age must be 18 or older. Please enter a valid age.");
        formField.addClass("error");
        return;
      } else {
        formField.removeClass("error");
      }
    }
  
    // Sending the AJAX request
    $.ajax({
      url: character_data.ajax_url,
      method: 'POST',
      data: {
        action: character_data.action,
        nonce: $("#character_nonce").val(),
        form_data: [{
          name: fieldName,
          value: fieldValue
        }]
      },
      success: function(response) {
        console.log("Form field data sent successfully:", response);
        formField.siblings(".character-value").text(fieldValue);
      },
      error: function(error) {
        console.log("Failed to send form field data:", error);
      }
    });
  }
  
  $(".character-form input[type='text']").on("keydown", function(e) {
    if (e.keyCode === 13) {
      e.preventDefault();
      updateMetaKey(this);
    }
  });
  
  $(".character-form select").on("change", function() {
    updateMetaKey(this);
  });   

//Saving character form on Save Button
$(".character-form").on("submit", function(e) {
  e.preventDefault(); // Prevent the default form submission action

  // Perform the age validation and display an error if necessary
  var ageField = $('[name="age"], [meta_key="character_age"]');
  var ageValue = ageField.val().trim();

  if (ageValue !== "" && (isNaN(parseInt(ageValue)) || parseInt(ageValue) < 18)) {
    alert("Age must be 18 or older. Please enter a valid age.");
    ageField.addClass("error");
    return false;
  } else {
    ageField.removeClass("error");
  }

  // Continue with saving the form
  saveForm();
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
    uploadPhoto(this.files[0]);
  });

  $(".upload-button").on("click", function() {
    $(".file-upload").click();
  });

  function uploadPhoto(file) {
    var formData = new FormData();
    formData.append("action", "upload_character_photo");
    formData.append("nonce", $("#character_nonce").val());
    formData.append("character_photo", file);

    // Now, send the form data to the server with an AJAX request
    $.ajax({
      type: "POST",
      url: character_data.ajax_url,
      data: formData,
      processData: false,
      contentType: false,
      success: function(response) {
        var data = JSON.parse(response);
        if(data.success) {
            // Update all images
            $(".profile-pic, .background-photo, .photo").attr("src", data.imageURL);
        } else {
            console.log('Image upload failed');
        }
      },
      error: function(jqXHR, textStatus, errorThrown) {
        // Log the status and error thrown to console for debugging
        console.log('Status: ' + textStatus + ', error: ' + errorThrown);
        
        // Handle error response if needed, e.g. display a message to the user
        alert('An error occurred while uploading the image. Please try again.');
      }
    });
    
  }

  function saveForm() {
    var form_data = $(".character-form").serializeArray();
  
    var saveButton = $(".save-button");
    var statusMessage = saveButton.siblings(".status-message");
  
    $.ajax({
      type: "POST",
      url: character_data.ajax_url,
      data: {
        action: character_data.action,
        form_data: form_data,
        nonce: $("#character_nonce").val()
      },
      success: function(response) {
        $.each(form_data, function(i, field) {
          $('[name="' + field.name + '"]')
            .siblings(".character-value")
            .text(field.value);
        });
  
        statusMessage.text("Your changes have been saved successfully.");
        statusMessage.removeClass("error").addClass("success");
      },
      error: function(error) {
        console.log(error);
  
        statusMessage.text("Failed to save changes. Please try again.");
        statusMessage.removeClass("success").addClass("error");
      }
    });
  
    return false;
  }  

// Toggle code using jQuery instead of vanilla JS
var magicformButton = $(".magic-form-icon");
var characterContainer = $(".character-container");
var magicformIcon = magicformButton.find("i");

// We are assuming that original icon class is always second class in the class list
var originalIconClass = magicformIcon.attr("class").split(' ')[1]; 

magicformButton.on("click", function() {
  if (characterContainer.is(":visible")) {
    characterContainer.hide();
    magicformIcon.removeClass("fa-times").addClass(originalIconClass);
  } else {
    characterContainer.show();
    magicformIcon.removeClass(originalIconClass).addClass("fa-times");
  }
});

// If the viewport is desktop-sized when the page loads, switch the icon to the "fa-times" icon
if ($(window).width() > 767) { // adjust the 768px breakpoint as necessary
  magicformIcon.removeClass(originalIconClass).addClass("fa-times");
}

}); //close jquery