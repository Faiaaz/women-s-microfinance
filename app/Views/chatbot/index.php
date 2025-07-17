<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Loan Assistant Chat</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .chat-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 600px;
            height: 80vh;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }

        .chat-header {
            background: linear-gradient(135deg, #4299e1 0%, #3182ce 100%);
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 20px 20px 0 0;
        }

        .chat-header h1 {
            font-size: 1.5rem;
            margin-bottom: 5px;
        }

        .chat-header p {
            opacity: 0.9;
            font-size: 0.9rem;
        }

        .chat-messages {
            flex: 1;
            padding: 20px;
            overflow-y: auto;
            background: #f8fafc;
        }

        .message {
            margin-bottom: 20px;
            display: flex;
            align-items: flex-start;
        }

        .message.user {
            justify-content: flex-end;
        }

        .message.bot {
            justify-content: flex-start;
        }

        .message-content {
            max-width: 80%;
            padding: 15px 20px;
            border-radius: 20px;
            line-height: 1.5;
            position: relative;
            white-space: pre-line;
        }

        .message.user .message-content {
            background: linear-gradient(135deg, #4299e1 0%, #3182ce 100%);
            color: white;
            border-bottom-right-radius: 5px;
        }

        .message.bot .message-content {
            background: white;
            color: #2d3748;
            border: 1px solid #e2e8f0;
            border-bottom-left-radius: 5px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        .message-avatar {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            margin: 0 10px;
        }

        .message.user .message-avatar {
            background: linear-gradient(135deg, #4299e1 0%, #3182ce 100%);
            color: white;
            order: 2;
        }

        .message.bot .message-avatar {
            background: linear-gradient(135deg, #48bb78 0%, #38a169 100%);
            color: white;
        }

        .quick-options {
            padding: 15px 20px;
            background: white;
            border-top: 1px solid #e2e8f0;
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .quick-options button {
            background: linear-gradient(135deg, #f7fafc 0%, #edf2f7 100%);
            border: 2px solid #e2e8f0;
            border-radius: 25px;
            padding: 12px 20px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 0.9rem;
            color: #2d3748;
            font-weight: 500;
        }

        .quick-options button:hover {
            background: linear-gradient(135deg, #4299e1 0%, #3182ce 100%);
            color: white;
            border-color: #3182ce;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(66, 153, 225, 0.3);
        }

        .chat-input {
            padding: 20px;
            background: white;
            border-top: 1px solid #e2e8f0;
            display: flex;
            gap: 10px;
        }

        .chat-input input {
            flex: 1;
            padding: 15px 20px;
            border: 2px solid #e2e8f0;
            border-radius: 25px;
            font-size: 1rem;
            outline: none;
            transition: border-color 0.3s ease;
        }

        .chat-input input:focus {
            border-color: #4299e1;
        }

        .chat-input button {
            background: linear-gradient(135deg, #4299e1 0%, #3182ce 100%);
            color: white;
            border: none;
            padding: 15px 25px;
            border-radius: 25px;
            cursor: pointer;
            font-size: 1rem;
            font-weight: bold;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .chat-input button:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(66, 153, 225, 0.4);
        }

        .back-btn {
            position: absolute;
            top: 20px;
            left: 20px;
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 20px;
            cursor: pointer;
            text-decoration: none;
            font-size: 0.9rem;
            backdrop-filter: blur(10px);
            transition: background 0.3s ease;
        }

        .back-btn:hover {
            background: rgba(255, 255, 255, 0.3);
        }

        .typing-indicator {
            display: none;
            padding: 15px 20px;
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 20px;
            border-bottom-left-radius: 5px;
            margin-bottom: 20px;
            max-width: 80%;
        }

        .typing-dots {
            display: flex;
            gap: 5px;
        }

        .typing-dots span {
            width: 8px;
            height: 8px;
            background: #4299e1;
            border-radius: 50%;
            animation: typing 1.4s infinite ease-in-out;
        }

        .typing-dots span:nth-child(1) { animation-delay: -0.32s; }
        .typing-dots span:nth-child(2) { animation-delay: -0.16s; }

        @keyframes typing {
            0%, 80%, 100% { transform: scale(0.8); opacity: 0.5; }
            40% { transform: scale(1); opacity: 1; }
        }

        .finished-message {
            background: linear-gradient(135deg, #48bb78 0%, #38a169 100%);
            color: white;
            padding: 20px;
            border-radius: 15px;
            margin: 20px 0;
            text-align: center;
            font-weight: bold;
            box-shadow: 0 5px 15px rgba(72, 187, 120, 0.3);
        }

        @media (max-width: 768px) {
            .chat-container {
                height: 90vh;
                border-radius: 15px;
            }
            
            .chat-header {
                border-radius: 15px 15px 0 0;
            }
            
            .message-content {
                max-width: 90%;
            }
            
            .quick-options {
                padding: 10px 15px;
            }
            
            .quick-options button {
                font-size: 0.8rem;
                padding: 10px 15px;
            }
        }
    </style>
</head>
<body>
    <a href="<?= base_url() ?>" class="back-btn">← Back to Home</a>
    
    <div class="chat-container">
        <div class="chat-header">
            <h1>💬 Loan Assistant</h1>
            <p>Ask me anything about our loan programs and application process</p>
        </div>
        
        <div class="chat-messages" id="chatMessages">
            <!-- Messages will be added here -->
        </div>
        
        <div class="quick-options" id="quickOptions">
            <!-- Quick response buttons will be added here -->
        </div>
        
        <div class="chat-input">
            <input type="text" id="messageInput" placeholder="Type your message here..." maxlength="500">
            <button id="sendButton">Send</button>
        </div>
    </div>

    <script>
        let currentStep = 1;
        let isFinished = false;

        // Initialize chat
        window.onload = function() {
            sendMessage('', 1);
        };

        // Send message function
        function sendMessage(message, step = currentStep) {
            const chatMessages = document.getElementById('chatMessages');
            const quickOptions = document.getElementById('quickOptions');
            
            // Add user message if not initial load
            if (message.trim()) {
                addMessage(message, 'user');
            }

            // Show typing indicator
            showTypingIndicator();

            // Send request to server
            fetch('/chatbot/processMessage', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `message=${encodeURIComponent(message)}&step=${step}`
            })
            .then(response => response.json())
            .then(data => {
                hideTypingIndicator();
                addMessage(data.response, 'bot');
                currentStep = data.next_step;
                
                if (data.options && data.options.length > 0) {
                    showQuickOptions(data.options);
                } else {
                    hideQuickOptions();
                }

                if (data.finished) {
                    isFinished = true;
                    addFinishedMessage();
                }
            })
            .catch(error => {
                hideTypingIndicator();
                addMessage('Sorry, I encountered an error. Please try again.', 'bot');
                console.error('Error:', error);
            });
        }

        // Add message to chat
        function addMessage(message, sender) {
            const chatMessages = document.getElementById('chatMessages');
            const messageDiv = document.createElement('div');
            messageDiv.className = `message ${sender}`;
            
            const avatar = document.createElement('div');
            avatar.className = 'message-avatar';
            avatar.textContent = sender === 'user' ? '👤' : '🤖';
            
            const contentDiv = document.createElement('div');
            contentDiv.className = 'message-content';
            contentDiv.innerHTML = message.replace(/\n/g, '<br>');
            
            messageDiv.appendChild(avatar);
            messageDiv.appendChild(contentDiv);
            chatMessages.appendChild(messageDiv);
            
            // Scroll to bottom
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }

        // Show typing indicator
        function showTypingIndicator() {
            const chatMessages = document.getElementById('chatMessages');
            const typingDiv = document.createElement('div');
            typingDiv.className = 'typing-indicator';
            typingDiv.id = 'typingIndicator';
            typingDiv.innerHTML = `
                <div class="typing-dots">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
            `;
            chatMessages.appendChild(typingDiv);
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }

        // Hide typing indicator
        function hideTypingIndicator() {
            const typingIndicator = document.getElementById('typingIndicator');
            if (typingIndicator) {
                typingIndicator.remove();
            }
        }

        // Show quick options
        function showQuickOptions(options) {
            const quickOptions = document.getElementById('quickOptions');
            quickOptions.innerHTML = '';
            
            options.forEach(option => {
                const button = document.createElement('button');
                button.textContent = option;
                button.onclick = () => {
                    sendMessage(option);
                    hideQuickOptions();
                };
                quickOptions.appendChild(button);
            });
        }

        // Hide quick options
        function hideQuickOptions() {
            const quickOptions = document.getElementById('quickOptions');
            quickOptions.innerHTML = '';
        }

        // Add finished message
        function addFinishedMessage() {
            const chatMessages = document.getElementById('chatMessages');
            const finishedDiv = document.createElement('div');
            finishedDiv.className = 'finished-message';
            finishedDiv.innerHTML = '✨ Thank you for using our chatbot! A loan officer will contact you soon.';
            chatMessages.appendChild(finishedDiv);
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }

        // Event listeners
        document.getElementById('sendButton').addEventListener('click', function() {
            const input = document.getElementById('messageInput');
            const message = input.value.trim();
            
            if (message && !isFinished) {
                sendMessage(message);
                input.value = '';
            }
        });

        document.getElementById('messageInput').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                const message = this.value.trim();
                if (message && !isFinished) {
                    sendMessage(message);
                    this.value = '';
                }
            }
        });
    </script>
</body>
</html> 