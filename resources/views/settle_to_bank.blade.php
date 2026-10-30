@if (!request()->has('turbo'))
    @include('header', ['page' => 'pending_settlements'])
@endif ?>

<nav aria-label="breadcrumb" class="my-4">
    <ol class="breadcrumb breadcrumb-style2 mb-0">
        <li class="breadcrumb-item"><a href="javascript:void(0);"><i class="ti ti-home me-1 fs-15"></i>Home</a></li>
        <li class="breadcrumb-item"><a href="javascript:void(0);"><i class="ti ti-receipt me-1 fs-15"></i>Settlements</a></li>
        <li class="breadcrumb-item"><a href="javascript:void(0);"><i class="ti ti-receipt me-1 fs-15"></i># {{ $settlement['txnid'] }}</a></li>
        <li class="breadcrumb-item active" aria-current="page"><i class="ti ti-building-bank me-1 fs-15"></i>Settle to Bank</li>
    </ol>
</nav>

<div class="row">
    <div class="col-lg-4">
        <div class="card custom-card">
            <div class="card-header">
                <h6 class="card-title"><i class="ti ti-building-bank me-1 fs-15"></i>&nbsp;&nbsp;Settle to Bank</h6>
            </div>
           <div class="card-body">
               <form action="{{ route('admin.settlements.settle-to-bank') }}" method="POST" novalidate>
                   
                  
                         <input type="hidden" name="id" value="{{ $settlement_id }}">
                         @php $bankAccount = \Illuminate\Support\Facades\DB::table('users_bank')->where('status', 'Approved')->where('user_id', $settlement['user_id'])->first(); @endphp
                         
                       
                          
                       
                    <div class="rounded bg-primary-transparent p-2 mb-2">
                        <table style="font-size: 10px;">
                            <tr>
                                <th>Name</th>
                                <th class="px-2">:</th>
                                <td>{{ $bankAccount->name ?? $bankAccount['name'] ?? 'NA' }}</td>
                            </tr>
                            <tr>
                                <th>Account</th>
                                <th class="px-2">:</th>
                                <td>{{ $bankAccount->account_number ?? $bankAccount['account_number'] ?? 'NA' }}</td>
                            </tr>
                            
                            <tr>
                                <th>IFSC</th>
                                <th class="px-2">:</th>
                                <td>{{ $bankAccount->account_ifsc ?? $bankAccount['account_ifsc'] ?? 'NA' }}</td>
                            </tr>
                            
                            <tr>
                                <th>Bank</th>
                                <th class="px-2">:</th>
                                <td>{{ $bankAccount->bank_name ?? $bankAccount['bank_name'] ?? 'NA' }}</td>
                            </tr>
                            
                            <tr>
                                <th>Branch</th>
                                <th class="px-2">:</th>
                                <td>{{ $bankAccount->branch ?? $bankAccount['branch'] ?? 'NA' }}</td>
                            </tr>
                        </table>
                    </div>
                       
                 
                   
                   <div class="form-group mb-2">
                       <label class="mb-1">Amount <span class="text-primary">*</span></label>
                       <input class="form-control" value="₹ {{ $settlement['net_amount'] }}" disabled>
                   </div>
                 
                   <div class="form-group mb-2">
                       <label class="mb-1">Reference No <span class="text-primary">*</span></label>
                       <input class="form-control" name="utr" placeholder="Enter Reference No" required>
                       <div class="invalid-feedback"></div>
                   </div>
                   
                   <div class="form-group mb-2">
                       <label class="mb-1">Remarks <span class="text-primary">*</span></label>
                       <textarea class="form-control" rows="3" name="remarks" validation="remarks" placeholder="Enter Remarks"></textarea>
                       <div class="invalid-feedback"></div>
                   </div>
                 
                   
                   <button type="submit" class="btn btn-primary mt-3 float-end btn-sm"><i class="ti ti-circle-plus"></i>&nbsp;&nbsp;Settle Amount</button>
               </form>
           </div>
        </div>
    </div>
</div>

<script>

   function onRequestResponse(response){
      notify("success",response.message,() => {
        navigate("/admin/settlements/pending");
      });
   }
   
   function onRequestError(error){
       notify("error",error.responseJSON.message);
   }

</script>

@if (!request()->has('turbo'))
    @include('footer')
@endif