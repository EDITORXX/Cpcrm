

<?php $__env->startSection('title', 'WhatsApp Chat - Base CRM'); ?>
<?php $__env->startSection('page-title', 'WhatsApp Chat'); ?>

<?php $__env->startSection('content'); ?>
<div class="h-screen flex flex-col bg-gray-50">
    <!-- Main Chat Container -->
    <div class="flex-1 flex overflow-hidden">
        <!-- Left Sidebar - Conversations List -->
        <div class="w-80 bg-white border-r border-gray-200 flex flex-col">
            <!-- Header -->
            <div class="p-4 border-b border-gray-200 bg-gradient-to-r from-[#063A1C] to-[#205A44]">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xl font-bold text-white">
                        <i class="fab fa-whatsapp mr-2"></i>Chats
                    </h2>
                    <button onclick="openAddContactModal()" 
                            class="p-2 bg-white bg-opacity-20 hover:bg-opacity-30 rounded-lg text-white transition-colors">
                        <i class="fas fa-plus"></i>
                    </button>
                </div>
                <!-- Search -->
                <input type="text" 
                       id="searchConversations" 
                       placeholder="Search conversations..."
                       class="w-full px-4 py-2 rounded-lg bg-white bg-opacity-20 border border-white border-opacity-30 text-white placeholder-white placeholder-opacity-70 focus:outline-none focus:ring-2 focus:ring-white focus:ring-opacity-50">
            </div>

            <!-- Conversations List -->
            <div id="conversationsList" class="flex-1 overflow-y-auto">
                <?php $__empty_1 = true; $__currentLoopData = $conversations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $conversation): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <?php
                        $latestMessage = $conversation->getLatestMessage();
                        $unreadCount = $conversation->getUnreadCount();
                    ?>
                    <div class="conversation-item p-4 border-b border-gray-100 hover:bg-gray-50 cursor-pointer transition-colors"
                         data-conversation-id="<?php echo e($conversation->id); ?>"
                         onclick="loadConversation(<?php echo e($conversation->id); ?>)">
                        <div class="flex items-start">
                            <div class="w-12 h-12 rounded-full bg-gradient-to-r from-[#063A1C] to-[#205A44] flex items-center justify-center text-white font-semibold mr-3">
                                <i class="fab fa-whatsapp"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between mb-1">
                                    <div class="flex items-center space-x-2 flex-1 min-w-0">
                                        <h3 class="font-semibold text-gray-900 truncate">
                                            <?php echo e($conversation->contact_name ?: $conversation->phone_number); ?>

                                        </h3>
                                        <?php if($conversation->lead): ?>
                                            <span class="px-2 py-0.5 bg-blue-100 text-blue-700 text-xs rounded-full font-medium whitespace-nowrap" title="Linked to Lead">
                                                <i class="fas fa-link mr-1"></i>Lead
                                            </span>
                                        <?php endif; ?>
                                        <?php if(auth()->user()->isAdmin() && $conversation->user): ?>
                                            <span class="px-2 py-0.5 bg-purple-100 text-purple-700 text-xs rounded-full font-medium whitespace-nowrap" title="Conversation Owner">
                                                <i class="fas fa-user mr-1"></i><?php echo e($conversation->user->name); ?>

                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    <?php if($latestMessage): ?>
                                        <span class="text-xs text-gray-500 ml-2 whitespace-nowrap">
                                            <?php echo e($latestMessage->created_at->format('H:i')); ?>

                                        </span>
                                    <?php endif; ?>
                                </div>
                                <div class="flex items-center justify-between">
                                    <p class="text-sm text-gray-600 truncate">
                                        <?php if($latestMessage): ?>
                                            <?php echo e(Str::limit($latestMessage->message, 40)); ?>

                                        <?php else: ?>
                                            No messages yet
                                        <?php endif; ?>
                                    </p>
                                    <?php if($unreadCount > 0): ?>
                                        <span class="ml-2 px-2 py-1 bg-green-500 text-white text-xs rounded-full font-semibold">
                                            <?php echo e($unreadCount); ?>

                                        </span>
                                    <?php endif; ?>
                                </div>
                                <?php if($conversation->lead): ?>
                                    <div class="mt-1 text-xs text-gray-500">
                                        <i class="fas fa-user mr-1"></i><?php echo e($conversation->lead->name); ?> • 
                                        <span class="capitalize"><?php echo e($conversation->lead->status); ?></span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <div class="p-8 text-center text-gray-500">
                        <i class="fab fa-whatsapp text-4xl mb-4 text-gray-300"></i>
                        <p>No conversations yet</p>
                        <button onclick="openAddContactModal()" 
                                class="mt-4 px-4 py-2 bg-gradient-to-r from-[#063A1C] to-[#205A44] text-white rounded-lg hover:from-[#205A44] hover:to-[#15803d] transition-colors">
                            <i class="fas fa-plus mr-2"></i>Start New Chat
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Right Panel - Active Conversation -->
        <div class="flex-1 flex flex-col" id="chatPanel">
            <!-- Empty State -->
            <div id="emptyState" class="flex-1 flex items-center justify-center bg-gray-50">
                <div class="text-center">
                    <i class="fab fa-whatsapp text-6xl text-gray-300 mb-4"></i>
                    <h3 class="text-xl font-semibold text-gray-700 mb-2">Select a conversation</h3>
                    <p class="text-gray-500">Choose a conversation from the list or start a new one</p>
                </div>
            </div>

            <!-- Active Chat (hidden by default) -->
            <div id="activeChat" class="flex-1 flex flex-col hidden">
                <!-- Chat Header -->
                <div class="bg-white border-b border-gray-200 p-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center flex-1 min-w-0">
                            <div class="w-10 h-10 rounded-full bg-gradient-to-r from-[#063A1C] to-[#205A44] flex items-center justify-center text-white font-semibold mr-3 flex-shrink-0">
                                <i class="fab fa-whatsapp"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center space-x-2 flex-wrap">
                                    <h3 id="chatContactName" class="font-semibold text-gray-900 truncate"></h3>
                                    <span id="leadBadge" class="hidden px-2 py-0.5 bg-blue-100 text-blue-700 text-xs rounded-full font-medium">
                                        <i class="fas fa-link mr-1"></i>Lead
                                    </span>
                                    <span id="userInfo" class="hidden px-2 py-0.5 bg-purple-100 text-purple-700 text-xs rounded-full font-medium">
                                        <i class="fas fa-user mr-1"></i><span id="userName"></span>
                                    </span>
                                </div>
                                <p id="chatPhoneNumber" class="text-sm text-gray-500"></p>
                                <div id="leadInfo" class="hidden mt-1">
                                    <a id="leadLink" href="#" class="text-xs text-blue-600 hover:text-blue-800 flex items-center">
                                        <i class="fas fa-user mr-1"></i>
                                        <span id="leadName"></span>
                                        <span class="ml-1 text-gray-500">•</span>
                                        <span id="leadStatus" class="ml-1 capitalize"></span>
                                        <i class="fas fa-external-link-alt ml-1 text-xs"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center space-x-2">
                            <button onclick="deleteCurrentConversation()" 
                                    class="p-2 text-red-500 hover:bg-red-50 rounded-lg transition-colors"
                                    title="Delete conversation">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Messages Area -->
                <div id="messagesContainer" class="flex-1 overflow-y-auto p-4 bg-gray-50 space-y-4">
                    <!-- Messages will be loaded here -->
                </div>

                <!-- Message Input Area -->
                <div class="bg-white border-t border-gray-200 p-4">
                    <div class="flex items-end space-x-2">
                        <button onclick="openTemplateModal()" 
                                class="p-2 text-gray-600 hover:bg-gray-100 rounded-lg transition-colors"
                                title="Send template">
                            <i class="fas fa-file-alt"></i>
                        </button>
                        <div class="flex-1">
                            <textarea id="messageInput" 
                                      rows="1"
                                      placeholder="Type a message..."
                                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 resize-none"
                                      onkeydown="handleMessageKeydown(event)"></textarea>
                        </div>
                        <button onclick="sendMessage()" 
                                id="sendButton"
                                class="p-3 bg-gradient-to-r from-[#063A1C] to-[#205A44] text-white rounded-lg hover:from-[#205A44] hover:to-[#15803d] transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                            <i class="fas fa-paper-plane"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Contact Modal -->
