jQuery(document).ready(async ($) => {

  const psychicNames = [
    "Cinder",
    "Kiki",
    "Foxy",
    "Coco",
    "Suna",
    "Lady",
    "Spook",
    "Sammie",
    "Scully",
    "Gypsa",
    "Frankie",
  ];
  let userScrolling = false;
  let isTyping = false;
  const responseQueue = [];

const getRandomPsychicName = () => {
    return psychicNames[Math.floor(Math.random() * psychicNames.length)];
  };

const updateChatHeader = (psychicName, logoUrl) => {
    $(".chat-header h2").text("ASK " + psychicName.toUpperCase());
    $(".chat-header .header-logo")
      .attr("src", logoUrl)
      .on("load", function () {
        $(".chat-header h2, .chat-header .header-logo").show();
      });
  };

// Define an array of colors
  const colors = [
    "#FFB6C1",
    "#8B008B",
    "#FF69B4",
    "#FFC0CB",
    "#DA70D6",
    "#9370DB",
    "#8B008B",
    "#4B0082",
    "#00CED1",
    "#1E90FF",
    "#87CEFA",
    "#00BFFF",
    "#FFA07A",
    "#FF7F50",
    "#FF6347",
  ];

// Select a random color from the array
  const randomColor = colors[Math.floor(Math.random() * colors.length)];

// Set the CSS variable with the random color
  document.documentElement.style.setProperty("--primary-color", randomColor);

//Refresh the page when the logo is clicked
  const refreshPage = async () => {
// Shrink the logo
  shrinkLogo();
// Clear the stored psychic in local storage
    await localforage.removeItem("psychicName");
// Refresh the page
    window.location.reload();
  };

  $(".chat-header img.header-logo").on("click touchstart", async (e) => {
    e.preventDefault();
    await refreshPage();
  });

function shrinkLogo() {
  const logo = document.querySelector('.header-logo');
  logo.style.transition = 'transform 0.2s ease-in-out';
  logo.style.transform = 'scale(0.8)';
  setTimeout(function() {
    logo.style.transition = 'transform 0.2s ease-in-out';
    logo.style.transform = 'scale(1)';
    setTimeout(function() {
      window.location.reload();
    }, 200);
  }, 200);
}

function typeMessage(element, message, index, interval, callback) {
    element.html(message);
    if (typeof callback === "function") {
        callback();
    }
    $(".response-column").animate(
        { scrollTop: $(".response-column")[0].scrollHeight },
        50
    );
}

function handleMessageReply(reply) {
  if (reply['role'] === 'system' && reply['content'].startsWith('You are now')) {
    // Handle system message
  } else if (reply['role'] === 'user') {
    // Handle user message
  } else {
    // Handle AI message
    var msg = reply['content'];
    var imgStartIndex = msg.indexOf('<img');

// Check if message contains an image
    if (imgStartIndex != -1) {
      var imgEndIndex = msg.indexOf('>', imgStartIndex);
      var imgTag = msg.substring(imgStartIndex, imgEndIndex+1);

// Add inline styles to the image
      var styledImgTag = imgTag.slice(0, -1) + ' style="max-width: 100%; height: auto;">';

// Replace the original image tag with the styled image tag
      msg = msg.replace(imgTag, styledImgTag);
    }

    addToTranscript(msg, 'ai');
  }
}

function scrollToBottom() {
    if (window.innerWidth >= 768) {
      // Smooth scrolling animation for non-mobile devices
      $(".response-column").animate(
        { scrollTop: $(".response-column")[0].scrollHeight },
        50
      );
    } else {
      // Instant scroll to the bottom for mobile devices
      $(".response-column").scrollTop($(".response-column")[0].scrollHeight);
    }
  }

function splitAIResponse(response) {
  // Split the response into lines, treating each line as a separate message
  const messages = response.split("\n").filter((line) => line.trim() !== "");
  return messages;
}

  async function handleSubmit(greeting = false) {
    let conversationHistory = "";
    var prompt = $("#prompt").val();
    var psychicName =
      (await localforage.getItem("psychicName")) || getRandomPsychicName();
    await localforage.setItem("psychicName", psychicName);

// Get the logo URL
    var logoUrl =
      "https://faenomena.com/wp-content/plugins/quote-gen/images/psychics/" +
      psychicName.toLowerCase() +
      ".jpg";

// Update the chat header with the psychic's name and logo
    updateChatHeader(psychicName, logoUrl);

let imagePromptPhrases = ['show me', 'send me', 'can i see'];
let imagePrompt = imagePromptPhrases.some(phrase => prompt.toLowerCase().includes(phrase));

const messageLimit = 25;
const messages = $(".response-column .message");
const messagesToSend = messages.slice(-messageLimit);

messagesToSend.each(function () {
const isUserMessage = $(this).hasClass("user-message");
const isAiMessage = $(this).hasClass("ai-message");

      if (isUserMessage) {
        conversationHistory += "User: " + $(this).text() + "\n";
      } else if (isAiMessage) {
        // Remove the psychic name and colon from the message
        const aiMessage = $(this).clone();
//        aiMessage.find(".logo").remove();
        const aiMessageText = aiMessage
          .text()
          .substring(psychicName.length + 2);
        conversationHistory += "AI: " + aiMessageText + "\n";
      }
    });
    if (prompt !== "" || greeting) {
      if (!greeting) {
        const userMessage = prompt;
        conversationHistory += `Q: ${userMessage}\n`;
        $(".response-column").append(
          '<div class="message user-message">' + userMessage + "</div>"
        );

        // Add scrolling animation for the user message
        $(".response-column").animate(
          { scrollTop: $(".response-column")[0].scrollHeight },
          50
        );
        $("#prompt").val("");
      }

$.ajax({
  url: encodeURI(myAjax4.ajaxurl),
  type: "POST",
  data: {
    action: "generate_chat4",
    prompt: encodeURIComponent(prompt),
    greeting: greeting,
    psychicName: psychicName,
    conversationHistory: encodeURIComponent(conversationHistory),
  },

success: async function (response) {
  console.log("AJAX request success: ", response);
  var ai_responses = [];
  if (response.success) {
    ai_responses = response.data;
  } else {
    ai_responses = [
      "I am currently busy casting spells and tending to my garden. Check back soon.",
    ];
  }

  // Process each AI response
  for (let i = 0; i < ai_responses.length; i++) {
    // If the response is an image URL, create an image element
    if (ai_responses[i].startsWith('http')) {
      let imgElement = '<img src="' + ai_responses[i] + '" alt="Generated image" class="generated-image">';
      ai_responses[i] = imgElement;
    }
  }

  async function displayNextResponse() {
    if (responseQueue.length > 0 && !isTyping) {
      isTyping = true;
      const nextResponse = responseQueue.shift();
      const logoUrl =
        "https://faenomena.com/wp-content/plugins/quote-gen/images/psychics/" +
        psychicName.toLowerCase() +
        ".jpg";

      // Check if the response is an image URL
      if (nextResponse.response.startsWith("http")) {
        const imageUrl = nextResponse.response;
        $(".response-column").append('<img src="' + imageUrl + '" class="generated-image" />');
      } else {
        const aiMessageElement = $(
          '<div class="message ai-message"><img src="' +
            logoUrl +
            '" class="logo"></div>'
        );

      if (imagePrompt) {
        // Push the image response to the queue
        responseQueue.push({ response: '<img src="' + response.data + '"/>' });
      }

        $(".response-column").append(aiMessageElement);
        typeMessage(aiMessageElement, nextResponse.response, 0, 0, () => {
          isTyping = false;
          displayNextResponse();
        });
      }
setTimeout(scrollToBottom, 800);

    }
  }

  ai_responses.forEach((ai_response) => {
    if (!isTyping) {
      isTyping = true;
      if (ai_response.startsWith("http")) {
 $(".response-column").append('<img src="' + imageUrl + '" class="generated-image" />');
      } else {
        const aiMessageElement = $(
          '<div class="message ai-message"><img src="' +
            logoUrl +
            '" class="logo"></div>'
        );
        $(".response-column").append(aiMessageElement);
        typeMessage(aiMessageElement, ai_response.trim(), 0, 0, () => {
          isTyping = false;
          displayNextResponse();
        });
      }
setTimeout(scrollToBottom, 800);

    } else {
      responseQueue.push({ response: ai_response.trim() });
      displayNextResponse();
    }

setTimeout(scrollToBottom, 800);

// Update the conversationHistory variable before setting it to sessionStorage
    conversationHistory += `A: ${ai_response.trim()}\n`;
  });

  await localforage.setItem("conversationHistory", conversationHistory);
},
      });
    }
  }

  $("#prompt").on("input", function () {
    // Add active class to send button
    $("#send-button").addClass("active");

    // Get the primary color from the CSS variable
    let primaryColor = getComputedStyle(
      document.documentElement
    ).getPropertyValue("--primary-color");

    // Add the primary color to the send button
    $("#send-button").css("color", primaryColor);
  });

//Lightbox open
$(document).on('click touchstart', '.generated-image', function(event) {
  event.preventDefault();
  var imgSrc = $(this).attr('src');
  $('#lightbox img').attr('src', imgSrc);
  $('#lightbox').show();
});

//Lightbox close
$(document).on('click', '#lightbox', function(e) {
  if(e.target !== this) 
    return;
  $('#lightbox').hide();
});

$("#send-button").on("click touchend", async function (e) {
  e.preventDefault();
  await handleSubmit();
  $("#prompt").focus();

  // Add animated class to send button only if it has active class
  if ($("#send-button").hasClass("active")) {
    $("#send-button").addClass("animated");

    // Remove active and animated classes after 1 second
    setTimeout(function () {
      $("#send-button").removeClass("active animated");

      // Reset the color of the send button to the default
      $("#send-button").css("color", "");
    }, 1000);
  }
});

  $("#prompt").on("keydown", async function (e) {
    if (e.which === 13) {
      e.preventDefault();
      await handleSubmit();
    }
  });

$("#prompt").on("focus", function () {
  setTimeout(function () {
    const responseColumn = $(".response-column");
    const scrollHeight = responseColumn[0].scrollHeight;
    responseColumn.scrollTop(scrollHeight);
    // Forcing a second scroll for better mobile support
    setTimeout(() => {
      responseColumn.scrollTop(scrollHeight);
    }, 100);
  }, 0);
});

  setTimeout(async function () {
    await handleSubmit(true);
  }, 0);
});

jQuery(document).ready(function () {
  const picker = new EmojiButton({
    position: "top-start",
    zIndex: 1000,
    autoTheme: true,
    style: "dark", // Set the style to 'dark'
  });

  picker.on("emoji", (emoji) => {
    const input = document.getElementById("prompt");
    input.value += emoji;
    input.focus();
  });

  document.getElementById("emoji-button").addEventListener("click", (event) => {
    picker.togglePicker(event.currentTarget);
  });
});

document.getElementById("prompt").addEventListener("focus", function () {
  this.setAttribute("placeholder", "");
});
document.getElementById("prompt").addEventListener("blur", function () {
  if (this.value === "") {
    this.setAttribute("placeholder", "Type a message");
  }
});