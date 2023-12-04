<div id="lightbox" class="lightbox">
    <div class="lightbox-content">
        <a class="generated-link" id="generated-link" href=""></a>
        <img id="generated-image" src="">
        <button class="lightbox-download-button" id="lightbox-download-button" onclick="downloadImage()" title="Download Image">
            <i class="fas fa-angle-double-down"></i>
        </button>
        <button class="lightbox-close-button" id="lightbox-close-button" onclick="closeLightbox()" title="Close">
            <i class="fas fa-times"></i>
        </button>
    </div>
</div>

<button class="go-home-icon" id="go-home-button" title="Go Home">
    <i class="fas fa-home"></i>
</button>

<div class="chat-background">
    <img class="background-photo" src="https://t3.ftcdn.net/jpg/03/46/83/96/360_F_346839683_6nAPzbhpSkIpb8pmAwufkC7c5eD7wYws.jpg" alt="Background Image">
    <div class="character-name">
        <h3></h3>
    </div>
</div>

<div class="chat-container">
    <div class="chat-viewport" id="chat-viewport">
        <div class="chat-column">
            <div class="chat-header">
                <div class="generated-name-container">
                    <img class="header-photo" src="https://t3.ftcdn.net/jpg/03/46/83/96/360_F_346839683_6nAPzbhpSkIpb8pmAwufkC7c5eD7wYws.jpg" alt="Headshot">
                </div>
            </div>
            <div class="response-column-wrapper">
                <div class="response-column" id="responseColumn"></div>
            </div>
            <div class="prompt-container">
                <div class="prompt-input-container">
                    <div class="left-icons">
                        <button id="image-upload-button" class="input-group-text" title="Upload Image"><i class="fas fa-paperclip"></i></button>
                        <input type="file" id="image-upload" accept="image/*" style="display: none;">
                    </div>
                    <div class="prompt-message-container">
                        <div id="prompt" name="prompt" class="chat-textarea" contenteditable="true"></div>
                    </div>
                    <div class="right-icons">
                        <button id="camera-button" class="input-group-text" title="Request a Photo"><i class="fas fa-camera"></i></button>
                        <button type="submit" id="send-button" title="Send Message"><i class="fas fa-paper-plane"></i></button>
                    </div>
                </div>
            </div>
        </div>