<div id="addContactModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-lg p-6 max-w-md w-full">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold">Add New Contact</h3>
            <button onclick="closeAddContactModal()" class="text-gray-500 hover:text-gray-700">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form id="addContactForm" onsubmit="event.preventDefault(); createConversation();">
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Phone Number</label>
                <input type="text" 
                       id="newPhoneNumber" 
                       required
                       placeholder="+91 9876543210 or 9876543210"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                <p class="text-xs text-gray-500 mt-1">Include country code (e.g., +91 for India)</p>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Contact Name (Optional)</label>
                <input type="text" 
                       id="newContactName" 
                       placeholder="Enter name"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
            </div>
            <div class="flex justify-end space-x-3">
                <button type="button" 
                        onclick="closeAddContactModal()" 
                        class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">
                    Cancel
                </button>
                <button type="submit" 
                        class="px-4 py-2 bg-gradient-to-r from-[#063A1C] to-[#205A44] text-white rounded-lg hover:from-[#205A44] hover:to-[#15803d]">
                    Add Contact
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Template Selector Modal -->
<div id="templateModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-lg p-6 max-w-2xl w-full max-h-[80vh] flex flex-col">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold">Select Template</h3>
            <div class="flex items-center space-x-2">
                <button onclick="syncTemplates()" 
                        id="syncTemplatesBtn"
                        class="px-4 py-2 bg-gradient-to-r from-[#063A1C] to-[#205A44] text-white rounded-lg hover:from-[#205A44] hover:to-[#15803d] transition-colors text-sm font-medium">
                    <i class="fas fa-sync-alt mr-2" id="syncIcon"></i>
                    <span id="syncText">Sync Templates</span>
                </button>
                <button onclick="closeTemplateModal()" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
        <div id="templatesList" class="flex-1 overflow-y-auto space-y-2">
            <div class="text-center text-gray-500 py-8">
                <i class="fas fa-spinner fa-spin text-2xl mb-2"></i>
                <p>Loading templates...</p>
            </div>
        </div>
    </div>
