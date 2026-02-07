/**
 * ChongDashboard - Notifications/Messages tab functions.
 */

async function loadUnreadCount() {
    const result = await apiCall('api/messages.php');
    if (result.unread_count !== undefined) {
        const badge = document.getElementById('notifBadge');
        if (badge) {
            badge.textContent = result.unread_count;
            badge.style.display = result.unread_count > 0 ? 'inline-flex' : 'none';
        }
        if (result.messages && result.messages.length > 0) {
            const adminMsg = result.messages.find(m => m.direction === 'received');
            if (adminMsg) adminUserId = adminMsg.from_user_id;
        }
    }
}

async function loadMessages() {
    const result = await apiCall('api/messages.php');
    if (result.messages) {
        messagesData = result.messages;
        const unread = result.unread_count || 0;
        const badge = document.getElementById('notifBadge');
        if (badge) {
            badge.textContent = unread;
            badge.style.display = unread > 0 ? 'inline-flex' : 'none';
        }
        if (!adminUserId && messagesData.length > 0) {
            const adminMsg = messagesData.find(m => m.direction === 'received');
            if (adminMsg) adminUserId = adminMsg.from_user_id;
        }
        renderNotifications(messagesData, notifCurrentFilter);
    }
}

function renderNotifications(messages, filter) {
    let filtered = messages;
    if (filter === 'unread') {
        filtered = messages.filter(m => m.direction === 'received' && !m.is_read);
    } else if (filter === 'sent') {
        filtered = messages.filter(m => m.direction === 'sent');
    } else if (filter === 'read') {
        filtered = messages.filter(m => m.direction === 'received' && m.is_read);
    }

    const totalCount = messages.length;
    const unreadCount = messages.filter(m => m.direction === 'received' && !m.is_read).length;
    const sentCount = messages.filter(m => m.direction === 'sent').length;

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
        filtered.forEach(m => {
            const isUnread = m.direction === 'received' && !m.is_read;
            const rowClass = isUnread ? 'unread' : '';

            const dirBadge = m.direction === 'sent'
                ? '<span class="dir-badge sent">Sent</span>'
                : '<span class="dir-badge received">Received</span>';

            const dot = isUnread
                ? '<div class="unread-dot"></div>'
                : '';

            const timeStr = formatRelativeTime(m.created_at);

            const fromTo = m.direction === 'received'
                ? escapeHtml(m.from_name)
                : 'To: ' + escapeHtml(m.to_name);

            const deleteBtn = m.direction === 'received' ? `
                <button class="act-icon danger" onclick="event.stopPropagation(); deleteMessage(${m.id})" title="Delete">
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                </button>
            ` : '';

            html += `
                <tr class="${rowClass}" onclick="viewMessage(${m.id})" style="cursor:pointer;">
                    <td style="width:24px;padding:10px 6px 10px 14px;">${dot}</td>
                    <td>${dirBadge}</td>
                    <td>${fromTo}</td>
                    <td class="td-subject">${escapeHtml(m.subject || '(No subject)')}</td>
                    <td class="td-time">${timeStr}</td>
                    <td style="text-align:center;">
                        <div class="action-group">
                            <button class="act-icon" onclick="event.stopPropagation(); viewMessage(${m.id})" title="View">
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

    const footLeft = document.getElementById('notifFootLeft');
    const footRight = document.getElementById('notifFootRight');
    if (footLeft) footLeft.textContent = `${filtered.length} message${filtered.length !== 1 ? 's' : ''}`;
    if (footRight) footRight.textContent = `${unreadCount} unread`;
}

function filterNotifications(filter) {
    notifCurrentFilter = filter;

    document.querySelectorAll('.notif-filters .f-chip').forEach(chip => {
        chip.classList.toggle('active', chip.dataset.filter === filter);
    });

    renderNotifications(messagesData, filter);
}

async function viewMessage(id) {
    const msg = messagesData.find(m => m.id == id);
    if (!msg) return;

    currentMessageId = id;

    document.getElementById('viewMessageSubject').textContent = msg.subject;
    document.getElementById('viewMessageFrom').textContent = msg.direction === 'received'
        ? 'From: ' + msg.from_name
        : 'To: ' + msg.to_name;
    document.getElementById('viewMessageDate').textContent = new Date(msg.created_at).toLocaleString();
    document.getElementById('viewMessageBody').textContent = msg.message;

    const replyBtn = document.getElementById('replyBtn');
    replyBtn.style.display = msg.direction === 'received' ? 'inline-flex' : 'none';

    openModal('viewMessageModal');

    if (msg.direction === 'received' && !msg.is_read) {
        await markMessageRead(id);
    }
}

async function markMessageRead(id) {
    const result = await apiCall('api/messages.php', 'PUT', { id: id });
    if (result.success) {
        const msg = messagesData.find(m => m.id == id);
        if (msg) msg.is_read = 1;

        const unread = messagesData.filter(m => m.direction === 'received' && !m.is_read).length;
        const badge = document.getElementById('notifBadge');
        if (badge) {
            badge.textContent = unread;
            badge.style.display = unread > 0 ? 'inline-flex' : 'none';
        }

        renderNotifications(messagesData, notifCurrentFilter);
    }
}

async function deleteMessage(id) {
    if (!confirm('Are you sure you want to delete this message?')) return;

    const result = await apiCall(`api/messages.php?id=${id}`, 'DELETE');
    if (result.success) {
        showToast('Message deleted', 'success');
        messagesData = messagesData.filter(m => m.id != id);
        renderNotifications(messagesData, notifCurrentFilter);
    } else {
        showToast(result.error || 'Failed to delete message', 'error');
    }
}

async function deleteCurrentMessage() {
    if (!currentMessageId) return;
    closeModal('viewMessageModal');
    await deleteMessage(currentMessageId);
}

async function markAllRead() {
    const result = await apiCall('api/messages.php', 'PUT', { mark_all: true });
    if (result.success) {
        showToast('All messages marked as read', 'success');
        loadMessages();
    }
}

function openNewMessageModal() {
    document.getElementById('newMessageForm').reset();
    openModal('newMessageModal');
}

function replyToMessage() {
    const msg = messagesData.find(m => m.id == currentMessageId);
    if (!msg) return;

    closeModal('viewMessageModal');

    const form = document.getElementById('newMessageForm');
    const subjectInput = form.querySelector('[name="subject"]');
    if (!msg.subject.startsWith('Re: ')) {
        subjectInput.value = 'Re: ' + msg.subject;
    } else {
        subjectInput.value = msg.subject;
    }

    adminUserId = msg.from_user_id;

    openModal('newMessageModal');
}

async function sendMessage(e) {
    e.preventDefault();

    if (!adminUserId) {
        const usersResult = await apiCall('api/users.php');
        if (usersResult.users) {
            const admin = usersResult.users.find(u => u.role === 'admin');
            if (admin) adminUserId = admin.id;
        }
    }

    if (!adminUserId) {
        showToast('Cannot find admin user', 'error');
        return;
    }

    const form = e.target;
    const data = {
        to_user_id: adminUserId,
        subject: form.subject.value,
        message: form.message.value
    };

    const result = await apiCall('api/messages.php', 'POST', data);
    if (result.success) {
        showToast('Message sent successfully', 'success');
        closeModal('newMessageModal');
        loadMessages();
    } else {
        showToast(result.error || 'Failed to send message', 'error');
    }
}
