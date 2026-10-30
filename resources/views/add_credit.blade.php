@if (!request()->has('turbo'))
    @include('header', ['page' => 'add_credits'])
@endif

<nav aria-label="breadcrumb" class="my-4">
    <ol class="breadcrumb breadcrumb-style2 mb-0">
        <li class="breadcrumb-item"><a href="javascript:void(0);"><i class="ti ti-home me-1 fs-15"></i>Home</a></li>
        <li class="breadcrumb-item"><a href="javascript:void(0);"><i class="ti ti-credit-card me-1 fs-15"></i>User Credits</a></li>
        <li class="breadcrumb-item active" aria-current="page"><i class="ti ti-circle-plus me-1 fs-15"></i>Add Credits</li>
    </ol>
</nav>

<div class="row">
    <div class="col-lg-4">
        <div class="card custom-card">
            <div class="card-header">
                <h6 class="card-title"><i class="ti ti-circle-plus me-1 fs-15"></i>&nbsp;&nbsp;Add Credits</h6>
            </div>
           <div class="card-body">
               <form action="{{ route('admin.credits.store') }}" method="POST" novalidate>
                   
                   <div class="form-group mb-2">
                       <label class="mb-1">User <span class="text-primary">*</span></label>
                       <select class="form-control" id="user" data-placeholder="Select User" required name="user_id">
                           <option></option>
                           @foreach (\App\Models\User::where("active", 1)->get() as $user)
                           <option value="{{ $user->id }}">{{ $user->name }}</option>
                           @endforeach
                       </select>
                       <div class="invalid-feedback"></div>
                   </div>
                   
                   <div class="form-group mb-2">
                       <label class="mb-1">Amount <span class="text-primary">*</span></label>
                       <input type="number" name="amount" validation="amount"  required class="form-control" placeholder="Enter Amount">
                       <div class="invalid-feedback"></div>
                   </div>
                   
                   <div class="form-group mb-2">
                       <label class="mb-1">Remarks <span class="text-primary">*</span></label>
                       <textarea type="number" rows="4" name="remarks" required class="form-control" placeholder="Enter Remarks"></textarea>
                       <div class="invalid-feedback"></div>
                   </div>
                   
                   
                   
                   <button type="submit" class="btn btn-primary mt-3 float-end btn-sm"><i class="ti ti-circle-plus"></i>&nbsp;&nbsp;Add Credits</button>
               </form>
           </div>
        </div>
    </div>
</div>

<script>

   function onRequestResponse(response){
      notify("success",response.message,() => {
        navigate("/admin/credits/pending");
      });
   }
   
   function onRequestError(error){
       notify("error",error.responseJSON.message);
   }

</script>

@if (!request()->has('turbo'))
    @include('footer')
@endif