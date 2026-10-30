@if (!request()->has('turbo'))
    @include('header', ['page' => 'banks'])
@endif

@php
$users = \App\Models\User::all();
$bUsers = explode(",", str_replace('"', "", $bank['users'] ?? ''));
@endphp

<nav aria-label="breadcrumb" class="my-4">
    <ol class="breadcrumb breadcrumb-style2 mb-0">
        <li class="breadcrumb-item"><a href="javascript:void(0);"><i class="ti ti-home me-1 fs-15"></i>Home</a></li>
        <li class="breadcrumb-item"><a href="javascript:void(0);"><i class="ti ti-cash me-1 fs-15"></i>Fund Requests</a></li>
        <li class="breadcrumb-item"><a href="javascript:void(0);"><i class="ti ti-building-bank me-1 fs-15"></i>Banks</a></li>
        <li class="breadcrumb-item active" aria-current="page"><i class="ti ti-building-bank me-1 fs-15"></i>{{ $bank['name'] }}</li>
     
    </ol>
</nav>

<div class="row">
    <div class="col-lg-6">
        <div class="card custom-card">
            <div class="card-header">
                <h6 class="card-title">Bank Users</h6>
            </div>
            <div class="card-body">
                <label class="mb-1">Bank Users</label>
                <select id="selectedUsers" multiple class="form-control" data-placeholder="Select Users">
                     <option></option>
                     @foreach ($users as $user)
                        <option {{ in_array($user['id'], $bUsers) ? 'selected' : '' }} value="{{ $user['id'] }}">{{ $user['name'] }}</option>
                     @endforeach
                </select>
                
                <button onclick="updateUsers(this);" class="btn btn-primary float-end mt-3"><i class="ti ti-users"></i>&nbsp;&nbsp;Update</button>
            </div>
        </div>
    </div>
    
    
</div>

<script>
   function updateUsers(btn) {
       btn.disabled = true;
       btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>';
       let users = document.querySelector("#selectedUsers");
       users = users.selectedOptions;
       let selecteds = [];
       for(let i = 0; i < users.length; i++) {
           selecteds.push('"'+users[i].value+'"');
       }
       let value = selecteds.join(",");
       $.post("/api/admin/update-bank-users",{
           bank : {{ $bank['id'] }},
           users : value
       },(data) => {
           notify("success","Users Updated Successfully");
            btn.disabled = false;
            btn.innerHTML = '<i class="ti ti-users"></i>&nbsp;&nbsp;Update';
       }).fail((xhr) => {
           let error = xhr.responseJSON.error;
           notify("error",error.message);
           btn.disabled = false;
           btn.innerHTML = '<i class="ti ti-users"></i>&nbsp;&nbsp;Update';
       });
   }
   function load() {
      
   }
</script>

<script class="afterLoadScript" type="text/plain">
    load();
</script>

<style>
    .select2-selection.select2-selection--multiple{
        height: 100px !important;
        overflow: scroll !important;
        padding: 10px !important;
    }
</style>

@if (!request()->has('turbo'))
    @include('footer')
@endif