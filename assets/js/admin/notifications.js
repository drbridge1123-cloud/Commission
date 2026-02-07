/**
 * Admin Dashboard - Notifications/Messages tab functions.
 */

// Message functions
function openMessageModal(userId, userName) {
    document.getElementById('messageRecipientId').value = userId;
    document.getElementById('messageRecipientName').textContent = userName;
    document.getElementById('messageSubject').value = '';
    document.getElementById('messageBody').value = '';
    document.getElementById('messageModal').style.display = 'flex';
}

function closeMessageModal() {
    document.getElementById('messageModal').style.display = 'none';
}

async function sendMessage(e) {
    e.preventDefault();

    const userId = document.getElementById('messageRecipientId').value;
    const subject = document.getElementById('messageSubject').value;
    const message = document.getElementById('messageBody').value;

    try {
        const result = await apiCall('api/messages.php', {
            method: 'POST',
            body: JSON.stringify({
                to_user_id: parseInt(userId),
                subject: subject,
                message: message
            })
        });

        if (result.success) {
            alert('Message sent successfully!');
            closeMessageModal();
        }
    } catch (err) {
        console.error('Error:', err);
        alert(err.message || 'Error sending message');
    }
}

// Messages Tab Functions
async function loadMessages() {
    try {
        // Get dismissed notifications from localStorage
        const dismissed = JSON.parse(localStorage.getItem('dismissedNotificationsAdmin') || '[]');

        // Get all cases for notifications
        const casesData = await apiCall('api/cases.php');
        const cases = casesData.cases || [];

        // Get all messages
        const messagesData = await apiCall('api/messages.php');
        const messages = messagesData.messages || [];
        allMessagesAdmin = messages;

        // Get all users for employee names
        const usersData = await apiCall('api/users.php');
        const users = usersData.users || [];

        // Get case approval/rejection notifications (last 50) - filter out dismissed
        const reviewed = cases.filter(c => c.reviewed_at && !dismissed.includes(`case_${c.id}`))
            .sort((a, b) => new Date(b.reviewed_at) - new Date(a.reviewed_at))
            .slice(0, 50);

        const content = document.getElementById('notificationsContent');

        if (reviewed.length === 0 && messages.length === 0) {
            content.innerHTML = '<p style="text-align: center; color: #8b8fa3; padding: 32px; font-size: 12px; font-family: Outfit, sans-serif;">No notifications yet</p>';
            return;
        }

        let htmlOutput = '';

        // System Notifications Section
        if (reviewed.length > 0) {
            htmlOutput += `
                <div style="background: #1a1a2e; padding: 10px 16px;">
                    <h3 style="font-size: 11px; font-weight: 600; color: #fff; text-transform: uppercase; letter-spacing: 0.5px; font-family: 'Outfit', sans-serif;">System Notifications</h3>
                </div>
            `;

            reviewed.forEach(c => {
                const employee = users.find(u => u.id === c.user_id);
                const statusIcon = c.status === 'paid' ? '<span style="color: #0d9488;">✓</span>' : '<span style="color: #dc2626;">✗</span>';
                htmlOutput += `
                    <div onclick="showCaseDetailAdmin(${c.id})" style="display: flex; align-items: center; padding: 12px 16px; border-bottom: 1px solid #f0f1f3; cursor: pointer; transition: background 0.1s;" onmouseover="this.style.background='#f5f8ff'" onmouseout="this.style.background='transparent'">
                        <div style="width: 28px; height: 28px; border-radius: 6px; background: ${c.status === 'paid' ? '#d1fae5' : '#fee2e2'}; display: flex; align-items: center; justify-content: center; font-size: 14px; margin-right: 12px;">${statusIcon}</div>
                        <div style="flex: 1; min-width: 0;">
                            <div style="font-size: 13px; font-weight: 600; color: #1a1a2e; font-family: 'Outfit', sans-serif;">Case #${c.case_number} - ${c.client_name}</div>
                            <div style="font-size: 11px; color: #8b8fa3; margin-top: 2px;">${c.status === 'paid' ? `Commission: $${parseFloat(c.commission).toLocaleString('en-US', {minimumFractionDigits: 2})}` : 'Rejected'} · ${formatDate(new Date(c.reviewed_at))}</div>
                        </div>
                        <button onclick="event.stopPropagation(); showDeleteConfirmModal('case', 'case_${c.id}')" class="act-link danger" style="margin-left: 8px;">Delete</button>
                    </div>
                `;
            });
        }

        // Messages Section
        if (messages.length > 0) {
            htmlOutput += `
                <div style="background: #1a1a2e; padding: 10px 16px;">
                    <h3 style="font-size: 11px; font-weight: 600; color: #fff; text-transform: uppercase; letter-spacing: 0.5px; font-family: 'Outfit', sans-serif;">Messages</h3>
                </div>
            `;

            messages.forEach(m => {
                const isSent = m.direction === 'sent';
                const bgColor = isSent ? '#d1fae5' : '#dbeafe';
                const iconEmoji = isSent ? '↑' : '↓';
                const unreadDot = !isSent && !m.is_read ? '<span style="color: #dc2626; margin-right: 4px;">●</span>' : '';

                htmlOutput += `
                    <div onclick="viewMessage(${m.id})" style="display: flex; align-items: center; padding: 12px 16px; border-bottom: 1px solid #f0f1f3; cursor: pointer; transition: background 0.1s;" onmouseover="this.style.background='#f5f8ff'" onmouseout="this.style.background='transparent'">
                        <div style="width: 28px; height: 28px; border-radius: 6px; background: ${bgColor}; display: flex; align-items: center; justify-content: center; font-size: 14px; margin-right: 12px; font-weight: 600; color: ${isSent ? '#0d9488' : '#3b82f6'};">${iconEmoji}</div>
                        <div style="flex: 1; min-width: 0;">
                            <div style="font-size: 13px; font-weight: 600; color: #1a1a2e; font-family: 'Outfit', sans-serif;">${unreadDot}${m.from_name} → ${m.to_name}</div>
                            <div style="font-size: 11px; color: #8b8fa3; margin-top: 2px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">${m.subject} · ${formatDate(new Date(m.created_at))}</div>
                        </div>
                        ${!isSent ? `<button onclick="event.stopPropagation(); showDeleteConfirmModal('message', ${m.id})" class="act-link danger" style="margin-left: 8px;">Delete</button>` : ''}
                    </div>
                `;
            });
        }

        // Render all content
        content.innerHTML = htmlOutput;

        // Update badge with unread count from API
        const unreadCount = messagesData.unread_count || 0;
        const badge = document.getElementById('notificationBadge');
        if (badge) {
            if (unreadCount > 0) {
                badge.textContent = unreadCount;
                badge.classList.remove('hidden');
            } else {
                badge.classList.add('hidden');
            }
        }

        // Populate employee dropdown for compose
        const select = document.getElementById('composeRecipientId');
        const employees = users.filter(u => u.role === 'employee');
        select.innerHTML = '<option value="">Select employee...</option>' +
            employees.map(u => `<option value="${u.id}">${u.display_name}</option>`).join('');

    } catch (err) {
        console.error('Error loading messages:', err);
    }
}

