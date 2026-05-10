@extends('layouts.app')
@section('title', 'Bulk Communications')
@section('page-title', 'Bulk SMS & WhatsApp Notifications')

@section('content')

<div class="row g-3">
    <!-- Left Column: Compose Message -->
    <div class="col-lg-8">
        <div class="chart-card h-100">
            <h6 class="section-title"><i class="bi bi-chat-square-text-fill me-2 text-primary"></i>Compose Broadcast Message</h6>
            <p class="chart-sub mb-4">Send simulated messages via WhatsApp or SMS to selected allottees.</p>

            <form id="broadcastForm">
                @csrf
                <!-- Target Selection -->
                <div class="mb-4">
                    <label class="form-label fw-bold">1. Select Recipients</label>
                    <div class="d-flex gap-3 mb-2">
                        <button type="button" class="btn btn-sm btn-outline-success" onclick="selectAll()">Select All ({{ $allottees->count() }})</button>
                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="selectDefaulters()">Select Defaulters (≥3 months)</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="clearSelection()">Clear</button>
                    </div>
                    <select name="allottee_ids[]" id="recipientSelect" class="form-select" multiple style="height: 150px; font-size: 12px;" required>
                        @foreach($allottees as $a)
                            <option value="{{ $a->id }}" data-due="{{ $a->due_months }}">
                                {{ $a->name }} ({{ $a->file_no }}) — Blk {{ $a->block_no }}/Flt {{ $a->flat_no }} — Due: {{ $a->due_months }} mo
                            </option>
                        @endforeach
                    </select>
                    <div class="form-text mt-1"><span id="selectedCount" class="fw-bold text-success">0</span> recipients selected.</div>
                </div>

                <!-- Template Selection -->
                <div class="mb-4">
                    <label class="form-label fw-bold">2. Choose Template (Optional)</label>
                    <select id="templateSelect" class="form-select form-select-sm mb-2" onchange="loadTemplate()">
                        <option value="">-- Custom Message --</option>
                        <option value="maintenance_due">Monthly Maintenance Due (Standard)</option>
                        <option value="reminder">Payment Reminder</option>
                        <option value="defaulter">Defaulter Notice (Urgent)</option>
                        <option value="receipt">Payment Receipt</option>
                    </select>
                </div>

                <!-- Message Body -->
                <div class="mb-4">
                    <label class="form-label fw-bold">3. Message Body</label>
                    <textarea name="message" id="messageBody" class="form-control" rows="5" required placeholder="Type your message here. Placeholders like [NAME], [MONTH], [AMOUNT], [PSID] will be replaced automatically."></textarea>
                    <div class="form-text">Available placeholders: <code>[NAME]</code>, <code>[MONTH]</code>, <code>[AMOUNT]</code>, <code>[PSID]</code>, <code>[MONTHS]</code>, <code>[REF]</code>, <code>[DATE]</code></div>
                </div>

                <!-- Channel & Send -->
                <div class="mb-3">
                    <label class="form-label fw-bold">4. Select Channel & Send</label>
                    <div class="d-flex gap-4 mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="channel" id="channelWhatsapp" value="whatsapp" checked>
                            <label class="form-check-label text-success fw-bold" for="channelWhatsapp"><i class="bi bi-whatsapp me-1"></i> WhatsApp</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="channel" id="channelSms" value="sms">
                            <label class="form-check-label text-primary fw-bold" for="channelSms"><i class="bi bi-chat-text-fill me-1"></i> SMS</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="channel" id="channelEmail" value="email" disabled>
                            <label class="form-check-label text-muted" for="channelEmail"><i class="bi bi-envelope-fill me-1"></i> Email (Unavailable)</label>
                        </div>
                    </div>
                    
                    <button type="submit" id="btnSend" class="btn btn-success w-100 py-2 fw-bold" style="font-size: 15px;">
                        <i class="bi bi-send-fill me-2"></i> Send Broadcast
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Right Column: Delivery Log -->
    <div class="col-lg-4">
        <div class="chart-card h-100">
            <h6 class="section-title"><i class="bi bi-clock-history me-2 text-warning"></i>Recent Broadcasts</h6>
            <div id="broadcastLogs" class="mt-3">
                @if(count($logs) === 0)
                    <div class="text-center text-muted py-5">
                        <i class="bi bi-inbox fs-1 mb-2 d-block"></i>
                        No broadcasts sent yet.
                    </div>
                @else
                    @foreach($logs as $log)
                        <div class="p-3 mb-2 rounded bg-light border">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <span class="badge {{ $log->channel === 'whatsapp' ? 'bg-success' : 'bg-primary' }} text-uppercase" style="font-size: 9px;"><i class="bi {{ $log->channel === 'whatsapp' ? 'bi-whatsapp' : 'bi-chat-text' }} me-1"></i> {{ $log->channel }}</span>
                                <small class="text-muted" style="font-size: 10px;">{{ \Carbon\Carbon::parse($log->created_at)->diffForHumans() }}</small>
                            </div>
                            <div style="font-size: 11px; color: #374151;" class="fw-bold mb-1">To: Allottee ID #{{ $log->allottee_id }}</div>
                            <div style="font-size: 11px; color: #6b7280; line-height: 1.4;">{{ Str::limit($log->message, 80) }}</div>
                        </div>
                    @endforeach
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Sending Overlay -->
<div id="sendingOverlay" class="position-fixed top-0 start-0 w-100 h-100 d-none" style="background: rgba(0,0,0,0.7); z-index: 1050; display: flex; flex-direction: column; align-items: center; justify-content: center;">
    <div class="spinner-border text-success mb-3" style="width: 4rem; height: 4rem;" role="status"></div>
    <h3 class="text-white fw-bold">Sending Broadcast...</h3>
    <p class="text-white-50" id="sendingProgress">Connecting to gateway...</p>
