@if (!request()->has('turbo'))
    @include('header', ['page' => 'add_package'])
@endif <nav aria-label="breadcrumb" class="my-4">
  <ol class="breadcrumb breadcrumb-style2 mb-0">
    <li class="breadcrumb-item">
      <a href="javascript:void(0);">
        <i class="ti ti-home me-1 fs-15"></i>Home </a>
    </li>
    <li class="breadcrumb-item">
      <a href="javascript:void(0);">
        <i class="ti ti-box me-1 fs-15"></i>Packages </a>
    </li>
    <li class="breadcrumb-item">
      <a href="javascript:void(0);">
        <i class="ti ti-box me-1 fs-15"></i> {{ $package['name'] }} </a>
    </li>
    <li class="breadcrumb-item active" aria-current="page">
      <i class="ti ti-list me-1 fs-15"></i>Package Slabs
    </li>
  </ol>
</nav>
<div class="row">
  <div class="col-lg-3 mb-2">
    <div class="list-group">
      <a href="javascript:void(0);" class="list-group-item list-group-item-action active" onclick="changeService(this,'payin');">
        <div class="d-flex align-items-center">
          <div>
            <span class="avatar avatar-xs bg-success text-default avatar-rounded">
              <i class="ti ti-arrow-down-left"></i>
            </span>
          </div>
          <div class="ms-2">PayIn</div>
        </div>
      </a>
      <a href="javascript:void(0);" class="list-group-item list-group-item-action" onclick="changeService(this,'payout');">
        <div class="d-flex align-items-center">
          <div>
            <span class="avatar avatar-xs bg-danger avatar-rounded">
              <i class="ti ti-arrow-up-right"></i>
            </span>
          </div>
          <div class="ms-2">Payout</div>
        </div>
      </a>
    </div>
  </div>
  <div class="col-lg-9 mb-2">
    <div class="card custom-card" id="payinCard">
      <div class="card-header">
        <h6 class="card-title">Payin Charge Slabs</h6>
      </div>
      <div class="card-body px-0 pt-0">
        

          <form action="{{ route('admin.package.slabs.update') }}" method="POST">
              
              
            <input type="hidden" name="service" value="payin">
            <input type="hidden" name="package_id" value="{{ $package['id'] }}">
            
            
            <table class="table w-100">
                <thead class="table-primary">
                <tr>
                    <th style="padding: 12px 10px !important;" class="text-center">Min Amount</th>
                    <th style="padding: 12px 10px !important;" class="text-center">Max Amount</th>
                    <th style="padding: 12px 10px !important;" class="text-center">Charge Amount</th>
                    <th style="padding: 12px 10px !important;" class="text-center">Percent</th>
                    <th style="padding: 12px 10px !important;" class="text-center">GST</th>
                </tr>
                </thead>
                @for ($i = 1; $i < 7; $i++)
                <tr>
                    <td>
                         <input type="number" class="form-control text-center" step="0.01" value="{{ $slabs['payin'][$i-1]['min_amount'] ?? '' }}" placeholder="Min Amount" name="min_amount_{{ $i }}">
                    </td>
                     <td>
                        <input type="number" class="form-control text-center" step="0.01" value="{{ $slabs['payin'][$i-1]['max_amount'] ?? '' }}" placeholder="Max Amount" name="max_amount_{{ $i }}">
                    </td>
                     <td>
                        <input type="number" class="form-control text-center" step="0.01" value="{{ $slabs['payin'][$i-1]['amount'] ?? '' }}" placeholder="Charge Amount" name="charge_amount_{{ $i }}">
                    </td>
                     <td>
                    <div class="custom-toggle-switch toggle-sm ms-2">
                  <input id="size-smrecharge{{ $i }}" type="checkbox" value="percent" name="percent_{{ $i }}" {{ isset($slabs['payin'][$i-1]) && $slabs['payin'][$i-1]['percent'] == 1 ? 'checked' : '' }}>
                  <label for="size-smrecharge{{ $i }}" class="label-primary"></label>
                </div>
                    </td>
                     <td>
                        <div class="custom-toggle-switch toggle-sm ms-2">
                  <input id="size-smgrecharge{{ $i }}" type="checkbox" value="gst" name="gst_{{ $i }}" {{ isset($slabs['payin'][$i-1]) && $slabs['payin'][$i-1]['gst'] == 1 ? 'checked' : '' }}>
                  <label for="size-smgrecharge{{ $i }}" class="label-primary"></label>
                </div>
                    </td>
                </tr>
                @endfor
            </table>
            
           
            <button type="submit" class="btn btn-primary mt-4 float-end mx-3">Save Changes</button>
         
          </form>
   
      </div>
    </div>
    <div class="card custom-card d-none" id="payoutCard">
      <div class="card-header">
        <h6 class="card-title">Payout Charge Slabs</h6>
      </div>
      <div class="card-body px-0 pt-0">
        

          <form action="{{ route('admin.package.slabs.update') }}" method="POST">
              
              
            <input type="hidden" name="service" value="payout">
            <input type="hidden" name="package_id" value="{{ $package['id'] }}">
            
            
            <table class="table w-100">
                <thead class="table-primary">
                <tr>
                    <th style="padding: 12px 10px !important;" class="text-center">Min Amount</th>
                    <th style="padding: 12px 10px !important;" class="text-center">Max Amount</th>
                    <th style="padding: 12px 10px !important;" class="text-center">Charge Amount</th>
                    <th style="padding: 12px 10px !important;" class="text-center">Percent</th>
                    <th style="padding: 12px 10px !important;" class="text-center">GST</th>
                </tr>
                </thead>
                @for ($i = 1; $i < 7; $i++)
                <tr>
                    <td>
                         <input type="number" class="form-control text-center" step="0.01" value="{{ $slabs['payout'][$i-1]['min_amount'] ?? '' }}" placeholder="Min Amount" name="min_amount_{{ $i }}">
                    </td>
                     <td>
                        <input type="number" class="form-control text-center" step="0.01" value="{{ $slabs['payout'][$i-1]['max_amount'] ?? '' }}" placeholder="Max Amount" name="max_amount_{{ $i }}">
                    </td>
                     <td>
                        <input type="number" class="form-control text-center" step="0.01" value="{{ $slabs['payout'][$i-1]['amount'] ?? '' }}" placeholder="Charge Amount" name="charge_amount_{{ $i }}">
                    </td>
                     <td>
                    <div class="custom-toggle-switch toggle-sm ms-2">
                  <input id="size-smrecharg{{ $i }}" type="checkbox" value="percent" name="percent_{{ $i }}" {{ isset($slabs['payout'][$i-1]) && $slabs['payout'][$i-1]['percent'] == 1 ? 'checked' : '' }}>
                  <label for="size-smrecharg{{ $i }}" class="label-primary"></label>
                </div>
                    </td>
                     <td>
                        <div class="custom-toggle-switch toggle-sm ms-2">
                  <input id="size-smgrecharg{{ $i }}" type="checkbox" value="gst" name="gst_{{ $i }}" {{ isset($slabs['payout'][$i-1]) && $slabs['payout'][$i-1]['gst'] == 1 ? 'checked' : '' }}>
                  <label for="size-smgrecharg{{ $i }}" class="label-primary"></label>
                </div>
                    </td>
                </tr>
                @endfor
            </table>
            
    
          
            <button type="submit" class="btn btn-primary mt-4 float-end mx-3">Save Changes</button>
          
          </form>
   
      </div>
    </div>
  </div>
</div>
<script>
  function changeService(ele,service) {
      $(".list-group-item-action").removeClass("active");
      $(ele).addClass("active");
      if(service == "payin") {
          $("#payoutCard").addClass("d-none");
          $("#payinCard").removeClass("d-none");
      }else{
          $("#payinCard").addClass("d-none");
          $("#payoutCard").removeClass("d-none");
      }
  }
  
  function onRequestResponse(response) {
    notify("success", response.message);
  }

  function onRequestError(error) {
    notify("error", error.responseJSON.message);
  }
</script> @if (!request()->has('turbo'))
    @include('footer')
@endif