</div>

<!-- Template Preview Modal -->
<div id="templatePreviewModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-lg p-6 max-w-lg w-full max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold text-gray-900">Send Template</h3>
            <button onclick="closeTemplatePreviewModal()" class="text-gray-500 hover:text-gray-700 transition-colors">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        <div id="templatePreviewContent" class="mb-6">
            <!-- Preview content will be inserted here -->
        </div>
        <div class="flex justify-end space-x-3 pt-4 border-t border-gray-200">
            <button onclick="closeTemplatePreviewModal()" 
                    class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors">
                Cancel
            </button>
            <button id="confirmSendTemplateBtn" 
                    onclick="confirmSendTemplate()" 
                    class="px-4 py-2 bg-gradient-to-r from-[#063A1C] to-[#205A44] text-white rounded-lg hover:from-[#205A44] hover:to-[#15803d] transition-colors">
                Send Template
            </button>
        </div>
    </div>
    </div>
</div>

<script>
const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
const currentUserId = <?php echo e(auth()->id()); ?>;
let currentConversationId = null;
let messagePollingInterval = null;

// Load conversation
function loadConversation(conversationId) {
    currentConversationId = conversationId;
    
    // Update UI
    document.getElementById('emptyState').classList.add('hidden');
    document.getElementById('activeChat').classList.remove('hidden');
    
    // Mark conversation as active in list
    document.querySelectorAll('.conversation-item').forEach(item => {
        item.classList.remove('bg-green-50', 'border-green-200');
    });
    document.querySelector(`[data-conversation-id="${conversationId}"]`)?.classList.add('bg-green-50', 'border-green-200');
    
    // Fetch conversation details
    fetch(`<?php echo e(route('chat.conversations.show', '')); ?>/${conversationId}`, {
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const conversation = data.data.conversation;
            document.getElementById('chatContactName').textContent = conversation.contact_name || conversation.phone_number;
            document.getElementById('chatPhoneNumber').textContent = conversation.phone_number;
            
            // Show user info if admin viewing other user's conversation
            if (conversation.user_name && conversation.user_id !== currentUserId) {
                const userInfoEl = document.getElementById('userInfo');
                const userNameEl = document.getElementById('userName');
                if (userInfoEl && userNameEl) {
                    userInfoEl.classList.remove('hidden');
                    userNameEl.textContent = conversation.user_name;
                }
            } else {
                const userInfoEl = document.getElementById('userInfo');
                if (userInfoEl) {
                    userInfoEl.classList.add('hidden');
                }
            }
            
            // Show lead info if linked
            if (conversation.lead) {
                document.getElementById('leadBadge').classList.remove('hidden');
                document.getElementById('leadInfo').classList.remove('hidden');
                document.getElementById('leadName').textContent = conversation.lead.name;
                document.getElementById('leadStatus').textContent = conversation.lead.status;
                document.getElementById('leadLink').href = conversation.lead.url;
            } else {
                document.getElementById('leadBadge').classList.add('hidden');
                document.getElementById('leadInfo').classList.add('hidden');
            }
            
            // Load messages
            loadMessages(data.data.messages);
            
            // Start polling for new messages
            startMessagePolling();
        }
    })
    .catch(error => {
        console.error('Error loading conversation:', error);
    });
}

