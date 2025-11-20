@if(Session::has('success'))
    <div class="alert alert-success alert-dismissible">
        <a href="javascript:void(0);" class="close text-decoration-none" aria-hidden="true">×</a>
        <h4 class="text-start"><i class="icon fa fa-check"></i> Success!</h4>
        {{ Session::get('success') }}
    </div>
@endif

@if(Session::has('error'))
    <div class="alert alert-danger alert-dismissible">
        <a href="javascript:void(0);" class="close text-decoration-none" aria-hidden="true">×</a>
        <h4 class="text-start"><i class="icon fa fa-ban"></i> Error!</h4>
       {{ Session::get('error')  }}
    </div>
@endif