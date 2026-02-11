/**
 * Employee dashboard - Notifications tab functions.
 */

// Messages state
let currentViewingMessage = null;

// Delete confirmation modal variables
let pendingDeleteType = null; // 'case' or 'message'
let pendingDeleteId = null;

async function checkNotifications() {
    try {
        // Check for new messages
        const messagesData = await apiCall('api/messages.php');
        const unreadMessages = messagesData.messages.filter(m => !m.is_read);

        // Find new messages since last check
        const newMessages = messagesData.messages.filter(m => {
            const createdDate = new Date(m.created_at);
            return createdDate > new Date(lastCheckedTime);
        });

        // Update notification badge with unread count
        const badge = document.getElementById('notificationBadge');
        if (badge) {
            if (unreadMessages.length > 0) {
                badge.textContent = unreadMessages.length;
                badge.classList.remove('hidden');
            } else {
                badge.classList.add('hidden');
            }
        }

        // Show browser notifications for new messages
        if (Notification.permission === 'granted') {
            newMessages.forEach(m => {
                new Notification(`New Message from ${m.from_name}`, {
                    body: m.subject,
                    icon: '/favicon.ico'
                });
            });
        }

        lastCheckedTime = new Date().toISOString();
    } catch (err) {
        console.error('Error checking notifications:', err);
    }
}

async function loadNotifications() {
    try {
        // Get dismissed notifications from localStorage
        const dismissed = JSON.parse(localStorage.getItem('dismissedNotifications') || '[]');

        // Get messages
        const msgData = await apiCall('api/messages.php');
        const messages = msgData.messages || [];
        allMessages = messages; // Store globally for modal access

        // Build items list from messages only
        const allItems = [];

        messages.forEach(m => {
            const isSent = m.direction === 'sent';
            allItems.push({
                type: 'message',
                subtype: isSent ? 'sent' : 'received',
                direction: m.direction,
                is_read: isSent ? true : !!m.is_read,
                fromTo: isSent ? 'To: ' + escapeHtml(m.to_name) : escapeHtml(m.from_name),
                subject: escapeHtml(m.subject || '(No subject)'),
                time: m.created_at,
                onclick: `viewMessage(${m.id})`,
                deleteAction: !isSent ? `showDeleteConfirmModal('message', ${m.id})` : null,
                id: m.id
            });
        });

        // Sort by time descending
        allItems.sort((a, b) => new Date(b.time) - new Date(a.time));

        // Store for filtering
        window._notifItems = allItems;

        renderNotifications(allItems, notifCurrentFilter);

        // Update badge
        const unreadCount = msgData.unread_count || 0;
        const notifBadge = document.getElementById('notificationBadge');
        if (notifBadge) {
            if (unreadCount > 0) {
                notifBadge.textContent = unreadCount;
                notifBadge.classList.remove('hidden');
            } else {
                notifBadge.classList.add('hidden');
            }
        }
    } catch (err) {
        console.error('Error loading notifications:', err);
    }
}