async function markAllReadAdmin() {
    try {
        await apiCall('api/messages.php', {
            method: 'PUT',
            body: JSON.stringify({ mark_all: true })
        });

        const casesData = await apiCall('api/cases.php');
        const cases = casesData.cases || [];
        const reviewedCases = cases.filter(c => c.reviewed_at);
        const dismissed = reviewedCases.map(c => `case_${c.id}`);
        localStorage.setItem('dismissedNotificationsAdmin', JSON.stringify(dismissed));

        loadMessages();
    } catch (err) {
        console.error('Error marking all as read:', err);
    }
}

function openComposeMessageAdmin() {
    document.getElementById('composeMessageModal').style.display = 'flex';
    document.getElementById('composeRecipientId').value = '';
    document.getElementById('composeSubject').value = '';
    document.getElementById('composeMessage').value = '';
}

function closeComposeMessageAdmin() {
    document.getElementById('composeMessageModal').style.display = 'none';
}

async function sendAdminMessage(e) {
    e.preventDefault();

    const toUserId = document.getElementById('composeRecipientId').value;
    const subject = document.getElementById('composeSubject').value;
    const message = document.getElementById('composeMessage').value;

    try {
        const result = await apiCall('api/messages.php', {
            method: 'POST',
            body: JSON.stringify({
                to_user_id: parseInt(toUserId),
                subject: subject,
                message: message
            })
        });

        if (result.success) {
            alert('Message sent successfully!');
            closeComposeMessageAdmin();
            loadMessages();
        } else {
            alert(result.error || 'Error sending message');
        }
    } catch (err) {
        console.error('Error:', err);
        alert('Error sending message');
    }
}

