        <!-- Notifications Tab -->
        <div id="content-notifications" class="hidden" style="width: 100%;">
            <div id="notificationsCard" style="width: 100%;">
                <!-- Filters / Actions -->
                <div class="filters">
                    <button onclick="openComposeMessageAdmin()" class="ink-btn ink-btn-primary ink-btn-sm">+ New Message</button>
                    <button onclick="markAllReadAdmin()" class="ink-btn ink-btn-secondary ink-btn-sm">Mark All Read</button>
                </div>

                <div class="tbl-container" style="width: 100%;">
                    <div id="notificationsTableContainer" style="overflow-x: auto; width: 100%;">
                        <div id="notificationsContent" style="width: 100%;">
                            <!-- Notifications will be loaded here -->
                        </div>
                    </div>
                </div>
            </div>
        </div>