function renderNotifications(items, filter) {
    // Filter items
    let filtered = items;
    if (filter === 'unread') {
        filtered = items.filter(i => !i.is_read);
    } else if (filter === 'sent') {
        filtered = items.filter(i => i.direction === 'sent');
    } else if (filter === 'read') {
        filtered = items.filter(i => i.direction === 'received' && i.is_read);
    }

    // Stats (messages only for counts)
    const msgItems = items.filter(i => i.type === 'message');
    const totalCount = items.length;
    const unreadCount = items.filter(i => !i.is_read).length;
    const sentCount = items.filter(i => i.direction === 'sent').length;

    const statTotal = document.getElementById('notifStatTotal');
    const statUnread = document.getElementById('notifStatUnread');
    const statSent = document.getElementById('notifStatSent');
    if (statTotal) statTotal.textContent = totalCount;
    if (statUnread) {
        statUnread.textContent = unreadCount;
        statUnread.className = 'qs-val ' + (unreadCount > 0 ? 'blue' : 'dim');
    }
    if (statSent) {
        statSent.textContent = sentCount;
        statSent.className = 'qs-val ' + (sentCount === 0 ? 'dim' : '');
    }

    const tbody = document.getElementById('notificationsBody');

    if (filtered.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="6">
                    <div class="notif-empty">
                        <div class="notif-empty-icon">
                            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <div class="notif-empty-title">No messages</div>
                        <div class="notif-empty-desc">You're all caught up!</div>
                    </div>
                </td>
            </tr>
        `;
    } else {
        let html = '';
        filtered.forEach(item => {
            const isUnread = !item.is_read;
            const rowClass = isUnread ? 'unread' : '';

            // Direction badge
            let dirBadge;
            if (item.type === 'system') {
                dirBadge = item.subtype === 'approved'
                    ? '<span class="dir-badge system-approved">Approved</span>'
                    : '<span class="dir-badge system-rejected">Rejected</span>';
            } else {
                dirBadge = item.direction === 'sent'
                    ? '<span class="dir-badge sent">Sent</span>'
                    : '<span class="dir-badge received">Received</span>';
            }

            const dot = isUnread ? '<div class="unread-dot"></div>' : '';
            const timeStr = formatRelativeTime(item.time);

            const deleteBtn = item.deleteAction ? `
                <button class="act-icon danger" onclick="event.stopPropagation(); ${item.deleteAction}" title="Delete">
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                </button>
            ` : '';

            html += `
                <tr class="${rowClass}" onclick="${item.onclick}" style="cursor:pointer;">
                    <td style="width:24px;padding:10px 6px 10px 14px;">${dot}</td>
                    <td>${dirBadge}</td>
                    <td>${item.fromTo}</td>
                    <td class="td-subject">${item.subject}</td>
                    <td class="td-time">${timeStr}</td>
                    <td style="text-align:center;">
                        <div class="action-group">
                            <button class="act-icon" onclick="event.stopPropagation(); ${item.onclick}" title="View">
                                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            </button>
                            ${deleteBtn}
                        </div>
                    </td>
                </tr>
            `;
        });
        tbody.innerHTML = html;
    }

    // Footer
    const footLeft = document.getElementById('notifFootLeft');
    const footRight = document.getElementById('notifFootRight');
    if (footLeft) footLeft.textContent = `${filtered.length} item${filtered.length !== 1 ? 's' : ''}`;
    if (footRight) footRight.textContent = `${unreadCount} unread`;
}

function filterNotifications(filter) {
    notifCurrentFilter = filter;

    // Update chip active state
    document.querySelectorAll('.notif-filters .f-chip').forEach(chip => {
        chip.classList.toggle('active', chip.dataset.filter === filter);
    });

    // Re-render with current data
    if (window._notifItems) {
        renderNotifications(window._notifItems, filter);
    }
}

async function viewMessage(messageId) {
    try {
        // Find the message
        const message = allMessages.find(m => m.id == messageId);
        if (!message) {
            console.error('Message not found');
            return;
        }

        // Show modal with message content
        showMessageModal(message);

        // Mark as read if unread
        if (!message.is_read) {
            await apiCall('api/messages.php', {
                method: 'PUT',
                body: JSON.stringify({ id: messageId })
            });

            // Update local message status
            message.is_read = 1;

            // Reload notifications to update badge
            checkNotifications();
        }
    } catch (err) {
        console.error('Error viewing message:', err);
    }
}

function showMessageModal(message) {
    currentViewingMessage = message; // Store current message for reply/delete

    const isSent = message.direction === 'sent';
    const otherName = isSent ? message.to_name : message.from_name;

    // Update modal title and labels based on direction
    document.getElementById('messageModalTitle').textContent = isSent ? `Message to ${otherName}` : `Message from ${otherName}`;
    document.getElementById('messageFromLabel').textContent = isSent ? 'To:' : 'From:';
    document.getElementById('messageFrom').textContent = otherName;
    document.getElementById('messageSubject').textContent = message.subject;
    document.getElementById('messageBody').textContent = message.message;

    const date = new Date(message.created_at);
    document.getElementById('messageDate').textContent = formatRelativeTime(message.created_at);

    // Check if subject contains a case number and show/hide View Case button
    const caseMatch = message.subject.match(/Case #(\d+)/i);
    const viewCaseBtn = document.getElementById('viewCaseBtn');
    if (caseMatch) {
        window.currentMessageCaseNumber = caseMatch[1];
        viewCaseBtn.style.display = 'inline-block';
    } else {
        window.currentMessageCaseNumber = null;
        viewCaseBtn.style.display = 'none';
    }

    document.getElementById('messageModal').style.display = 'flex';
}

async function viewCaseFromMessage() {
    const caseNumber = window.currentMessageCaseNumber;
    if (!caseNumber) {
        alert('No case number found in this message');
        return;
    }

    try {
        // Find the case by case_number
        const data = await apiCall('api/cases.php');
        const cases = data.cases || [];
        const targetCase = cases.find(c => c.case_number === caseNumber);

        if (!targetCase) {
            alert(`Case #${caseNumber} not found`);
            return;
        }

        // Close message modal and open case detail
        closeMessageModal();
        viewCaseDetail(targetCase.id);
    } catch (err) {
        console.error('Error:', err);
        alert('Error loading case details');
    }
}

