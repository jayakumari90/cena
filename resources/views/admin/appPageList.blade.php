@extends('layouts.admin')
 
@section('content')

<script src = "{{asset('public/assets/js/jquery.dataTables.min.js')}}" defer ></script>
<link rel="stylesheet" href="{{asset('public/assets/css/summernote.min.css') }}" integrity="sha512-KbfxGgOkkFXdpDCVkrlTYYNXbF2TwlCecJjq1gK5B+BYwVk7DGbpYi4d4+Vulz9h+1wgzJMWqnyHQ+RDAlp8Dw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
<div class="main-panel">
    <div class="content-wrapper">
      <div class="row">
        
        <div class="col-lg-12 grid-margin stretch-card">
          <div class="card">
            <div class="card-body">
              <h4 class="card-title">App Pages</h4>
              
              <div class="table-responsive pt-3">
                <table class="table table-bordered datatable">
                  <thead>
                    <tr>
                        <th>#</th>
                        <th>Title</th>
                        <th>Slug</th>
                        <th width="150px">Action</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
                <tfoot>
                    <tr>
                        <th>#</th>
                        <th>Title</th>
                        <th>Slug</th>
                        <th>Action</th>
                    </tr>
                </tfoot>
                </table>
              </div>
            </div>
          </div>
        </div>
        
      </div>
    </div>
    <!-- content-wrapper ends -->
    
  </div>
<div id="editAppPageModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="editAppPageModalTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editAppPageModalTitle">Edit AppPage</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <form method="post" action="{{route('appPage.edit')}}" enctype="multipart/form-data">
                    @csrf
                    <div class="form-group">
                        <label for="page_title">Title</label>
                        <input type="text" class="form-control" name="page_title" required id="page_title" readonly>
                    </div>
                    <div class="form-group">
                        <label for="page_slug">Slug</label>
                        <input type="text" class="form-control" name="page_slug" required id="page_slug" readonly>
                    </div>
                    <div class="form-group">
                        <label for="page_content">Content</label>
                        <textarea class="form-control" name="page_content" required id="page_content"></textarea>
                    </div>
                    <div class="form-group">
                        <input type="hidden" value="" name="page_id" id="edit_appPage_id">
                        <button type="submit" class="btn btn-primary m-auto d-block rounded-0" style="width:200px">Update AppPage</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<script src="{{ asset('public/assets/js/summernote.js') }}" defer></script>
  <script type="text/javascript">

      $(function () { 
        
        var table = $('.datatable').DataTable({
            processing: true,
            serverSide: true,
            // pageLength: 2,
            ajax: "{{ route('appPage.list') }}",
            columns: [{
                    data: 'DT_RowIndex',
                    name: 'DT_RowIndex'
                },
                {
                    data: 'title',
                    name: 'title'
                },
                {
                    data: 'slug',
                    name: 'slug'
                },
                {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false
                },
            ]
        });
        
      });
      function getAppPageDetail(appPage_id) {
        $.ajaxSetup({
            headers: {
              'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        jQuery.ajax({
            url: "{{ route('appPage.detail') }}",
            method: 'post',
            data: {
                id: appPage_id,
            },
            success: function(result) {
                $('#editAppPageModal #page_title').val(result.title);
                $('#editAppPageModal #edit_appPage_id').val(result.id);
                $('#editAppPageModal #page_slug').val(result.slug);
                $('#page_content').summernote('code', result.content);
                $('#editAppPageModal').modal('show');

            }
        });
    }
    </script>
@endsection