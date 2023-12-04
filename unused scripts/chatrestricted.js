//chat.js

// Global
let conversationHistory = "";
let inactiveTimeout = null;
let isInactivityPrompt = false;
let inactivityInterval = 3 * 60 * 1000; // 3 minutes
let maxInactivityChecks = 5; // The maximum number of times this check should occur
let inactivityChecks = 0; // Counter keeps track of how many times the check has been made
let userSubscriptionStatus = false; // Assuming the initial subscription status is false
let userRole = ""; // Global variable to store the user role
let isAdmin = false; // Global variable to indicate admin status
let isTyping = false;
let responseQueue = []; 

const baseUrl = window.location.origin; // Get the base URL of the current page
const soundFilePath = `${baseUrl}/wp-content/plugins/sexbot/sounds/message.wav`; // Construct the dynamic file path
const soundEffect = new Audio(soundFilePath); // Play the sound effect

jQuery(document).ready(async ($) => {
  await localforage.ready();
//  await localforage.clear();

  const name = myUserMeta.name || "";
  const photoUrl = myUserMeta.photo || "";   
  const colors = ["#8B008B", "#9370DB", "#8B008B", "#4B0082", "#1E90FF", "#E40D53", "#557CCA", "#6955BF"];
  const randomColor = colors[Math.floor(Math.random() * colors.length)];
  const $responseColumn = $(".response-column");
  const responseColumnElement = $responseColumn[0];
  const $prompt = $("#prompt");

  document.documentElement.style.setProperty("--primary-color", randomColor);

  const updateChatHeader = (photoUrl, name) => {
    $(".chat-header h3").text(name || "");
    if (photoUrl) {
      $(".chat-header .header-photo")
        .attr("src", photoUrl)
        .on("load", function () {
          $(".chat-header h3, .chat-header .header-photo").show();
        });
    } else {
      $(".chat-header .header-photo").hide();
    }
  };
  
  updateChatHeader(photoUrl, name);

  function retrieveUserRoleAndStatus() {
    // Retrieve the user role from WordPress
    userRole = myUserMeta.role || ""; // Assuming you have a property in myUserMeta indicating the user role

    // Check if the user is an admin based on the user role
    if (userRole.toLowerCase() === "administrator") {
      isAdmin = true;
    }

    // Retrieve the user subscription status from WordPress
    userSubscriptionStatus = myUserMeta.subscriptionStatus || false; 
  }

  retrieveUserRoleAndStatus();

  function convertURLsToLinks(element) {
    element.contents().filter(function () {
      return this.nodeType === 3; // Filter only text nodes
    }).each(function () {
      $(this).replaceWith(function () {
        return this.nodeValue.replace(/(https?:\/\/(?!.*\.(?:png|jpg|jpeg|gif|bmp))[^\s]+)|(www\.[^\s]+)/g, '<a href="$&" target="_blank" style="word-break: break-all; color: blue;">$&</a>');
      });
    });
  }   

  function scrollToBottom() {
    const responseColumnWrapper = $(".response-column-wrapper");
    const images = responseColumnWrapper.find('img');
    const lastImage = images.last()[0];
  
    if (lastImage && lastImage.complete) {
      // If the last image is already loaded, scroll immediately
      scrollToBottomAction();
    } else if (lastImage) {
      // If the last image is not yet loaded, wait for it to load
      lastImage.addEventListener('load', scrollToBottomAction);
    } else {
      // No image elements found, scroll immediately
      scrollToBottomAction();
    }
  }
  
  function scrollToBottomAction() {
    const responseColumnWrapper = $(".response-column-wrapper");
    if (window.innerWidth >= 768) {
      // Smooth scrolling animation for non-mobile devices
      responseColumnWrapper[0].scrollTo({
        top: responseColumnWrapper[0].scrollHeight,
        behavior: "smooth"
      });
    } else {
      // Instant scroll to the bottom for mobile devices
      responseColumnWrapper[0].scrollTop = responseColumnWrapper[0].scrollHeight;
    }
  }  

/* One message only no follow-up
  function appendUserMessage(message) {
    const timestamp = new Date().toLocaleTimeString([], { hour: 'numeric', minute: '2-digit', hour12: true }).replace(/^0/, ''); // Update this line
    const userMessageElement = $('<div class="message user-message">' + message + '</div>');
    const userTimestampElement = $('<div class="timestamp user-timestamp">' + timestamp + '</div>');
  
    conversationHistory += "\n###User: " + message + "\n";
    console.log(conversationHistory);
  
    userMessageElement.append(userTimestampElement); // Append timestamp as a child of user message
    $responseColumn.append(userMessageElement);
    scrollToBottom();
  }
  */
  
  function appendUserMessage(message) {
    const timestamp = new Date().toLocaleTimeString([], { hour: 'numeric', minute: '2-digit', hour12: true }).replace(/^0/, '');
    const userMessageElement = $('<div class="message user-message"></div>');
    const userTextElement = $('<div class="text user-text">' + message + '</div>');
    const userTimestampElement = $('<div class="timestamp user-timestamp">' + timestamp + '</div>');
  
    // Append the user message to the conversation history without the timestamp
    if (!$prompt.text().includes("Inactivity prompt")) {
      conversationHistory += "\n###User: " + message + "\n";
    }
    console.log(conversationHistory);
  
    const inactivityPromptText = "###Instruct: The person you are chatting with is taking awhile to respond, send a photo to grab their attention.";
  
    if ($prompt.text().trim() !== inactivityPromptText) {
      userMessageElement.append(userTextElement);
      userMessageElement.append(userTimestampElement);
      $responseColumn.append(userMessageElement);
      convertURLsToLinks(userMessageElement);
      scrollToBottom();
  
      //Add the animated chat bubble
      const placeholderUrl = "https://t3.ftcdn.net/jpg/03/46/83/96/360_F_346839683_6nAPzbhpSkIpb8pmAwufkC7c5eD7wYws.jpg";
      const aiMessageElement = $('<div class="message ai-message placeholder-message"><img src="' + (photoUrl || placeholderUrl) + '" class="photo"></div>');
      const placeholderBubbleElement = $('<div class="chat-bubble-placeholder"><div class="bubble"></div><div class="bubble"></div><div class="bubble"></div></div>');
  
      aiMessageElement.append(placeholderBubbleElement); // Append the placeholder to aiMessageElement
      $responseColumn.append(aiMessageElement);
  
      scrollToBottom();
    }
  
    // If there was an existing timeout, clear it
    if (inactiveTimeout) {
      clearTimeout(inactiveTimeout);
    }
  
    // Set a timeout to check for user inactivity after inactivityInterval, but only if we haven't done it before and not exceeded maxInactivityChecks
    if (!isInactivityPrompt && inactivityChecks < maxInactivityChecks) {
      inactiveTimeout = setTimeout(function () {
        // Set the flag to indicate this is an inactivity prompt
        isInactivityPrompt = true;
  
        // Only generate the inactivity prompt if the prompt is currently empty
        if ($prompt.text().trim() === '') {
          $prompt.text(inactivityPromptText);
          console.log("Inactivity prompt: " + inactivityPromptText);
          handleSubmit();
        }
  
        // Increment the counter
        inactivityChecks++;
      }, inactivityInterval);
    }
    // After handleSubmit is called, reset the isInactivityPrompt flag
    isInactivityPrompt = false;
  }  
  
  function appendAIMessage(message) {
    const placeholderUrl = "https://t3.ftcdn.net/jpg/03/46/83/96/360_F_346839683_6nAPzbhpSkIpb8pmAwufkC7c5eD7wYws.jpg";    
    const timestamp = new Date().toLocaleTimeString([], { hour: 'numeric', minute: '2-digit', hour12: true }).replace(/^0/, '');
    const aiTimestampElement = $('<div class="timestamp ai-timestamp">' + timestamp + '</div>');
    const aiMessageElement = $('<div class="message ai-message"></div>'); // Create a container for AI message and photo and timestamp
    const aiTextElement = $('<div class="text ai-text">' + message + '</div>'); // AI message text
    const aiPhotoElement = $('<img src="' + (photoUrl || placeholderUrl) + '" class="photo">'); // AI message photo

    aiMessageElement.append(aiPhotoElement); 
    aiMessageElement.append(aiTextElement); 
    aiMessageElement.append(aiTimestampElement); 
    $responseColumn.append(aiMessageElement);
    convertURLsToLinks(aiTextElement);
  
    typeMessage(aiTextElement, message.trim(), 0, 0, () => {
      isTyping = false;
      displayNextResponse();
    });
    scrollToBottom();

    // Play the sound effect
    soundEffect.play();    
  
    // Exclude image URLs, links, and div elements from conversation history
    const hasDivClass = /<div\b[^>]*class\s*=\s*["'][^"']*["']/i.test(message);
    if (!hasDivClass) {
      conversationHistory += "\n###Response: " + message + "\n";
      console.log(conversationHistory);
    }
  }  
  
//  function appendImageMessage(imageUrl) {
//    const aiMessageElement = $('<div class="message ai-message"><img src="' + photoUrl + '" class="photo"></div>');
//    const imageElement = $('<img src="' + imageUrl + '" alt="Generated image" class="generated-image">');
//    aiMessageElement.append(imageElement);
//    $responseColumn.append(aiMessageElement);
//    scrollToBottom();
//    conversationHistory += "[Image: ${imageUrl}]\n";
//    console.log(conversationHistory);
//  }
  
  function displayNextResponse() {
    if (responseQueue.length > 0 && !isTyping) {
      isTyping = true;
      const nextResponse = responseQueue.shift();
  
      if (nextResponse.response.startsWith("http")) {
        const imageUrl = nextResponse.response;
        appendImageMessage(imageUrl);
        isTyping = false;
        displayNextResponse();
      } else {
        appendAIMessage(nextResponse.response);
      }
    }
  }

  async function handleSubmit(greeting = false) {
    const prompt = $prompt.text();
    const isAdmin = myUserMeta.role === 'administrator';
    const isActiveSubscriber = myUserMeta.status === 'active';
    const messageLimit = isAdmin ? Infinity : (isActiveSubscriber ? Infinity : 5);
    const messages = $(".response-column .message");
  
    if (messages.length >= messageLimit) {
      const messagesToKeep = messages.slice(messages.length - messageLimit);
  
      let updatedConversationHistory = "";
  
      messagesToKeep.each(function () {
        const messageTextDiv = $(this).find('.text');
        const messageText = messageTextDiv.text().trim();
        const isUserMessage = $(this).hasClass("user-message");
        const isAiMessage = $(this).hasClass("ai-message");
  
        if (isUserMessage) {
          updatedConversationHistory += "\n###User: " + messageText + "\n";
        } else if (isAiMessage) {
          updatedConversationHistory += "\n###Response: " + messageText + "\n";
        }
      });
  
      conversationHistory = updatedConversationHistory;
  
      await localforage.setItem("conversationHistory", conversationHistory);
    }  
  
    if (!isAdmin && !isActiveSubscriber && messages.length >= messageLimit) {
      // User has reached the message limit and is not an active subscriber
      const alertMessage = "You have reached the message limit. Please subscribe to continue chatting. To subscribe and remove message restrictions, click [here](link-to-subscription-page).";
    
      appendAIMessage(alertMessage);
    
      return;
    } else {
      // User's status has changed, remove paywall and alert messages
      $(".ai-message").filter(function () {
        const messageText = $(this).text().trim();
        const alertMessage = "You have reached the message limit. Please subscribe to continue chatting. To subscribe and remove message restrictions, click [here](link-to-subscription-page).";
        return messageText === alertMessage;
      }).remove();
    }       
  
    if (prompt !== "" || greeting) {
      if (!greeting) {
        const userMessage = prompt;
        appendUserMessage(userMessage);
        $prompt.text("");
      }
  
      console.log('User Role:', myUserMeta.role);
  
      $.ajax({
        url: myChatAjax.ajaxurl,
        type: "POST",
        data: {
          action: "generate_chat",
          prompt: prompt,
          greeting: greeting,
          conversationHistory: conversationHistory,
          userRole: myUserMeta.role,
        },
        success: async function (response) {
          let ai_responses = [];
          if (response.success) {
            console.log(response.data);
            ai_responses = response.data;
          } else {
            ai_responses = ["I am currently busy. Please check back later."];
          }
          for (let i = 0; i < ai_responses.length; i++) {
            if (ai_responses[i].startsWith("http")) {
              const imageUrl = ai_responses[i];
              ai_responses[i] = `<img src="${imageUrl}" alt="Generated image" class="generated-image">`;
            }
          }
  
          ai_responses.forEach((ai_response) => {
            if (!isTyping) {
              isTyping = true;
              if (ai_response.startsWith("http")) {
                const imageUrl = ai_response;
                $responseColumn.find('.placeholder-message').remove(); // Remove the placeholder chat bubble
  
                appendImageMessage(imageUrl);
                isTyping = false;
                displayNextResponse();
              } else {
                $responseColumn.find('.placeholder-message').remove(); // Remove the placeholder chat bubble
                appendAIMessage(ai_response.trim());
              }
            } else {
              responseQueue.push({ response: ai_response.trim() });
              displayNextResponse();
            }
          });
          await localforage.setItem("conversationHistory", conversationHistory);
        },
      });
    }
  }  

  // Event listeners for user input and interaction
  $prompt.on("keydown", async function (e) {
    if (e.which === 13) {
      e.preventDefault();
  
      $("#send-button").addClass("animated");
  
      await handleSubmit();
  
      setTimeout(function () {
        $("#send-button").removeClass("active animated");
        $("#send-button").css("color", "#5A5A5A"); // Default color
      }, 1000);
    }
  });
  
  $prompt.on("input", function () {
    const primaryColor = getComputedStyle(document.documentElement).getPropertyValue("--primary-color");
  
    if ($(this).text().trim() !== '') { // If the input is not empty
      $("#send-button").addClass("active");
      $("#send-button").css("color", primaryColor);
    } else { // If the input is empty
      $("#send-button").removeClass("active");
      $("#send-button").css("color", "#5A5A5A"); // Default color
    }
  });
  
  $("#send-button").on("click touchstart", async function (e) {
    e.preventDefault();
    
    if ($prompt.text().trim() !== '') { // If the prompt is not empty
      await handleSubmit();
      $prompt.focus();
  
      $("#send-button").addClass("animated");
  
      setTimeout(function () {
        $("#send-button").removeClass("active animated");
        $("#send-button").css("color", "#5A5A5A"); // Default color
      }, 1000);
    }
  });

  document.getElementById("prompt").addEventListener("focus", function () {
    this.setAttribute("placeholder", "");
  });
  document.getElementById("prompt").addEventListener("blur", function () {
    if (this.value === "") {
      this.setAttribute("placeholder", "Type a message");
    }
  });

  $(document).on('click', '.generated-image, .uploaded-image', function(event) {
    event.stopPropagation();
    event.preventDefault();
    var imgSrc = $(this).attr('src');
    $('#lightbox img').attr('src', imgSrc);
    $('#lightbox').show();
  });
  
  $(document).on('click', '#lightbox', function(e) {
    if (e.target !== this) {
      // if the image itself is clicked, do nothing
      if (e.target === $('#lightbox img')[0]) {
        e.preventDefault(); // prevent default event
      }
      return;
    }
    e.stopPropagation();
    $('#lightbox').hide();
  });  

  //Camera button 
  $("#camera-button").on("click touchstart", async function (e) {
    e.preventDefault();
  
    // Get the position and dimensions of the camera button
    var buttonPosition = $("#camera-button").offset();
    var buttonWidth = $("#camera-button").outerWidth();
    var buttonHeight = $("#camera-button").outerHeight();
  
    // Create a new div for the flash animation
    var flash = $('<div>').addClass('flash').css({
      width: 0,
      height: 0,
      top: buttonPosition.top + buttonHeight / 2,
      left: buttonPosition.left + buttonWidth / 2,
    });
  
    // Add the flash div to the body
    $('body').append(flash);
  
    // Animate the flash div
    anime({
      targets: flash.get(0),
      width: buttonWidth * 3,  // scale size based on your needs
      height: buttonHeight * 3, // scale size based on your needs
      top: buttonPosition.top + buttonHeight / 2 - buttonHeight * 1.5,
      left: buttonPosition.left + buttonWidth / 2 - buttonWidth * 1.5,
      opacity: [0.8, 0],
      duration: 500,
      easing: 'easeOutExpo',
      complete: function() {
        // Remove the flash div when the animation completes
        flash.remove();
      }
    });
  
    $prompt.text("📷"); // set input field value
    await handleSubmit();
    $prompt.text(""); // clear input field after sending
  });  

  //Scroll on prompt focus
  $prompt.on("focus", function () {
    setTimeout(function () {
      responseColumnElement.scrollTo({
        top: responseColumnElement.scrollHeight,
        behavior: "smooth"
      });
      setTimeout(() => {
        responseColumnElement.scrollTop = responseColumnElement.scrollHeight;
      }, 100);
    }, 0);
  });

  setTimeout(async function () {
    await handleSubmit(true);
  }, 0);
}); //close the jquery



