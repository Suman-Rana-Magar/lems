<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>AI Chat</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
        }
    </script>

    <style>
        @keyframes slideInUp {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }

        @keyframes pulse-glow {
            0%, 100% {
                box-shadow: 0 0 20px rgba(99, 102, 241, 0.3);
            }
            50% {
                box-shadow: 0 0 30px rgba(99, 102, 241, 0.6);
            }
        }

        .message-bubble {
            animation: slideInUp 0.3s ease-out;
        }

        .messages::-webkit-scrollbar {
            height: 8px;
            width: 8px;
        }

        .messages::-webkit-scrollbar-track {
            background: transparent;
        }

        .messages::-webkit-scrollbar-thumb {
            background-color: rgba(255, 255, 255, 0.2);
            border-radius: 999px;
        }

        .messages::-webkit-scrollbar-thumb:hover {
            background-color: rgba(255, 255, 255, 0.3);
        }

        .image-preview {
            max-width: 300px;
            max-height: 300px;
            border-radius: 12px;
            object-fit: cover;
            cursor: pointer;
            transition: transform 0.2s;
        }

        .image-preview:hover {
            transform: scale(1.05);
        }

        .upload-btn {
            position: relative;
            overflow: hidden;
        }

        .upload-btn input[type="file"] {
            position: absolute;
            opacity: 0;
            width: 100%;
            height: 100%;
            cursor: pointer;
        }

        .gradient-border {
            position: relative;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 2px;
            border-radius: 12px;
        }

        .gradient-border::before {
            content: '';
            position: absolute;
            inset: 2px;
            background: #1a1a1a;
            border-radius: 10px;
            z-index: -1;
        }

        .glass-effect {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .hover-lift {
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .hover-lift:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
        }
    </style>
</head>

<body class="bg-gray-100 dark:bg-gray-900 min-h-screen flex items-center justify-center p-4 transition-colors duration-200">

    <div class="w-full max-w-3xl bg-white dark:bg-gray-800 shadow-2xl rounded-2xl overflow-hidden h-[80vh] flex flex-col border border-gray-300 dark:border-gray-700 transition-colors duration-200">

        <!-- Header -->
        <header class="flex items-center justify-between p-4 border-b border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 transition-colors duration-200">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-full bg-gradient-to-br from-indigo-600 via-purple-600 to-pink-600 flex items-center justify-center text-white font-bold shadow-lg hover-lift pulse-glow">
                    AI
                </div>
                <div>
                    <h1 class="text-lg font-semibold text-gray-900 dark:text-white bg-gradient-to-r from-indigo-600 to-purple-600 dark:from-indigo-400 dark:to-purple-400 bg-clip-text text-transparent">Assistant</h1>
                    <p class="text-sm text-gray-600 dark:text-gray-300">Ask anything, your helper AI...</p>
                </div>
            </div>

            <div class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-300">
                <div class="hidden md:flex items-center gap-2">
                    <div class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></div>
                    <span>Status: <strong class="text-green-600 dark:text-green-400">Online</strong></span>
                </div>
                <button id="theme-toggle" class="p-2 rounded-md hover:bg-gray-100 dark:hover:bg-gray-700 transition-all hover-lift" title="Toggle theme">
                    <svg id="theme-icon-light" class="hidden w-5 h-5 text-gray-700 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>
                    <svg id="theme-icon-dark" class="w-5 h-5 text-gray-300 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path>
                    </svg>
                </button>
                <button id="clear-btn" class="px-3 py-1 border border-gray-300 dark:border-gray-600 rounded-md hover:bg-gray-100 dark:hover:bg-gray-700 hover:border-indigo-500 dark:hover:border-indigo-500 transition-all hover-lift text-gray-700 dark:text-gray-300">
                    Clear
                </button>
            </div>
        </header>

        <!-- Messages -->
        <main class="flex-1 overflow-hidden">
            <div class="messages p-4 space-y-4 h-full overflow-y-auto">
                <!-- Messages will be dynamically added here -->
            </div>
        </main>

        <!-- Composer -->
        <footer class="p-4 border-t border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 transition-colors duration-200">
            <!-- Image Preview -->
            <div id="image-preview-container" class="mb-3 hidden">
                <div class="flex items-center gap-2 p-2 bg-gray-50 dark:bg-gray-700 rounded-lg border border-gray-200 dark:border-gray-600 transition-colors duration-200">
                    <img id="preview-image" src="" alt="Preview" class="image-preview">
                    <button type="button" id="remove-image" class="ml-auto p-1 rounded-full hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-600 dark:text-gray-300 hover:text-red-500 dark:hover:text-red-400 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>

            <form id="chat-form" class="flex items-end gap-3" onsubmit="return false;">
                <div class="upload-btn relative p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-all hover-lift">
                    <input type="file" id="image-upload" accept="image/*" class="absolute inset-0 opacity-0 cursor-pointer">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>

                <textarea
                    id="message"
                    rows="1"
                    placeholder="Type your message..."
                    class="flex-1 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-200 placeholder-gray-500 dark:placeholder-gray-400 px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all resize-none overflow-hidden min-h-[48px] max-h-[200px]"
                    style="line-height: 1.5;"></textarea>

                <button id="send-btn" type="submit" class="bg-gradient-to-r from-indigo-600 to-purple-600 text-white px-6 py-3 rounded-xl hover:from-indigo-700 hover:to-purple-700 shadow-lg hover:shadow-indigo-500/50 transition-all hover-lift font-medium">
                    <span class="flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                        </svg>
                        Send
                    </span>
                </button>
            </form>
        </footer>

    </div>

    <script>
        const form = document.getElementById('chat-form');
        const messageInput = document.getElementById('message');
        const messagesEl = document.querySelector('.messages');
        const imageUpload = document.getElementById('image-upload');
        const imagePreviewContainer = document.getElementById('image-preview-container');
        const previewImage = document.getElementById('preview-image');
        const removeImageBtn = document.getElementById('remove-image');
        const clearBtn = document.getElementById('clear-btn');
        let selectedImage = null;
        let selectedImageFile = null;
        
        // Conversation history for context
        let conversationHistory = [];

        // Theme management
        const themeToggle = document.getElementById('theme-toggle');
        const themeIconLight = document.getElementById('theme-icon-light');
        const themeIconDark = document.getElementById('theme-icon-dark');

        // Get current theme (default to dark)
        function getCurrentTheme() {
            return localStorage.getItem('theme') || 'dark';
        }

        // Apply theme
        function applyTheme(theme) {
            const html = document.documentElement;
            html.classList.remove('light', 'dark');
            html.classList.add(theme);
            updateThemeIcon(theme);
        }

        // Update theme icon
        function updateThemeIcon(theme) {
            if (theme === 'light') {
                themeIconLight.classList.remove('hidden');
                themeIconDark.classList.add('hidden');
            } else {
                themeIconDark.classList.remove('hidden');
                themeIconLight.classList.add('hidden');
            }
        }

        // Toggle theme
        function toggleTheme() {
            const current = getCurrentTheme();
            const nextTheme = current === 'light' ? 'dark' : 'light';
            localStorage.setItem('theme', nextTheme);
            applyTheme(nextTheme);
        }

        // Initialize theme (default to dark)
        const savedTheme = getCurrentTheme();
        applyTheme(savedTheme);

        // Theme toggle button click
        themeToggle.addEventListener('click', toggleTheme);

        // Auto-resize textarea
        function autoResizeTextarea(textarea) {
            textarea.style.height = 'auto';
            const scrollHeight = textarea.scrollHeight;
            const maxHeight = 200; // max-h-[200px]
            const minHeight = 48; // min-h-[48px]
            
            if (scrollHeight > maxHeight) {
                textarea.style.height = maxHeight + 'px';
                textarea.style.overflowY = 'auto';
            } else {
                textarea.style.height = Math.max(scrollHeight, minHeight) + 'px';
                textarea.style.overflowY = 'hidden';
            }
        }

        // Initialize textarea height
        autoResizeTextarea(messageInput);

        // Auto-resize on input and paste
        messageInput.addEventListener('input', () => {
            autoResizeTextarea(messageInput);
        });

        messageInput.addEventListener('paste', () => {
            // Use setTimeout to ensure paste content is processed first
            setTimeout(() => {
                autoResizeTextarea(messageInput);
            }, 0);
        });

        // Handle Enter key (send on Enter, new line on Shift+Enter)
        messageInput.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                form.dispatchEvent(new Event('submit'));
            }
        });

        // Get current time in a nice format
        function getCurrentTime() {
            const now = new Date();
            const hours = now.getHours();
            const minutes = now.getMinutes();
            const ampm = hours >= 12 ? 'PM' : 'AM';
            const displayHours = hours % 12 || 12;
            const displayMinutes = minutes.toString().padStart(2, '0');
            return `${displayHours}:${displayMinutes} ${ampm}`;
        }

        // Image upload handler
        imageUpload.addEventListener('change', (e) => {
            const file = e.target.files[0];
            if (file) {
                if (!file.type.startsWith('image/')) {
                    alert('Please select an image file.');
                    imageUpload.value = '';
                    return;
                }

                // Check file size (max 5MB)
                if (file.size > 5 * 1024 * 1024) {
                    alert('Image size should be less than 5MB.');
                    imageUpload.value = '';
                    return;
                }

                selectedImageFile = file;
                const reader = new FileReader();
                reader.onload = (event) => {
                    selectedImage = event.target.result;
                    previewImage.src = selectedImage;
                    imagePreviewContainer.classList.remove('hidden');
                };
                reader.readAsDataURL(file);
            }
        });

        // Remove image
        removeImageBtn.addEventListener('click', () => {
            selectedImage = null;
            selectedImageFile = null;
            imageUpload.value = '';
            imagePreviewContainer.classList.add('hidden');
            previewImage.src = '';
        });

        // Clear chat
        clearBtn.addEventListener('click', () => {
            if (confirm('Are you sure you want to clear all messages?')) {
                messagesEl.innerHTML = '';
                conversationHistory = [];
            }
        });

        // Form submit handler
        form.addEventListener('submit', async (e) => {
            e.preventDefault();

            const text = messageInput.value.trim();
            const imageToSend = selectedImage; // Store before clearing
            const imageFileToSend = selectedImageFile; // Store file before clearing
            if (!text && !imageToSend) return;

            appendUserBubble(text, imageToSend);
            
            // Add user message to history
            const userMessage = {
                role: 'user',
                content: text || (imageToSend ? 'Describe this image.' : '')
            };
            if (imageToSend) {
                // Remove data URL prefix for storage
                const base64Image = imageToSend.replace(/^data:image\/[a-z]+;base64,/, '');
                userMessage.images = [base64Image];
            }
            conversationHistory.push(userMessage);

            messageInput.value = '';
            autoResizeTextarea(messageInput); // Reset textarea height
            selectedImage = null;
            selectedImageFile = null;
            imagePreviewContainer.classList.add('hidden');
            imageUpload.value = '';
            previewImage.src = '';

            const typing = showTypingIndicator();

            try {
                const payload = {
                    message: text || (imageToSend ? 'Describe this image.' : ''),
                    history: conversationHistory.slice(0, -1) // Send history without current message
                };
                
                // Include image as base64 if available
                if (imageToSend) {
                    payload.image = imageToSend;
                }

                const res = await fetch("/chat", {
                    method: "POST",
                    headers: {
                        "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content,
                        "Content-Type": "application/json"
                    },
                    body: JSON.stringify(payload)
                });

                const data = await res.json();
                removeTypingIndicator(typing);

                // Add AI response to history
                conversationHistory.push({
                    role: 'assistant',
                    content: data.ai
                });

                appendAIBubble(formatMarkdownResponse(data.ai));

            } catch (err) {
                removeTypingIndicator(typing);
                const errorMsg = "Something went wrong. Please try again.";
                conversationHistory.push({
                    role: 'assistant',
                    content: errorMsg
                });
                appendAIBubble(errorMsg);
            }

            messagesEl.scrollTop = messagesEl.scrollHeight;
        });

        // Format markdown response with proper HTML rendering
        function formatMarkdownResponse(text) {
            if (!text) return '';
            
            // Trim the text first
            text = text.trim();
            
            // Split by code blocks first to preserve them
            const parts = text.split(/(```[\s\S]*?```)/g);
            let html = '';
            
            parts.forEach((part) => {
                if (part.startsWith('```') && part.endsWith('```')) {
                    // Code block
                    const match = part.match(/```(\w+)?\n?([\s\S]*?)```/);
                    const code = match ? match[2].trim() : part.replace(/```/g, '').trim();
                    html += `<pre class="bg-gray-200 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded-lg p-4 my-2 overflow-x-auto"><code class="text-sm text-gray-800 dark:text-gray-300 font-mono">${escapeHtml(code)}</code></pre>`;
                } else if (part.trim()) {
                    // Regular text - process markdown
                    let processed = part.trim();
                    
                    // Headers first (before other processing)
                    processed = processed.replace(/^### (.*$)/gm, '<h3 class="text-lg font-semibold text-gray-900 dark:text-white mt-2 mb-1">$1</h3>');
                    processed = processed.replace(/^## (.*$)/gm, '<h2 class="text-xl font-semibold text-gray-900 dark:text-white mt-2 mb-1">$1</h2>');
                    processed = processed.replace(/^# (.*$)/gm, '<h1 class="text-2xl font-bold text-gray-900 dark:text-white mt-2 mb-1">$1</h1>');
                    
                    // Process lists - handle before escaping HTML
                    const lines = processed.split('\n');
                    let inList = false;
                    let listType = null;
                    let result = [];
                    
                    lines.forEach((line) => {
                        const trimmed = line.trim();
                        if (!trimmed) {
                            if (inList) {
                                result.push(`</${listType}>`);
                                inList = false;
                                listType = null;
                            }
                            return;
                        }
                        
                        // Check if already HTML (header)
                        if (trimmed.startsWith('<h')) {
                            if (inList) {
                                result.push(`</${listType}>`);
                                inList = false;
                                listType = null;
                            }
                            result.push(trimmed);
                            return;
                        }
                        
                        const unorderedMatch = trimmed.match(/^[-*+]\s+(.+)$/);
                        const orderedMatch = trimmed.match(/^(\d+)\.\s+(.+)$/);
                        
                        if (unorderedMatch) {
                            if (!inList || listType !== 'ul') {
                                if (inList) {
                                    result.push(`</${listType}>`);
                                }
                                result.push('<ul class="list-disc ml-6 my-1 space-y-0.5">');
                                inList = true;
                                listType = 'ul';
                            }
                            result.push(`<li class="text-gray-800 dark:text-gray-200">${unorderedMatch[1]}</li>`);
                        } else if (orderedMatch) {
                            if (!inList || listType !== 'ol') {
                                if (inList) {
                                    result.push(`</${listType}>`);
                                }
                                result.push('<ol class="list-decimal ml-6 my-1 space-y-0.5">');
                                inList = true;
                                listType = 'ol';
                            }
                            result.push(`<li class="text-gray-800 dark:text-gray-200">${orderedMatch[2]}</li>`);
                        } else {
                            if (inList) {
                                result.push(`</${listType}>`);
                                inList = false;
                                listType = null;
                            }
                            result.push(trimmed);
                        }
                    });
                    
                    if (inList) {
                        result.push(`</${listType}>`);
                    }
                    
                    processed = result.join('\n');
                    
                    // Now escape HTML for safety (but preserve already created HTML tags)
                    const htmlTags = [];
                    processed = processed.replace(/(<[^>]+>)/g, (match) => {
                        htmlTags.push(match);
                        return `__HTML_TAG_${htmlTags.length - 1}__`;
                    });
                    
                    processed = escapeHtml(processed);
                    
                    // Restore HTML tags
                    htmlTags.forEach((tag, index) => {
                        processed = processed.replace(`__HTML_TAG_${index}__`, tag);
                    });
                    
                    // Inline code
                    processed = processed.replace(/`([^`\n]+)`/g, '<code class="bg-gray-200 dark:bg-gray-900 border border-gray-300 dark:border-gray-700 rounded px-1.5 py-0.5 text-sm text-indigo-700 dark:text-indigo-300 font-mono">$1</code>');
                    
                    // Bold (**text** or __text__)
                    processed = processed.replace(/\*\*([^*]+)\*\*/g, '<strong class="font-semibold text-gray-900 dark:text-white">$1</strong>');
                    processed = processed.replace(/__(.+?)__/g, '<strong class="font-semibold text-gray-900 dark:text-white">$1</strong>');
                    
                    // Italic (*text* or _text_ but not **)
                    processed = processed.replace(/(?<!\*)\*([^*]+)\*(?!\*)/g, '<em class="italic text-gray-700 dark:text-gray-300">$1</em>');
                    processed = processed.replace(/(?<!_)_([^_]+)_(?!_)/g, '<em class="italic text-gray-700 dark:text-gray-300">$1</em>');
                    
                    // Links [text](url)
                    processed = processed.replace(/\[([^\]]+)\]\(([^\)]+)\)/g, '<a href="$2" target="_blank" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300 underline">$1</a>');
                    
                    // Horizontal rules
                    processed = processed.replace(/^[-*]{3,}$/gm, '<hr class="border-t border-gray-300 dark:border-gray-600 my-2">');
                    
                    // Split into paragraphs (double newlines) but preserve block elements
                    const blocks = processed.split(/(<h[1-6][^>]*>.*?<\/h[1-6]>|<ul[^>]*>.*?<\/ul>|<ol[^>]*>.*?<\/ol>|<pre[^>]*>.*?<\/pre>|<hr[^>]*>)/g);
                    let finalHtml = '';
                    
                    blocks.forEach((block) => {
                        if (!block.trim()) return;
                        
                        // If it's already a block element, add as-is
                        if (block.match(/^<(h[1-6]|ul|ol|pre|hr)/)) {
                            finalHtml += block;
                        } else {
                            // Regular text - wrap in paragraph
                            const para = block.trim().replace(/\n/g, ' ').replace(/\s+/g, ' ').trim();
                            if (para) {
                                finalHtml += `<p class="text-gray-800 dark:text-gray-200 my-1">${para}</p>`;
                            }
                        }
                    });
                    
                    html += finalHtml;
                }
            });
            
            // Final cleanup - remove empty paragraphs and excessive whitespace
            html = html.replace(/<p class="text-gray-200"><\/p>/g, '').replace(/>\s+</g, '><').trim();
            
            return html || '<p class="text-gray-800 dark:text-gray-200 my-1">' + escapeHtml(text) + '</p>';
        }

        function appendUserBubble(text, image = null) {
            const el = document.createElement('div');
            el.className = 'flex items-end justify-end message-bubble';
            
            let content = '';
            if (image) {
                content += `<img src="${image}" alt="Uploaded image" class="image-preview mb-2 rounded-lg">`;
            }
            if (text) {
                content += `<p class="text-white dark:text-white ${image ? '' : ''}">${escapeHtml(text)}</p>`;
            }
            
            el.innerHTML = `
                <div class="max-w-[75%] bg-indigo-600 dark:bg-indigo-600 bg-indigo-500 border border-indigo-500 dark:border-indigo-500 border-indigo-400 text-white dark:text-white rounded-2xl p-4 text-right shadow-lg hover-lift transition-colors duration-200">
                    ${content}
                    <div class="mt-2 text-xs text-indigo-100 dark:text-indigo-200 flex items-center justify-end gap-1">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        ${getCurrentTime()}
                    </div>
                </div>`;
            messagesEl.appendChild(el);
            messagesEl.scrollTop = messagesEl.scrollHeight;
        }

        function appendAIBubble(text) {
            const el = document.createElement('div');
            el.className = 'flex items-start gap-3 message-bubble';
            el.innerHTML = `
                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-indigo-600 to-purple-600 flex items-center justify-center text-white font-semibold shadow-lg flex-shrink-0">
                    AI
                </div>
                <div class="max-w-[75%] bg-gray-100 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-2xl p-4 shadow-lg hover-lift transition-colors duration-200">
                    <div class="text-gray-800 dark:text-gray-200 [&>p]:my-1 [&>p:first-child]:mt-0 [&>p:last-child]:mb-0 [&>ul]:my-1 [&>ol]:my-1 [&>h1]:my-2 [&>h1:first-child]:mt-0 [&>h2]:my-2 [&>h2:first-child]:mt-0 [&>h3]:my-2 [&>h3:first-child]:mt-0 [&>pre]:my-2">${text}</div>
                    <div class="mt-2 text-xs text-gray-500 dark:text-gray-400 flex items-center gap-1">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        ${getCurrentTime()}
                    </div>
                </div>`;
            messagesEl.appendChild(el);
            messagesEl.scrollTop = messagesEl.scrollHeight;
        }

        function showTypingIndicator() {
            const el = document.createElement('div');
            el.className = 'flex items-start gap-3 typing';
            el.innerHTML = `
                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-indigo-600/20 to-purple-600/20 flex items-center justify-center text-indigo-400 font-semibold flex-shrink-0">
                    AI
                </div>
                <div class="max-w-[60%] bg-gray-100 dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-2xl p-4 flex items-center gap-3 shadow-lg transition-colors duration-200">
                    <div class="flex gap-1.5 items-center">
                        <span class="w-2.5 h-2.5 rounded-full bg-indigo-400 animate-pulse"></span>
                        <span class="w-2.5 h-2.5 rounded-full bg-purple-400 animate-pulse" style="animation-delay: 0.2s"></span>
                        <span class="w-2.5 h-2.5 rounded-full bg-pink-400 animate-pulse" style="animation-delay: 0.4s"></span>
                    </div>
                    <div class="text-sm text-gray-600 dark:text-gray-400">Assistant is typing...</div>
                </div>`;
            messagesEl.appendChild(el);
            messagesEl.scrollTop = messagesEl.scrollHeight;
            return el;
        }

        function removeTypingIndicator(el) {
            if (el) el.remove();
        }

        function escapeHtml(unsafe) {
            return unsafe
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/\"/g, "&quot;")
                .replace(/'/g, "&#039;");
        }

        // Image preview click to view full size
        previewImage.addEventListener('click', () => {
            if (selectedImage) {
                window.open(selectedImage, '_blank');
            }
        });

        // Add welcome message on page load
        window.addEventListener('DOMContentLoaded', () => {
            const welcomeMsg = "Hello! I'm your AI assistant. You can ask me anything or share images with me. How can I help you today?";
            conversationHistory.push({
                role: 'assistant',
                content: welcomeMsg
            });
            appendAIBubble(formatMarkdownResponse(welcomeMsg));
        });
    </script>


</body>

</html>