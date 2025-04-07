// chatbot.js

document.addEventListener("DOMContentLoaded", function () {
    console.log("Alisa Chatbot JS Loaded");

    // Initialize variables
    let userId = null;
    let sessionId = 'session_' + Math.random().toString(36).substr(2, 9);

    // Get DOM elements
    const chatIcon = document.querySelector('.alisa-chat-icon');
    const chatWindow = document.querySelector('.alisa-chat-window');
    const chatOverlay = document.querySelector('.alisa-overlay');
    const userInfoSection = document.querySelector('.alisa-user-info');
    const chatContainer = document.querySelector('.alisa-chat-container');
    const submitButton = document.querySelector('.alisa-submit-user');
    const skipButton = document.querySelector('.alisa-skip-user');
    const chatInput = document.getElementById('alisa-chat-input');
    const sendButton = document.getElementById('alisa-send-btn');
    const closeButton = document.querySelector('.alisa-close-chat');

    // Show/hide chat window
    chatIcon.addEventListener('click', function() {
        chatWindow.style.display = 'block';
        chatOverlay.classList.add('show');
    });

    closeButton.addEventListener('click', function() {
        chatWindow.style.display = 'none';
        chatOverlay.classList.remove('show');
    });

    // Handle user info submission
    function saveUserInfo(name, email) {
        jQuery.ajax({
            url: alisa_ajax_object.ajax_url,
            method: 'POST',
            data: {
                action: 'alisa_save_user',
                name: name,
                email: email,
                session_id: sessionId,
                nonce: alisa_ajax_object.nonce
            },
            success: function(response) {
                if (response.success) {
                    userId = response.data.user_id;
                    userInfoSection.style.display = 'none';
                    chatContainer.style.display = 'block';
                    chatInput.disabled = false;
                    sendButton.disabled = false;
                    chatInput.focus();
                } else {
                    console.error('Error:', response.data.message);
                    alert('Error: ' + response.data.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', error);
                alert('Error saving user information');
            }
        });
    }

    // Handle message sending
    function sendMessage(message) {
        if (!message.trim()) return;

        // Add user message to chat
        const userMessageHtml = `<div class="alisa-user-message">${message}</div>`;
        chatContainer.insertAdjacentHTML('beforeend', userMessageHtml);

        jQuery.ajax({
            url: alisa_ajax_object.ajax_url,
            method: 'POST',
            data: {
                action: 'alisa_chat_message',
                message: message,
                user_id: userId,
                session_id: sessionId,
                nonce: alisa_ajax_object.nonce
            },
            success: function(response) {
                if (response.success) {
                    const botMessageHtml = `<div class="alisa-bot-message">${response.data.response}</div>`;
                    chatContainer.insertAdjacentHTML('beforeend', botMessageHtml);
                } else {
                    const errorHtml = `<div class="alisa-bot-message error">Sorry, I couldn't process your message.</div>`;
                    chatContainer.insertAdjacentHTML('beforeend', errorHtml);
                }
                chatContainer.scrollTop = chatContainer.scrollHeight;
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', error);
                const errorHtml = `<div class="alisa-bot-message error">Sorry, I couldn't process your message.</div>`;
                chatContainer.insertAdjacentHTML('beforeend', errorHtml);
                chatContainer.scrollTop = chatContainer.scrollHeight;
            }
        });

        chatInput.value = '';
    }

    // Event Listeners
    submitButton.addEventListener('click', function(e) {
        e.preventDefault();
        const nameInput = document.querySelector('#alisa-user-name');
        const emailInput = document.querySelector('#alisa-user-email');
        
        if (!nameInput.value.trim() || !emailInput.value.trim()) {
            alert('Please enter both name and email or click Skip.');
            return;
        }

        saveUserInfo(nameInput.value.trim(), emailInput.value.trim());
    });

    skipButton.addEventListener('click', function(e) {
        e.preventDefault();
        saveUserInfo('Guest', 'guest@example.com');
    });

    sendButton.addEventListener('click', function() {
        sendMessage(chatInput.value);
    });

    chatInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            sendMessage(chatInput.value);
        }
    });
});