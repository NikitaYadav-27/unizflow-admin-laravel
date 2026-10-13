<?php if (!isset($_GET['turbo'])) {
    $page = "settlement_packages";
    echo view('header', get_defined_vars());
} ?>

<nav aria-label="breadcrumb" class="my-4">
    <ol class="breadcrumb breadcrumb-style2 mb-0">
        <li class="breadcrumb-item"><a href="javascript:void(0);" onclick="navigate('/admin/dashboard')"><i class="ti ti-home me-1 fs-15"></i>Home</a></li>
        <li class="breadcrumb-item"><a href="javascript:void(0);" onclick="navigate('/admin/settlement-packages')"><i class="ti ti-receipt me-1 fs-15"></i>Settlements</a></li>
        <li class="breadcrumb-item"><a href="javascript:void(0);" onclick="navigate('/admin/settlement-packages')"><i class="ti ti-box me-1 fs-15"></i>Settlement Packages</a></li>
        <li class="breadcrumb-item active" aria-current="page"><i class="ti ti-edit me-1 fs-15"></i>Edit Settlement Package</li>
    </ol>
</nav>

<div class="row">
    <div class="col-xl-9 col-lg-11 col-12">
        <div class="card custom-card">
            <div class="card-header justify-content-between align-items-center">
                <div class="card-title">
                    <i class="ti ti-edit me-2 text-primary fs-18"></i>Edit Settlement Package: <?= htmlspecialchars($package->name) ?>
                </div>
                <button onclick="navigate('/admin/settlement-packages')" class="btn btn-outline-secondary btn-sm">
                    <i class="ti ti-arrow-left me-1"></i>Back to List
                </button>
            </div>
            <div class="card-body">
                <form id="editSettlementPackageForm" onsubmit="event.preventDefault(); submitEditPackageForm();">
                    <input type="hidden" id="packageId" value="<?= htmlspecialchars($package_id) ?>">

                    <div class="row mb-4">
                        <div class="col-md-7">
                            <label class="form-label fw-semibold">Package Name <span class="text-danger">*</span></label>
                            <input type="text" id="packageName" class="form-control" value="<?= htmlspecialchars($package->name) ?>" required>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label fw-semibold">Settlement Frequency</label>
                            <select id="frequencySelect" class="form-select no-select2" onchange="onFrequencyChange(this.value)">
                                <option value="1">1 Time a Day</option>
                                <option value="2">2 Times a Day</option>
                                <option value="3">3 Times a Day</option>
                                <option value="4">4 Times a Day</option>
                                <option value="custom" selected>Custom / Existing Slots</option>
                            </select>
                        </div>
                    </div>

                    
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="fw-semibold mb-0">
                            <i class="ti ti-clock-hour-4 me-1 text-primary"></i>Time Slots Schedule
                            <span class="badge bg-primary-transparent ms-2" id="slotCountBadge">0 Slots</span>
                        </h6>
                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="addSlotRow()">
                            <i class="ti ti-plus me-1"></i>Add Another Slot
                        </button>
                    </div>

                    <div id="slotsContainer" class="d-flex flex-column gap-3 mb-4">
                        <!-- Dynamic slot rows populated by JavaScript -->
                    </div>

                    <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                        <button type="button" onclick="navigate('/admin/settlement-packages')" class="btn btn-light">
                            Cancel
                        </button>
                        <button type="submit" id="submitBtn" class="btn btn-primary">
                            <i class="ti ti-check me-1"></i>Update Settlement Package
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<template id="slotTemplate">
    <div class="slot-card card border shadow-none mb-0" data-slot-index="{index}">
        <div class="card-header bg-light py-2 px-3 d-flex justify-content-between align-items-center">
            <span class="fw-semibold fs-13 text-primary">
                <i class="ti ti-clock me-1"></i>Slot #{number}
            </span>
            <button type="button" class="btn btn-sm btn-outline-danger py-0 px-2 remove-slot-btn" onclick="removeSlotRow(this)">
                <i class="ti ti-trash fs-12"></i> Remove
            </button>
        </div>
        <div class="card-body p-3">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label fs-12 fw-semibold mb-1">
                        Settlement Execution Time <span class="text-danger">*</span>
                    </label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="ti ti-alarm"></i></span>
                        <input type="time" class="form-control slot-settlement-time" value="{settlementTime}" required>
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="form-label fs-12 fw-semibold mb-1">
                        Transactions From Time <span class="text-danger">*</span>
                    </label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="ti ti-calendar-time"></i></span>
                        <input type="time" class="form-control slot-txn-from" value="{txnFrom}" required>
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="form-label fs-12 fw-semibold mb-1">
                        Transactions To Time <span class="text-danger">*</span>
                    </label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="ti ti-calendar-time"></i></span>
                        <input type="time" class="form-control slot-txn-to" value="{txnTo}" required>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    // Initial slots from backend
    const initialSlots = <?= json_encode($package->slots->map(function($s) {
        return [
            'settlementTime' => $s->settlement_time,
            'txnFrom' => $s->txn_from_time,
            'txnTo' => $s->txn_to_time
        ];
    })) ?>;

    const defaultPresets = {
        1: [
            { settlementTime: "18:00", txnFrom: "00:00", txnTo: "18:00" }
        ],
        2: [
            { settlementTime: "12:00", txnFrom: "00:00", txnTo: "12:00" },
            { settlementTime: "18:00", txnFrom: "12:00", txnTo: "18:00" }
        ],
        3: [
            { settlementTime: "10:00", txnFrom: "00:00", txnTo: "10:00" },
            { settlementTime: "14:00", txnFrom: "10:00", txnTo: "14:00" },
            { settlementTime: "18:00", txnFrom: "14:00", txnTo: "18:00" }
        ],
        4: [
            { settlementTime: "09:00", txnFrom: "00:00", txnTo: "09:00" },
            { settlementTime: "12:00", txnFrom: "09:00", txnTo: "12:00" },
            { settlementTime: "15:00", txnFrom: "12:00", txnTo: "15:00" },
            { settlementTime: "18:00", txnFrom: "15:00", txnTo: "18:00" }
        ]
    };

    function renderSlots(slots) {
        const container = document.getElementById('slotsContainer');
        const template = document.getElementById('slotTemplate').innerHTML;
        container.innerHTML = '';

        if (!slots || slots.length === 0) {
            slots = defaultPresets[1];
        }

        slots.forEach((slot, idx) => {
            let html = template
                .replace(/{index}/g, idx)
                .replace(/{number}/g, idx + 1)
                .replace(/{settlementTime}/g, slot.settlementTime || '10:00')
                .replace(/{txnFrom}/g, slot.txnFrom || '00:00')
                .replace(/{txnTo}/g, slot.txnTo || '10:00');
            container.insertAdjacentHTML('beforeend', html);
        });

        updateSlotNumbers();
    }

    function onFrequencyChange(freq) {
        if (defaultPresets[freq]) {
            renderSlots(defaultPresets[freq]);
        }
    }

    function addSlotRow() {
        const container = document.getElementById('slotsContainer');
        const currentCount = container.querySelectorAll('.slot-card').length;
        const template = document.getElementById('slotTemplate').innerHTML;

        let defaultTime = "12:00";
        let defaultFrom = "00:00";
        let defaultTo = "12:00";

        const lastSlot = container.querySelector('.slot-card:last-child');
        if (lastSlot) {
            const lastTo = lastSlot.querySelector('.slot-txn-to').value;
            if (lastTo) {
                defaultFrom = lastTo;
            }
        }

        let html = template
            .replace(/{index}/g, currentCount)
            .replace(/{number}/g, currentCount + 1)
            .replace(/{settlementTime}/g, defaultTime)
            .replace(/{txnFrom}/g, defaultFrom)
            .replace(/{txnTo}/g, defaultTo);

        container.insertAdjacentHTML('beforeend', html);
        document.getElementById('frequencySelect').value = 'custom';
        updateSlotNumbers();
    }

    function removeSlotRow(btn) {
        const container = document.getElementById('slotsContainer');
        const cards = container.querySelectorAll('.slot-card');
        if (cards.length <= 1) {
            notify("error", "At least one settlement slot is required");
            return;
        }
        btn.closest('.slot-card').remove();
        document.getElementById('frequencySelect').value = 'custom';
        updateSlotNumbers();
    }

    function updateSlotNumbers() {
        const container = document.getElementById('slotsContainer');
        const cards = container.querySelectorAll('.slot-card');
        cards.forEach((card, idx) => {
            const header = card.querySelector('.card-header span');
            if (header) {
                header.innerHTML = `<i class="ti ti-clock me-1"></i>Slot #${idx + 1}`;
            }
        });
        const badge = document.getElementById('slotCountBadge');
        if (badge) {
            badge.innerText = `${cards.length} ${cards.length === 1 ? 'Slot' : 'Slots'}`;
        }
    }

    function submitEditPackageForm() {
        const packageId = document.getElementById('packageId').value;
        const name = document.getElementById('packageName').value.trim();
        if (name.length < 3) {
            notify("error", "Package name must be at least 3 characters");
            return;
        }

        const slotCards = document.querySelectorAll('#slotsContainer .slot-card');
        if (slotCards.length === 0) {
            notify("error", "Please configure at least one settlement slot");
            return;
        }

        const slots = [];
        let hasError = false;

        slotCards.forEach((card, idx) => {
            const settlementTime = card.querySelector('.slot-settlement-time').value;
            const txnFrom = card.querySelector('.slot-txn-from').value;
            const txnTo = card.querySelector('.slot-txn-to').value;

            if (!settlementTime || !txnFrom || !txnTo) {
                notify("error", `Slot #${idx + 1}: Please fill in all times`);
                hasError = true;
                return;
            }

            slots.push({
                slot_number: idx + 1,
                settlement_time: settlementTime,
                txn_from_time: txnFrom,
                txn_to_time: txnTo
            });
        });

        if (hasError) return;

        const submitBtn = document.getElementById('submitBtn');
        const origText = submitBtn.innerHTML;
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status"></span> Updating...';

        $.ajax({
            url: '/api/admin/settlement-package/update',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({
                package_id: packageId,
                name: name,
                slots: slots
            }),
            success: function (res) {
                submitBtn.disabled = false;
                submitBtn.innerHTML = origText;
                notify("success", res.message || "Settlement Package updated successfully", () => {
                    navigate('/admin/settlement-packages');
                });
            },
            error: function (xhr) {
                submitBtn.disabled = false;
                submitBtn.innerHTML = origText;
                let msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : "Failed to update package";
                notify("error", msg);
            }
        });
    }

    // Render with existing slots
    if (initialSlots && initialSlots.length > 0) {
        renderSlots(initialSlots);
        if ([1, 2, 3, 4].includes(initialSlots.length)) {
            document.getElementById('frequencySelect').value = String(initialSlots.length);
        } else {
            document.getElementById('frequencySelect').value = 'custom';
        }
    } else {
        renderSlots(defaultPresets[3]);
        document.getElementById('frequencySelect').value = '3';
    }
</script>

<?php if (!isset($_GET['turbo'])) {
    echo view('footer', get_defined_vars());
} ?>
