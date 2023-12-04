jQuery(document).ready(function($) {
  let chatBoxVisible = false;

  let chatBoxOpenAnimation = anime({
    targets: '.chatbutton-container',
    scale: [0, 1],
    opacity: [0, 1],
    duration: 800,
    easing: 'easeOutCubic',
    begin: function() {
      $('.chatbutton-container').css('display', 'block'); // set to block just before starting the animation
      if ($(window).width() < 768) { // If mobile device
        $('body').css('overflow', 'hidden'); // prevent body from scrolling
      }
    },
    autoplay: false
  });

  let chatBoxCloseAnimation = anime({
    targets: '.chatbutton-container',
    scale: [1, 0],
    opacity: [1, 0],
    duration: 800,
    easing: 'easeInCubic',
    complete: function() {
      $('.chatbutton-container').css('display', 'none'); // set to none after the animation completes
      $(".open-chat-icon").show(); // show the open chat icon after the closing animation is done
      if ($(window).width() < 768) { // If mobile device
        $('body').css('overflow', 'auto'); // allow body to scroll
      }
    },
    autoplay: false
  });

  $(".open-chat-icon").on("click", function() {
    if (!chatBoxVisible) {
      chatBoxOpenAnimation.restart();
      chatBoxOpenAnimation.play();
      $(this).hide(); // hide the open chat icon
      chatBoxVisible = true;
    }
  });

  $(".close-chat-icon").on("click", function() {
    if (chatBoxVisible) {
      chatBoxCloseAnimation.restart();
      chatBoxCloseAnimation.play();
      chatBoxVisible = false;
    }
  });
});