jQuery(document).ready(function ($) {
  document.getElementById("image-upload-button").addEventListener("touchstart", function () {
    $('#image-upload').click();
  }, { passive: true });

  document.getElementById("image-upload-button").addEventListener("click", function () {
    $('#image-upload').click();
  }, { passive: true });

  $('#image-upload').on('change', function () {
    const file_data = $(this).prop('files')[0];
    const form_data = new FormData();
    const $responseColumn = $(".response-column");
    const responseColumnElement = $responseColumn[0];
    const photoUrl = myUserMeta.photo || "";
    const placeholderUrl = "https://t3.ftcdn.net/jpg/03/46/83/96/360_F_346839683_6nAPzbhpSkIpb8pmAwufkC7c5eD7wYws.jpg";
    form_data.append('action', 'upload_image');
    form_data.append('image_upload', file_data);
  
    console.log('Image upload in progress...');
    const imageUrl = URL.createObjectURL(file_data);
    const $imageMessage = $('<div class="message user-message"></div>'); // Create a container for user message and photo
    const photoElement = $('<img src="' + imageUrl + '" alt="Uploaded Image" class="uploaded-image">'); // User message photo
    const timestamp = new Date().toLocaleTimeString([], { hour: 'numeric', minute: '2-digit', hour12: true }).replace(/^0/, '');
    const userTimestampElement = $('<div class="timestamp user-timestamp">' + timestamp + '</div>');
  
    function scrollToBottom() {
      const responseColumnWrapper = $(".response-column-wrapper");
      const images = responseColumnWrapper.find('img');
      const lastImage = images.last()[0];
    
      if (lastImage && lastImage.complete) {
        // If the last image is already loaded, scroll immediately
        scrollToBottomAction();
      } else if (lastImage) {
        // If the last image is not yet loaded, wait for it to load
        lastImage.addEventListener('load', scrollToBottomAction);
      } else {
        // No image elements found, scroll immediately
        scrollToBottomAction();
      }
    }
    
    function scrollToBottomAction() {
      const responseColumnWrapper = $(".response-column-wrapper");
      if (window.innerWidth >= 768) {
        // Smooth scrolling animation for non-mobile devices
        responseColumnWrapper[0].scrollTo({
          top: responseColumnWrapper[0].scrollHeight,
          behavior: "smooth"
        });
      } else {
        // Instant scroll to the bottom for mobile devices
        responseColumnWrapper[0].scrollTop = responseColumnWrapper[0].scrollHeight;
      }
    }    

    $imageMessage.append(photoElement); // Append photo to user message container
    $imageMessage.append(userTimestampElement); // Append timestamp as a child of user message container
  
    $responseColumn.append($imageMessage);
    scrollToBottom();
  
  //  conversationHistory += '[Image Link: ' + imageUrl + ']\n';
  //  console.log(conversationHistory);
  
    $.ajax({
      url: myChatAjax.ajaxurl,
      type: 'POST',
      data: form_data,
      contentType: false,
      processData: false,
      beforeSend: function () { },
      success: function (response) {
        if (response.success) {
          console.log('Image upload successful. Image URL:', response.data.file_url);
  
          if (response.data.openai_response) {
            const timestamp = new Date().toLocaleTimeString([], { hour: 'numeric', minute: '2-digit', hour12: true }).replace(/^0/, '');
            const aiTimestampElement = $('<div class="timestamp ai-timestamp">' + timestamp + '</div>');
            const aiMessageElement = $('<div class="message ai-message"></div>'); // Create a container for AI message and photo
            const aiTextElement = $('<div class="ai-text">' + response.data.openai_response + '</div>'); // AI message text
            const aiPhotoElement = $('<img src="' + (photoUrl || placeholderUrl) + '" class="photo">'); // AI message photo
  
            aiMessageElement.append(aiPhotoElement); 
            aiMessageElement.append(aiTextElement); 
            aiMessageElement.append(aiTimestampElement); 
            $responseColumn.append(aiMessageElement);
            scrollToBottom();

            // Play the sound effect
            soundEffect.play();

            conversationHistory += '\n###Response: ' + response.data.openai_response + '\n';
            console.log(conversationHistory);
          }
  
          responseColumnElement.scrollTo({
            top: responseColumnElement.scrollHeight,
            behavior: "smooth"
          });
  
        } else {
          console.log('Image upload failed. Response:', response);
        }
      },
      error: function (xhr, status, error) {
        console.log('AJAX request failed. Status:', status, '. Error:', error);
      }
    });
  });  
});