// Load messages into UI
function loadMessages(messages) {
    const container = document.getElementById('messagesContainer');
    container.innerHTML = '';
    
    messages.forEach(message => {
        const messageDiv = document.createElement('div');
        messageDiv.className = `flex ${message.direction === 'sent' ? 'justify-end' : 'justify-start'}`;
        
        const bubble = document.createElement('div');
        bubble.className = `max-w-xs lg:max-w-md px-4 py-2 rounded-lg ${
            message.direction === 'sent' 
                ? 'bg-gradient-to-r from-[#063A1C] to-[#205A44] text-white' 
                : 'bg-white text-gray-900 border border-gray-200'
        }`;
        
        bubble.innerHTML = `
            <p class="text-sm">${escapeHtml(message.message)}</p>
            <div class="flex items-center justify-end mt-1 space-x-1">
                <span class="text-xs opacity-70">${formatTime(message.created_at)}</span>
                ${message.direction === 'sent' ? `<i class="fas fa-${getStatusIcon(message.status)} text-xs"></i>` : ''}
            </div>
        `;
        
        messageDiv.appendChild(bubble);
        container.appendChild(messageDiv);
    });
    
    // Scroll to bottom
    container.scrollTop = container.scrollHeight;
}

// Send message
function sendMessage() {
    if (!currentConversationId) {
        alert('Please select a conversation first');
        return;
    }
    
    const messageInput = document.getElementById('messageInput');
    const message = messageInput.value.trim();
    
    if (!message) {
        return;
    }
    
    const sendButton = document.getElementById('sendButton');
    sendButton.disabled = true;
    sendButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    
    fetch('<?php echo e(route("chat.messages.send")); ?>', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            conversation_id: currentConversationId,
            message: message
        })
    })
    .then(response => response.json())
    .then(data => {
        messageInput.value = '';
        messageInput.style.height = 'auto';
        
        if (data.success) {
            // Reload conversation
            loadConversation(currentConversationId);
        } else {
            alert('Failed to send message: ' + (data.error || data.message));
        }
    })
    .catch(error => {
        console.error('Error sending message:', error);
        alert('Error sending message');
    })
    .finally(() => {
        sendButton.disabled = false;
        sendButton.innerHTML = '<i class="fas fa-paper-plane"></i>';
    });
}