async function viewMessage(messageId) {
    try {
        const data = await apiCall('api/messages.php');
        const messages = data.messages || [];
        const message = messages.find(m => m.id == messageId);

        if (!message) return;

        const usersData = await apiCall('api/users.php');
        const users = usersData.users || [];

        const isSent = message.direction === 'sent';
        const otherName = isSent ? message.to_name : message.from_name;

        currentViewingMessageAdmin = {
            ...message,
            from_name: message.from_name,
            to_name: message.to_name,
            isSent: isSent
        };

        document.getElementById('viewMessageTitle').textContent = isSent ? `Message to ${otherName}` : `Message from ${otherName}`;
        document.getElementById('viewMessageFromLabel').textContent = isSent ? 'To:' : 'From:';
        document.getElementById('viewMessageFrom').textContent = otherName;
        document.getElementById('viewMessageSubject').textContent = message.subject;
        document.getElementById('viewMessageDate').textContent = new Date(message.created_at).toLocaleString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
        document.getElementById('viewMessageBody').textContent = message.message;

        document.getElementById('viewMessageModal').style.display = 'flex';

        if (!isSent && !message.is_read) {
            await apiCall('api/messages.php', {
                method: 'PUT',
                body: JSON.stringify({ id: messageId })
            });
            loadMessages();
        }
    } catch (err) {
        console.error('Error:', err);
    }
}

function closeViewMessage() {
    document.getElementById('viewMessageModal').style.display = 'none';
}

// Delete confirmation modal functions
function showDeleteConfirmModal(type, id) {
    pendingDeleteType = type;
    pendingDeleteId = id;
    document.getElementById('deleteConfirmModal').style.display = 'flex';
}

function closeDeleteConfirmModal() {
    pendingDeleteType = null;
    pendingDeleteId = null;
    document.getElementById('deleteConfirmModal').style.display = 'none';
}

function confirmDeleteNotification() {
    if (pendingDeleteType === 'case') {
        dismissNotificationAdmin(pendingDeleteId);
    } else if (pendingDeleteType === 'message') {
        deleteMessageAdminConfirmed(pendingDeleteId);
    }
    closeDeleteConfirmModal();
}

function dismissNotificationAdmin(notificationId) {
    const dismissed = JSON.parse(localStorage.getItem('dismissedNotificationsAdmin') || '[]');
    dismissed.push(notificationId);
    localStorage.setItem('dismissedNotificationsAdmin', JSON.stringify(dismissed));
    loadMessages();
}

async function deleteMessageAdmin(messageId) {
    if (!confirm('Are you sure you want to delete this message?')) {
        return;
    }
    await deleteMessageAdminConfirmed(messageId);
}

async function deleteMessageAdminConfirmed(messageId) {
    try {
        const result = await apiCall(`api/messages.php?id=${messageId}`, {
            method: 'DELETE'
        });

        if (result.success) {
            loadMessages();
        } else {
            alert(result.error || 'Error deleting message');
        }
    } catch (err) {
        console.error('Error:', err);
        alert('Error deleting message');
    }
}

function replyToMessageAdmin() {
    if (!currentViewingMessageAdmin) return;

    closeViewMessage();

    const replySubject = currentViewingMessageAdmin.subject.startsWith('Re: ')
        ? currentViewingMessageAdmin.subject
        : 'Re: ' + currentViewingMessageAdmin.subject;

    document.getElementById('composeRecipientId').value = currentViewingMessageAdmin.from_user_id;
    document.getElementById('composeSubject').value = replySubject;
    document.getElementById('composeMessage').value = '';
    document.getElementById('composeMessageModal').style.display = 'flex';
}

async function deleteCurrentMessageAdmin() {
    if (!currentViewingMessageAdmin) return;

    if (!confirm('Are you sure you want to delete this message?')) {
        return;
    }

    try {
        const result = await apiCall(`api/messages.php?id=${currentViewingMessageAdmin.id}`, {
            method: 'DELETE'
        });

        if (result.success) {
            closeViewMessage();
            loadMessages();
        } else {
            alert(result.error || 'Error deleting message');
        }
    } catch (err) {
        console.error('Error:', err);
        alert('Error deleting message');
    }
}