// Lightbox download button
window.onload = function() {
  document.getElementById('lightbox-download-button').onclick = function() {
      var imgSrc = document.getElementById('generated-image').src;
      var fileName = imgSrc.substring(imgSrc.lastIndexOf('/') + 1);
      var link = document.createElement('a');
      link.href = imgSrc;
      link.download = fileName;
      link.click();
  };
};

//Hammer pinch & zoom JS
jQuery(document).ready(function($) {
  var image = document.getElementById('generated-image');
  var hammer = new Hammer(image);

  var posX = 0, posY = 0, scale = 1, last_scale, last_posX, last_posY;

  hammer.get('pinch').set({ enable: true });
  hammer.get('doubletap').set({ enable: true });

  hammer.on('pinchstart pinchmove', function(ev){
      if(ev.type == 'pinchstart'){
          last_scale = scale;
      }
      scale = Math.max(1, Math.min(last_scale * ev.scale, 10));
      image.style.transform = 'translate('+posX+'px,'+posY+'px) scale('+scale+')';
  });

  hammer.on('panstart panmove', function(ev){
      posX = last_posX + ev.deltaX;
      posY = last_posY + ev.deltaY;
      image.style.transform = 'translate('+posX+'px,'+posY+'px) scale('+scale+')';
  });

  hammer.on('hammer.input', function(ev) {
      if(ev.isFinal) {
          last_posX = posX;
          last_posY = posY;
      }
  });

  hammer.on('doubletap', function() {
      scale = 1;
      posX = 0;
      posY = 0;
      image.style.transform = 'translate(0px,0px) scale(1)';
  });
});

// Expanding prompt container
let rootFontSize = parseFloat(
  getComputedStyle(document.documentElement).fontSize
);
let prompt = document.getElementById("prompt");
let sendButton = document.querySelector("#send-button");

// Set an initial height for the prompt box in rem
prompt.style.height = "1.5rem";

prompt.addEventListener("input", function () {
  prompt.style.height = "auto";
  prompt.style.height = Math.min(prompt.scrollHeight / rootFontSize, 9) + "rem";
});

prompt.addEventListener("keydown", function (e) {
  if (e.key === "Enter" && !e.shiftKey) {
    e.preventDefault();
    // Add your send message logic here
    prompt.style.height = "1.5rem"; // Reset the height after sending the message in rem
  }
});

sendButton.addEventListener(
  "click",
  function () {
    prompt.value = "";
    prompt.style.height = "1.5rem"; // Reset the height after sending the message in rem
  },
  false
);

//Home button
let goHomeButton = document.querySelector(".go-home-icon");

goHomeButton.addEventListener("click", function () {
  // Redirect to the home page
  window.location.replace("<?php echo esc_url(home_url()); ?>");
}, false);
