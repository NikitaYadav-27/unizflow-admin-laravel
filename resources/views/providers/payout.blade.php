@php
 $payouts = \App\Models\Providers::where("type", "payout")->where("active", 1)->get();
 $tabId = request()->query('tab');
 $currentProvider = $tabId ? $payouts->firstWhere('id', $tabId) : $payouts->first();
@endphp
<div class="row">
  <div class="col-lg-3 mb-2" style="height: 86vh !important; overflow: scroll;">
      <button type="button" class="btn btn-primary-light w-100 mb-2 d-flex align-items-center justify-content-center gap-1" data-bs-toggle="modal" data-bs-target="#addProviderModal">
        <i class="ti ti-plus fs-15"></i> Add Provider
      </button>

      @foreach ($payouts as $provider)  
      <div class="card mb-2 provider {{ ($currentProvider && ($currentProvider->id ?? $currentProvider['id']) == ($provider->id ?? $provider['id'])) ? 'active' : '' }}" onclick="navigate('{{ route('admin.providers.index', ['type' => 'payout']) }}?tab={{ $provider->id ?? $provider['id'] }}')">
        <div class="card-body d-flex gap-3 align-items-center">
          <div>
            <img src="{{ $provider->logo ?? $provider['logo'] }}" onerror="this.onerror=null;this.src='/assets/images/company-logos/1.png';" style="width: 60px; height: 30px; object-fit: contain;">
          </div>
          <div class="title fs-14 ms-2 fw-semibold">{{ $provider->name ?? $provider['name'] }}</div>
        </div>
      </div>
      @endforeach
      
 
  </div>
  <div class="col-lg-9 mb-2">
     @if ($currentProvider)
       @include("providers.payout.solitpay", ["provider" => $currentProvider])
     @else
        <div class="card custom-card">
          <div class="card-body text-center py-5">
            <i class="ti ti-building-bank text-muted" style="font-size: 3rem;"></i>
            <h6 class="mt-3">No Payout Providers Found</h6>
            <p class="text-muted">Click the button below to add your first payout provider.</p>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addProviderModal">
              <i class="ti ti-plus me-1"></i> Add Provider
            </button>
          </div>
        </div>
     @endif
  
  </div>
</div>

<style>
    .card.provider{
       filter: grayscale(1) !important;
       cursor: pointer;
    }
    .card.provider .title{
        color: gray;
    }
    
    .card.provider.active {
        filter: grayscale(0) !important;
        border: 2px solid rgb(var(--primary-rgb));
    }
    
    .card.provider.active .title{
        color: rgb(var(--primary-rgb)) !important;
    }
</style>
