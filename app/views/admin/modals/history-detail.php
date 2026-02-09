        <!-- History Detail Modal -->
        <div id="historyDetailModal" class="modal-overlay" onclick="if(event.target === this) this.classList.remove('show')">
            <div class="modal-content m-shell" onclick="event.stopPropagation()" style="max-width: 560px;">
                <div class="m-header">
                    <div class="m-header-title"><h3>Payment Detail</h3></div>
                    <button class="m-close" onclick="document.getElementById('historyDetailModal').classList.remove('show')">&times;</button>
                </div>
                <div class="m-body" id="historyDetailContent">
                </div>
            </div>
        </div>