// Create new conversation
function createConversation() {
    const phone = document.getElementById('newPhoneNumber').value.trim();
    const name = document.getElementById('newContactName').value.trim();
    
    fetch('<?php echo e(route("chat.conversations.create")); ?>', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            phone_number: phone,
            contact_name: name || null
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            closeAddContactModal();
            location.reload(); // Reload to show new conversation
        } else {
            alert('Error: ' + (data.message || 'Failed to create conversation'));
        }
    })
    .catch(error => {
        console.error('Error creating conversation:', error);
        alert('Error creating conversation');
    });
}

// Template functions
function openTemplateModal() {
    if (!currentConversationId) {
        alert('Please select a conversation first');
        return;
    }
    
    document.getElementById('templateModal').classList.remove('hidden');
    loadTemplates();
}

function closeTemplateModal() {
    document.getElementById('templateModal').classList.add('hidden');
}

let selectedTemplateId = null;

function loadTemplates() {
    fetch('<?php echo e(route("chat.templates.index")); ?>', {
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        const container = document.getElementById('templatesList');
        if (data.success && data.data.length > 0) {
            // Store templates globally for preview
            window.templatesData = data.data;
            
            container.innerHTML = data.data.map((template, index) => `
                <div class="p-4 border border-gray-200 rounded-lg hover:border-green-500 hover:shadow-md transition-all cursor-pointer group" data-template-index="${index}">
                    <div class="flex items-start justify-between mb-2">
                        <div class="flex-1">
                            <h4 class="font-semibold text-gray-900 mb-1">${escapeHtml(template.name || template.template_id || 'Unnamed Template')}</h4>
                            <p class="text-sm text-gray-600 line-clamp-2">${escapeHtml(template.content || template.body || 'No content available')}</p>
                            ${template.category ? `<span class="inline-block mt-2 px-2 py-1 text-xs bg-gray-100 text-gray-600 rounded">${escapeHtml(template.category)}</span>` : ''}
                            ${template.language ? `<span class="inline-block mt-2 ml-2 px-2 py-1 text-xs bg-blue-100 text-blue-600 rounded">${escapeHtml(template.language)}</span>` : ''}
                        </div>
                    </div>
                    <div class="flex items-center justify-end space-x-2 mt-3 opacity-0 group-hover:opacity-100 transition-opacity">
                        <button onclick="previewTemplateByIndex(${index})" 
                                class="px-3 py-1 text-xs bg-blue-100 text-blue-700 rounded hover:bg-blue-200">
                            <i class="fas fa-eye mr-1"></i>Preview
                        </button>
                        <button onclick="sendTemplate('${template.template_id}')" 
                                class="px-3 py-1 text-xs bg-gradient-to-r from-[#063A1C] to-[#205A44] text-white rounded hover:from-[#205A44] hover:to-[#15803d]">
                            <i class="fas fa-paper-plane mr-1"></i>Send
                        </button>
                    </div>
                </div>
            `).join('');
        } else {
            container.innerHTML = '<div class="text-center text-gray-500 py-8"><i class="fas fa-inbox text-3xl mb-2"></i><p>No templates available. Click "Sync Templates" to load templates from API.</p></div>';
        }
    })
    .catch(error => {
        console.error('Error loading templates:', error);
        document.getElementById('templatesList').innerHTML = '<div class="text-center text-red-500 py-8"><i class="fas fa-exclamation-triangle text-2xl mb-2"></i><p>Error loading templates</p></div>';
    });
}

