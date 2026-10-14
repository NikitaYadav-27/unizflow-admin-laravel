<?php if (!isset($_GET['turbo'])) {
    $page = "provider_slabs";
    echo view('header', get_defined_vars());
} ?>

<div class="d-flex justify-content-between align-items-center my-4 flex-wrap gap-2">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb breadcrumb-style2 mb-0">
            <li class="breadcrumb-item"><a href="javascript:void(0);"><i class="ti ti-home me-1 fs-15"></i>Home</a></li>
            <li class="breadcrumb-item"><a href="javascript:void(0);"><i class="ti ti-building-bank me-1 fs-15"></i>Providers</a></li>
            <li class="breadcrumb-item active" aria-current="page"><i class="ti ti-list-check me-1 fs-15"></i>Provider Slabs</li>
        </ol>
    </nav>
    <div class="d-flex align-items-center gap-2">
        <span class="badge bg-primary-transparent fs-12 px-3 py-2">
            <i class="ti ti-receipt-tax me-1"></i> Global GST Rate: <strong><?= floatval($gstRate); ?>%</strong>
        </span>
    </div>
</div>

<div class="row">
    <!-- Provider Selection Column -->
    <div class="col-xl-3 col-lg-4 mb-3" style="max-height: 85vh; overflow-y: auto;">
        <div class="card custom-card">
            <div class="card-header">
                <h6 class="card-title mb-0"><i class="ti ti-building-bank me-2"></i>Select Provider</h6>
            </div>
            <div class="card-body p-2">
                <?php if($providers->count() === 0): ?>
                    <p class="text-muted text-center py-3 mb-0">No active providers found.</p>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach($providers as $p): 
                            $isActive = ($currentProvider && $currentProvider->id == $p->id);
                            $slabCount = \App\Models\Providerslab::where('provider_id', $p->id)->count();
                        ?>
                            <a href="javascript:void(0);" 
                               onclick="navigate('/admin/provider-slabs?provider=<?= $p->id; ?>')"
                               class="list-group-item list-group-item-action d-flex align-items-center justify-content-between p-2 mb-1 rounded border-0 <?= $isActive ? 'active-provider-item' : 'provider-item' ?>">
                                <div class="d-flex align-items-center gap-2">
                                    <img src="<?= $p->logo; ?>" onerror="this.onerror=null;this.src='/assets/images/company-logos/1.png';" style="width: 38px; height: 26px; object-fit: contain; background: #fff; padding: 2px; border-radius: 4px; border: 1px solid #e2e8f0;">
                                    <div>
                                        <div class="fw-semibold fs-13 <?= $isActive ? 'text-primary' : 'text-dark' ?>"><?= htmlspecialchars($p->name); ?></div>
                                        <small class="text-muted">
                                            <?= strtoupper($p->type); ?> &bull; <?= htmlspecialchars($p->gateway); ?>
                                        </small>
                                    </div>
                                </div>
                                <div>
                                    <span class="badge <?= $slabCount > 0 ? 'bg-success-transparent text-success' : 'bg-secondary-transparent text-muted' ?> fs-11">
                                        <?= $slabCount; ?> Slab<?= $slabCount === 1 ? '' : 's' ?>
                                    </span>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Slabs Form Column -->
    <div class="col-xl-9 col-lg-8 mb-3">
        <?php if(!$currentProvider): ?>
            <div class="card custom-card">
                <div class="card-body text-center py-5">
                    <i class="ti ti-building-bank text-muted" style="font-size: 3rem;"></i>
                    <h6 class="mt-3">No Provider Selected</h6>
                    <p class="text-muted">Please select a provider from the left list to manage their charge slabs.</p>
                </div>
            </div>
        <?php else: ?>
            <div class="card custom-card">
                <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div class="d-flex align-items-center gap-3">
                        <img src="<?= $currentProvider->logo; ?>" onerror="this.onerror=null;this.src='/assets/images/company-logos/1.png';" style="width: 50px; height: 30px; object-fit: contain; background: #fff; padding: 2px; border-radius: 4px; border: 1px solid #e2e8f0;">
                        <div>
                            <h6 class="card-title mb-0">
                                <?= htmlspecialchars($currentProvider->name); ?>
                                <span class="badge <?= $currentProvider->type === 'payin' ? 'bg-success-transparent text-success' : 'bg-danger-transparent text-danger' ?> ms-2">
                                    <?= strtoupper($currentProvider->type); ?>
                                </span>
                            </h6>
                            <small class="text-muted">Gateway: <?= htmlspecialchars($currentProvider->gateway); ?> &bull; Daily Limit: ₹<?= number_format($currentProvider->daily_limit ?? 0, 2); ?></small>
                        </div>
                    </div>
                    <div class="text-end">
                        <span class="badge bg-info-transparent fs-12">
                            <i class="ti ti-info-circle me-1"></i> Cost deducted from Admin Gross Profit
                        </span>
                    </div>
                </div>

                <div class="card-body">
                    <form action="/api/admin/update-provider-slab" method="POST" id="providerSlabsForm">
                        <input type="hidden" name="provider_id" value="<?= $currentProvider->id; ?>">

                        <div class="table-responsive">
                            <table class="table align-middle table-bordered text-center">
                                <thead class="table-primary">
                                    <tr>
                                        <th style="width: 50px;">Tier</th>
                                        <th style="min-width: 140px;">Min Amount (₹)</th>
                                        <th style="min-width: 140px;">Max Amount (₹)</th>
                                        <th style="min-width: 140px;">Charge Amount</th>
                                        <th style="width: 110px;">Type (Percent)</th>
                                        <th style="width: 110px;">Apply GST</th>
                                        <th style="min-width: 130px;">Summary Rate</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php for($i = 1; $i <= 8; $i++): 
                                        $slab = $providerSlabs[$i-1] ?? null;
                                        $minVal = $slab ? $slab->min_amount : '';
                                        $maxVal = $slab ? $slab->max_amount : '';
                                        $amtVal = $slab ? $slab->amount : '';
                                        $isPercent = $slab ? ($slab->percent == 1) : false;
                                        $isGst = $slab ? ($slab->gst == 1) : false;
                                    ?>
                                        <tr class="slab-row" data-index="<?= $i; ?>">
                                            <td class="fw-semibold text-muted">#<?= $i; ?></td>
                                            <td>
                                                <input type="number" step="0.01" class="form-control form-control-sm text-center slab-min" 
                                                       name="min_amount_<?= $i; ?>" value="<?= $minVal; ?>" placeholder="0.00" oninput="updateSummaryLabel(<?= $i; ?>)">
                                            </td>
                                            <td>
                                                <input type="number" step="0.01" class="form-control form-control-sm text-center slab-max" 
                                                       name="max_amount_<?= $i; ?>" value="<?= $maxVal; ?>" placeholder="0.00" oninput="updateSummaryLabel(<?= $i; ?>)">
                                            </td>
                                            <td>
                                                <input type="number" step="0.01" class="form-control form-control-sm text-center slab-amt" 
                                                       name="charge_amount_<?= $i; ?>" value="<?= $amtVal; ?>" placeholder="0.00" oninput="updateSummaryLabel(<?= $i; ?>)">
                                            </td>
                                            <td>
                                                <div class="custom-toggle-switch toggle-sm d-inline-block">
                                                    <input id="percent_switch_<?= $i; ?>" type="checkbox" value="percent" 
                                                           name="percent_<?= $i; ?>" <?= $isPercent ? 'checked' : ''; ?> onchange="updateSummaryLabel(<?= $i; ?>)">
                                                    <label for="percent_switch_<?= $i; ?>" class="label-primary mb-0"></label>
                                                </div>
                                                <div class="fs-10 text-muted" id="percent_label_<?= $i; ?>"><?= $isPercent ? 'Percent (%)' : 'Flat (₹)' ?></div>
                                            </td>
                                            <td>
                                                <div class="custom-toggle-switch toggle-sm d-inline-block">
                                                    <input id="gst_switch_<?= $i; ?>" type="checkbox" value="gst" 
                                                           name="gst_<?= $i; ?>" <?= $isGst ? 'checked' : ''; ?> onchange="updateSummaryLabel(<?= $i; ?>)">
                                                    <label for="gst_switch_<?= $i; ?>" class="label-primary mb-0"></label>
                                                </div>
                                                <div class="fs-10 text-muted" id="gst_label_<?= $i; ?>"><?= $isGst ? '+ GST' : 'Exempt' ?></div>
                                            </td>
                                            <td class="text-center align-middle">
                                                <span class="badge bg-light text-dark fs-12 fw-normal" id="rate_badge_<?= $i; ?>">
                                                    <?= $amtVal !== '' ? (($isPercent ? ($amtVal . '%') : ('₹ ' . $amtVal)) . ($isGst ? ' + GST' : '')) : '<span class="text-muted">Empty</span>' ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endfor; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Live Cost Simulator Widget -->
                        <div class="p-3 my-3 rounded" style="background: linear-gradient(135deg, rgba(32, 143, 132, 0.05) 0%, rgba(13, 110, 253, 0.05) 100%); border: 1px dashed #208f84;">
                            <div class="row align-items-center g-3">
                                <div class="col-md-4">
                                    <label class="form-label mb-1 fw-semibold text-dark"><i class="ti ti-calculator me-1"></i> Test Slabs (Live Cost Calculator)</label>
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text">₹</span>
                                        <input type="number" id="simAmount" class="form-control" placeholder="Enter test amount (e.g. 1000)" value="1000" oninput="runSimulator()">
                                    </div>
                                </div>
                                <div class="col-md-8">
                                    <div class="d-flex flex-wrap align-items-center gap-3" id="simResult">
                                        <div>
                                            <span class="text-muted fs-11 text-uppercase d-block">Matching Tier</span>
                                            <span class="fw-semibold text-dark" id="simTier">-</span>
                                        </div>
                                        <div>
                                            <span class="text-muted fs-11 text-uppercase d-block">Base Cost</span>
                                            <span class="fw-bold text-primary" id="simBase">₹ 0.00</span>
                                        </div>
                                        <div>
                                            <span class="text-muted fs-11 text-uppercase d-block">Provider GST (<?= floatval($gstRate); ?>%)</span>
                                            <span class="fw-bold text-warning" id="simGst">₹ 0.00</span>
                                        </div>
                                        <div>
                                            <span class="text-muted fs-11 text-uppercase d-block">Total Provider Cost</span>
                                            <span class="fw-bold text-success fs-15" id="simTotal">₹ 0.00</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mt-3 pt-2 border-top">
                            <span class="text-muted fs-12">
                                <i class="ti ti-info-circle me-1"></i> Empty tier rows will be skipped automatically upon saving.
                            </span>
                            <button type="submit" class="btn btn-primary d-flex align-items-center gap-1 px-4">
                                <i class="ti ti-device-floppy fs-16"></i> Save Slabs
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
    .provider-item {
        border-left: 3px solid transparent !important;
        transition: all 0.2s ease-in-out;
    }
    .provider-item:hover {
        background-color: #f8fafc !important;
        border-left: 3px solid #cbd5e1 !important;
    }
    .active-provider-item {
        background-color: rgba(32, 143, 132, 0.08) !important;
        border-left: 3px solid #208f84 !important;
    }
