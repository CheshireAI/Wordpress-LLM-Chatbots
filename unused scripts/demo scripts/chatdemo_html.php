<div id="lightbox" class="lightbox">
    <div class="lightbox-content">
        <a class="generated-link" id="generated-link" href="<?php echo plugin_dir_url(__FILE__) . 'images/default.jpg'; ?>" target="_blank">
            <img id="generated-image" src="<?php echo plugin_dir_url(__FILE__) . 'images/default.jpg'; ?>">
        </a>
        <button class="lightbox-download-button" id="lightbox-download-button" onclick="downloadImage()">
            <i class="fas fa-angle-double-down"></i>
        </button>
    </div>
</div>

<div class="chat-container">
    <div class="chat-viewport" id="chat-viewport">
        <div class="chat-column">
            <div class="chat-header">
                <div class="generated-name-container">
                <img class="header-photo" src="https://t3.ftcdn.net/jpg/03/46/83/96/360_F_346839683_6nAPzbhpSkIpb8pmAwufkC7c5eD7wYws.jpg" alt="Headshot">
                    <h3></h3>
                </div>
            </div>
            <div class="response-column-wrapper">
                <div class="response-column" id="responseColumn"></div>
            </div>
            <div class="prompt-container">
        <div class="prompt-input-container">
            <div class="left-icons">
                <button id="image-upload-button" class="input-group-text"><i class="fas fa-paperclip"></i></button>
                <input type="file" id="image-upload" accept="image/*" style="display: none;">
            </div>
            <div class="prompt-message-container">
                <div id="prompt" name="prompt" class="chat-textarea" contenteditable="true" placeholder="Type a message"></div>
            </div>
            <div class="right-icons">
                <button id="camera-button" class="input-group-text"><i class="fas fa-camera"></i></button>
                <button type="submit" id="send-button"><i class="fas fa-paper-plane"></i></button>
            </div>
        </div>
    </div>
</div>