// Store templates data globally
window.templatesData = [];

function previewTemplateByIndex(index) {
    if (!window.templatesData || !window.templatesData[index]) {
        alert('Template data not found');
        return;
    }
    
    const template = window.templatesData[index];
    previewTemplate(template);
}

function previewTemplate(template) {
    if (!template) {
        alert('Template not found');
        return;
    }
    
    selectedTemplateId = template.template_id;
    const templateName = template.name || template.template_id || 'Unnamed Template';
    // Try multiple possible content fields
    const templateContent = template.content || template.body || template.message || template.text || template.description || '';
    const templateCategory = template.category || '';
    const templateLanguage = template.language || '';
    
    document.getElementById('templatePreviewContent').innerHTML = `
        <div class="mb-6">
            <div class="mb-4">
                <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-2">TEMPLATE NAME</label>
                <div class="text-base font-semibold text-gray-900">${escapeHtml(templateName)}</div>
            </div>
            ${templateCategory || templateLanguage ? `
            <div class="flex items-center space-x-2 mb-4">
                ${templateCategory ? `<span class="px-2 py-1 text-xs bg-gray-100 text-gray-600 rounded">${escapeHtml(templateCategory)}</span>` : ''}
                ${templateLanguage ? `<span class="px-2 py-1 text-xs bg-blue-100 text-blue-600 rounded">${escapeHtml(templateLanguage)}</span>` : ''}
            </div>
            ` : ''}
        </div>
        <div class="mb-6">
            <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-2">MESSAGE CONTENT</label>
            <div class="mt-2 p-4 bg-white rounded-lg border border-gray-300 min-h-[120px]">
                ${templateContent ? `
                    <p class="text-sm text-gray-800 whitespace-pre-wrap leading-relaxed">${escapeHtml(templateContent)}</p>
                ` : `
                    <p class="text-sm text-gray-400 italic">No content available</p>
                `}
            </div>
        </div>
        ${template.template_id ? `
        <div class="mb-4 pt-4 border-t border-gray-200">
            <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-2">TEMPLATE ID</label>
            <p class="text-sm text-gray-600 font-mono">${escapeHtml(template.template_id)}</p>
        </div>
        ` : ''}
    `;
    document.getElementById('templateModal').classList.add('hidden');
    document.getElementById('templatePreviewModal').classList.remove('hidden');
}

function closeTemplatePreviewModal() {
    document.getElementById('templatePreviewModal').classList.add('hidden');
    selectedTemplateId = null;
}

function confirmSendTemplate() {
    if (selectedTemplateId) {
        closeTemplatePreviewModal();
        sendTemplate(selectedTemplateId);
    }
}

function sendTemplate(templateId) {
    closeTemplateModal();
    
    fetch('<?php echo e(route("chat.messages.template")); ?>', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            conversation_id: currentConversationId,
            template_id: templateId,
            parameters: {}
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            loadConversation(currentConversationId);
        } else {
            alert('Failed to send template: ' + (data.error || data.message));
        }
    })
    .catch(error => {
        console.error('Error sending template:', error);
        alert('Error sending template');
    });
}

// Modal functions
function openAddContactModal() {
    document.getElementById('addContactModal').classList.remove('hidden');
    document.getElementById('newPhoneNumber').focus();
}

function closeAddContactModal() {
    document.getElementById('addContactModal').classList.add('hidden');
    document.getElementById('addContactForm').reset();
}