function replyToMessage() {
    if (!currentViewingMessage) return;

    closeMessageModal();

    // Open compose modal with pre-filled subject
    const replySubject = currentViewingMessage.subject.startsWith('Re: ')
        ? currentViewingMessage.subject
        : 'Re: ' + currentViewingMessage.subject;

    document.getElementById('composeSubject').value = replySubject;
    document.getElementById('composeMessage').value = '';
    document.getElementById('composeMessageModal').style.display = 'flex';
}

async function deleteCurrentMessage() {
    if (!currentViewingMessage) return;

    if (!confirm('Are you sure you want to delete this message?')) {
        return;
    }

    try {
        const result = await apiCall(`api/messages.php?id=${currentViewingMessage.id}`, {
            method: 'DELETE'
        });

        if (result.success) {
            closeMessageModal();
            loadNotifications(); // Reload notifications
        } else {
            alert(result.error || 'Error deleting message');
        }
    } catch (err) {
        console.error('Error:', err);
        alert(err.message || 'Error deleting message');
    }
}

function closeMessageModal() {
    document.getElementById('messageModal').style.display = 'none';
    // Reload notifications to refresh the list
    loadNotifications();
}

async function markAllRead() {
    try {
        // Mark all messages as read via API
        const result = await apiCall('api/messages.php', {
            method: 'PUT',
            body: JSON.stringify({ mark_all: true })
        });

        if (result.success) {
            // Reload notifications to update the display and badge
            loadNotifications();
        } else {
            alert('Error marking messages as read');
        }
    } catch (err) {
        console.error('Error:', err);
        alert(err.message || 'Error marking messages as read');
    }
}

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
        dismissNotification(pendingDeleteId);
    } else if (pendingDeleteType === 'message') {
        deleteMessageConfirmed(pendingDeleteId);
    }
    closeDeleteConfirmModal();
}

function dismissNotification(notificationId) {
    const dismissed = JSON.parse(localStorage.getItem('dismissedNotifications') || '[]');
    dismissed.push(notificationId);
    localStorage.setItem('dismissedNotifications', JSON.stringify(dismissed));
    loadNotifications(); // Reload to hide the dismissed notification
}

async function deleteMessageConfirmed(messageId) {
    try {
        const result = await apiCall(`api/messages.php?id=${messageId}`, {
            method: 'DELETE'
        });

        if (result.success) {
            loadNotifications(); // Reload notifications
        } else {
            alert(result.error || 'Error deleting message');
        }
    } catch (err) {
        console.error('Error:', err);
        alert(err.message || 'Error deleting message');
    }
}

function openComposeMessage() {
    document.getElementById('composeSubject').value = '';
    document.getElementById('composeMessage').value = '';
    document.getElementById('composeMessageModal').style.display = 'flex';
}

function closeComposeMessage() {
    document.getElementById('composeMessageModal').style.display = 'none';
}

async function sendEmployeeMessage(e) {
    e.preventDefault();

    const subject = document.getElementById('composeSubject').value;
    const message = document.getElementById('composeMessage').value;

    try {
        // Get admin user ID (assuming admin ID is 1 from setup.sql)
        const usersData = await apiCall('api/users.php');
        const adminUser = usersData.users?.find(u => u.role === 'admin');

        if (!adminUser) {
            alert('Admin user not found');
            return;
        }

        const result = await apiCall('api/messages.php', {
            method: 'POST',
            body: JSON.stringify({
                to_user_id: adminUser.id,
                subject: subject,
                message: message
            })
        });

        if (result.success) {
            alert('Message sent successfully!');
            closeComposeMessage();
            loadNotifications(); // Reload notifications
        } else {
            alert(result.error || 'Error sending message');
        }
    } catch (err) {
        console.error('Error:', err);
        alert(err.message || 'Error sending message');
    }
}

// Request notification permission
if ('Notification' in window && Notification.permission === 'default') {
    Notification.requestPermission();
}