</div>

<!-- Success Modal -->
<div class="modal fade" id="successModal" tabindex="-1">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content text-center">
            <div class="modal-body p-4">
                <i class="bi bi-check-circle-fill text-success" style="font-size: 4rem;"></i>
                <h5 class="mt-3 text-success fw-bold">Broadcast Complete</h5>
                <p class="text-muted small mb-0" id="successMessage"></p>
                <button type="button" class="btn btn-sm btn-outline-secondary w-100 mt-4" data-bs-dismiss="modal" onclick="window.location.reload()">Done</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    const templates = @json($templates);
    const select = document.getElementById('recipientSelect');
    const countDisplay = document.getElementById('selectedCount');

    // Update count when selection changes
    select.addEventListener('change', () => {
        countDisplay.innerText = Array.from(select.selectedOptions).length;
    });

    function selectAll() {
        Array.from(select.options).forEach(opt => opt.selected = true);
        select.dispatchEvent(new Event('change'));
    }

    function selectDefaulters() {
        Array.from(select.options).forEach(opt => {
            opt.selected = parseInt(opt.getAttribute('data-due')) >= 3;
        });
        select.dispatchEvent(new Event('change'));
    }

    function clearSelection() {
        select.selectedIndex = -1;
        select.dispatchEvent(new Event('change'));
    }

    function loadTemplate() {
        const val = document.getElementById('templateSelect').value;
        if (val && templates[val]) {
            document.getElementById('messageBody').value = templates[val];
        } else {
            document.getElementById('messageBody').value = '';
        }
    }

    document.getElementById('broadcastForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const selected = Array.from(select.selectedOptions).length;
        if (selected === 0) {
            alert('Please select at least one recipient.');
            return;
        }

        const formData = new FormData(this);
        const overlay = document.getElementById('sendingOverlay');
        const progress = document.getElementById('sendingProgress');
        
        // Show overlay animation
        overlay.classList.remove('d-none');
        
        let msgs = 0;
        const interval = setInterval(() => {
            msgs += Math.ceil(selected / 10);
            if(msgs > selected) msgs = selected;
            progress.innerText = `Sent ${msgs} of ${selected} messages...`;
        }, 200);

        fetch("{{ route('notifications.send') }}", {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => res.json())
        .then(data => {
            setTimeout(() => {
                clearInterval(interval);
                overlay.classList.add('d-none');
                
                document.getElementById('successMessage').innerText = data.message;
                new bootstrap.Modal(document.getElementById('successModal')).show();
            }, 2500); // minimum 2.5s simulated delay
        })
        .catch(err => {
            clearInterval(interval);
            overlay.classList.add('d-none');
            alert('An error occurred while sending.');
            console.error(err);
        });
    });
</script>
@endpush