function deleteCurrentConversation() {
    if (!currentConversationId) return;
    
    if (!confirm('Are you sure you want to delete this conversation?')) return;
    
    fetch(`<?php echo e(route('chat.conversations.delete', '')); ?>/${currentConversationId}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Error deleting conversation');
        }
    })
    .catch(error => {
        console.error('Error deleting conversation:', error);
        alert('Error deleting conversation');
    });
}

// Utility functions
function handleMessageKeydown(event) {
    if (event.key === 'Enter' && !event.shiftKey) {
        event.preventDefault();
        sendMessage();
    }
}

function formatTime(dateString) {
    const date = new Date(dateString);
    const now = new Date();
    const diff = now - date;
    const minutes = Math.floor(diff / 60000);
    
    if (minutes < 1) return 'Just now';
    if (minutes < 60) return `${minutes}m ago`;
    if (date.toDateString() === now.toDateString()) {
        return date.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit' });
    }
    return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', hour: 'numeric', minute: '2-digit' });
}

function getStatusIcon(status) {
    switch(status) {
        case 'sent': return 'check';
        case 'delivered': return 'check-double';
        case 'read': return 'check-double text-blue-400';
        case 'failed': return 'exclamation-circle';
        default: return 'clock';
    }
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function startMessagePolling() {
    if (messagePollingInterval) {
        clearInterval(messagePollingInterval);
    }
    
    messagePollingInterval = setInterval(() => {
        if (currentConversationId) {
            // Sync messages from API first, then reload conversation
            syncMessagesFromAPI(currentConversationId);
        }
    }, 10000); // Poll every 10 seconds
}

function syncMessagesFromAPI(conversationId) {
    fetch(`<?php echo e(route('chat.conversations.sync-messages', '')); ?>/${conversationId}`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.data.messages) {
            // Update messages in UI
            const existingMessages = document.querySelectorAll('#messagesContainer > div').length;
            const newMessages = data.data.messages;
            
            // Only add new messages (messages that don't exist in UI)
            newMessages.forEach(message => {
                const messageExists = Array.from(document.querySelectorAll('#messagesContainer > div')).some(div => {
                    const messageId = div.getAttribute('data-message-id');
                    return messageId && messageId == message.id;
                });
                
                if (!messageExists) {
                    addMessageToUI(message);
                }
            });
            
            // Scroll to bottom if new messages added
            if (newMessages.length > existingMessages) {
                const container = document.getElementById('messagesContainer');
                container.scrollTop = container.scrollHeight;
            }
        }
    })
    .catch(error => {
        console.error('Error syncing messages:', error);
    });
}

function addMessageToUI(message) {
    const container = document.getElementById('messagesContainer');
    const messageDiv = document.createElement('div');
    messageDiv.className = `flex ${message.direction === 'sent' ? 'justify-end' : 'justify-start'} mb-2`;
    messageDiv.setAttribute('data-message-id', message.id);
    
    const bubble = document.createElement('div');
    bubble.className = `max-w-xs lg:max-w-md px-4 py-2 rounded-lg ${
        message.direction === 'sent' 
            ? 'bg-gradient-to-r from-[#063A1C] to-[#205A44] text-white' 
            : 'bg-white text-gray-900 border border-gray-200'
    }`;
    
    bubble.innerHTML = `
        <p class="text-sm">${escapeHtml(message.message)}</p>
        <div class="flex items-center justify-end mt-1 space-x-1">
            <span class="text-xs opacity-70">${formatTime(message.created_at)}</span>
            ${message.direction === 'sent' ? `<i class="fas fa-${getStatusIcon(message.status)} text-xs"></i>` : ''}
        </div>
    `;
    
    messageDiv.appendChild(bubble);
    container.appendChild(messageDiv);
}

// Auto-resize textarea
document.getElementById('messageInput')?.addEventListener('input', function() {
    this.style.height = 'auto';
    this.style.height = (this.scrollHeight) + 'px';
});

// Close modals on outside click
document.getElementById('addContactModal')?.addEventListener('click', function(e) {
    if (e.target === this) closeAddContactModal();
});

document.getElementById('templateModal')?.addEventListener('click', function(e) {
    if (e.target === this) closeTemplateModal();
});
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\vivek\Pictures\Laravel crm fully functional\resources\views/chat/index.blade.php ENDPATH**/ ?>