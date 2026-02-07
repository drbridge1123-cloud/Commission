        <!-- History Detail Modal -->
        <div id="historyDetailModal" class="modal-overlay" onclick="if(event.target === this) this.classList.remove('show')">
            <div class="modal-content" onclick="event.stopPropagation()" style="max-width: 560px; max-height: 90vh; border-radius: 12px; box-shadow: 0 25px 60px rgba(0, 0, 0, 0.12); overflow: hidden; display: flex; flex-direction: column;">
                <div style="background: #0f172a; padding: 12px 20px; display: flex; justify-content: space-between; align-items: center; flex-shrink: 0;">
                    <h3 style="font-size: 14px; font-weight: 600; color: #fff; margin: 0; font-family: 'Outfit', sans-serif;">Payment Detail</h3>
                    <span class="modal-close" onclick="document.getElementById('historyDetailModal').classList.remove('show')" style="cursor: pointer; font-size: 20px; color: rgba(255,255,255,0.7);">&times;</span>
                </div>
                <div style="padding: 16px 20px; overflow-y: auto; flex: 1;" id="historyDetailContent">
                </div>
            </div>
        </div>