</style>

<script>
    const GLOBAL_GST_RATE = <?= floatval($gstRate); ?>;

    function updateSummaryLabel(index) {
        const amtInput = document.querySelector(`input[name="charge_amount_${index}"]`);
        const percentSwitch = document.querySelector(`input[name="percent_${index}"]`);
        const gstSwitch = document.querySelector(`input[name="gst_${index}"]`);
        const percentLabel = document.getElementById(`percent_label_${index}`);
        const gstLabel = document.getElementById(`gst_label_${index}`);
        const badge = document.getElementById(`rate_badge_${index}`);

        const isPercent = percentSwitch.checked;
        const isGst = gstSwitch.checked;
        const val = amtInput.value.trim();

        if (percentLabel) percentLabel.textContent = isPercent ? 'Percent (%)' : 'Flat (₹)';
        if (gstLabel) gstLabel.textContent = isGst ? '+ GST' : 'Exempt';

        if (badge) {
            if (val === '') {
                badge.innerHTML = '<span class="text-muted">Empty</span>';
            } else {
                let text = isPercent ? (val + '%') : ('₹ ' + val);
                if (isGst) text += ' + GST';
                badge.textContent = text;
            }
        }

        runSimulator();
    }

    function runSimulator() {
        const amtVal = parseFloat(document.getElementById('simAmount')?.value || 0);
        const simTier = document.getElementById('simTier');
        const simBase = document.getElementById('simBase');
        const simGst = document.getElementById('simGst');
        const simTotal = document.getElementById('simTotal');

        if (!simTier) return;

        let matched = false;

        for (let i = 1; i <= 8; i++) {
            const minInput = document.querySelector(`input[name="min_amount_${i}"]`);
            const maxInput = document.querySelector(`input[name="max_amount_${i}"]`);
            const amtInput = document.querySelector(`input[name="charge_amount_${i}"]`);
            const percentSwitch = document.querySelector(`input[name="percent_${i}"]`);
            const gstSwitch = document.querySelector(`input[name="gst_${i}"]`);

            if (!minInput || !maxInput || !amtInput) continue;

            const min = parseFloat(minInput.value);
            const max = parseFloat(maxInput.value);
            const charge = parseFloat(amtInput.value);

            if (!isNaN(min) && !isNaN(max) && !isNaN(charge) && amtVal >= min && amtVal <= max) {
                matched = true;
                const isPercent = percentSwitch.checked;
                const isGst = gstSwitch.checked;

                const baseCost = isPercent ? (amtVal * charge) / 100 : charge;
                const gstCost = isGst ? (baseCost * GLOBAL_GST_RATE) / 100 : 0;
                const totalCost = baseCost + gstCost;

                simTier.textContent = `Tier #${i} (₹${min} - ₹${max})`;
                simBase.textContent = '₹ ' + baseCost.toFixed(2);
                simGst.textContent = '₹ ' + gstCost.toFixed(2);
                simTotal.textContent = '₹ ' + totalCost.toFixed(2);
                break;
            }
        }

        if (!matched) {
            simTier.textContent = 'No matching slab';
            simBase.textContent = '₹ 0.00';
            simGst.textContent = '₹ 0.00';
            simTotal.textContent = '₹ 0.00';
        }
    }

    function onRequestResponse(response) {
        notify("success", response.message || "Provider slabs updated successfully!");
        setTimeout(function(){
            runSimulator();
        }, 100);
    }

    function onRequestError(error) {
        notify("error", error.responseJSON ? error.responseJSON.message : "Failed to update provider slabs");
    }

    window.onloads = window.onloads || [];
    window.onloads.push(runSimulator);
</script>

<script class="afterLoadScript" type="text/plain">
    runSimulator();
</script>

<?php if (!isset($_GET['turbo'])) {
    echo view('footer', get_defined_vars());
} ?>
