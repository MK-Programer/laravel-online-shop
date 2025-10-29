@extends('admin.layouts.app', ['tab_name' => 'Edit Sub Category'])

@section('content')
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid my-2">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Edit Sub Category</h1>
                </div>
                <div class="col-sm-6 text-right">
                    <a href="{{ route('admin.sub-categories.index') }}" class="btn btn-primary">Back</a>
                </div>
            </div>
        </div>
        <!-- /.container-fluid -->
    </section>
    <!-- Main content -->
    <section class="content">
        <!-- Default box -->
        <div class="container-fluid">
            <div id="alert-container"></div>
            <form action="{{ route('admin.sub-categories.update', ['record' => $subCategory->id]) }}" method="put" id="sub_category_form" name="sub_category_form">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label for="category">Category</label>
                                    <select name="category" id="category" class="form-control">
                                        <option value="">Select Category</option>
                                        @foreach ($categories as $id => $name)
                                            <option value="{{ $id }}" {{ $subCategory->category_id == $id ? 'selected' : '' }}>{{ $name }}</option>                                            
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="name">Name</label>
                                    <input type="text" name="name" id="name" class="form-control"
                                        placeholder="Name" value="{{ $subCategory->name }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="slug">Slug</label>
                                    <input type="text" name="slug" id="slug" class="form-control"
                                        placeholder="Slug" value="{{ $subCategory->slug }}" readonly>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="status">Status</label>
                                    <select name="status" id="status" class="form-control">
                                        <option value="1" {{ $subCategory->status == 1 ? 'selected' : '' }}>Active</option>
                                        <option value="0" {{ $subCategory->status == 0 ? 'selected' : '' }}>Blocked</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="pb-5 pt-3">
                    <button type="submit" class="btn btn-primary">Update</button>
                    <a href="{{ route('admin.sub-categories.index') }}" class="btn btn-outline-dark ml-3">Cancel</a>
                </div>
            </form>
        </div>
        <!-- /.card -->
    </section>
    <!-- /.content -->
@endsection

@section('js')
    <script>
        $('#sub_category_form').submit(function(event) {
            event.preventDefault();

            $.ajax({
                url: $(this).attr('action'),
                type: $(this).attr('method'),
                data: $(this).serializeArray(),
                dataType: 'json',
                beforeSend: function() {
                    removeAlert();
                    hideValidationErrors('sub_category_form');
                },
                success: function(response) {
                    resetForm('sub_category_form');
                    showSuccess(response.message);
                },
                error: function(xhr, status, error) {
                    console.log(xhr);
                    if (xhr.status == 422) {
                        showValidationErrors(xhr.responseJSON.errors);
                    } else {
                        const message = xhr.responseJSON?.error || 'An unexpected error occurred.';
                        showError(message);
                    }
                },
            });
        });

        $('#name').change(function() {
            let name = $(this).val();
            let slug = slugify(name);
            $('#slug').val(slug);
        });
    </script>
@endsection
