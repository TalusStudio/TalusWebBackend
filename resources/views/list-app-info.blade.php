@extends('layouts.master')

@section('title', 'Apps')

@section('content')
    <div class="container py-2">
        @include('layouts.build-modal')
        <div class="card">
            <div class="card-header bg-dark text-white font-weight-bold">
                <span class="fa-stack fa-lg">
                    <i class="fa fa-square-o fa-stack-2x"></i>
                    <i class="fa fa-database fa-stack-1x"></i>
                </span>
                Apps
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-borderless table-hover">
                        <thead>
                        <tr class="text-dark text-light">
                            <th scope="col" class="text-center col-sm-2 d-none d-sm-table-cell" data-bs-toggle="tooltip" data-bs-placement="top" title="ID">
                            </th>
                            <th scope="col" class="text-center col-sm-2" data-bs-toggle="tooltip" data-bs-placement="top" title="App">
                            </th>
                            <th scope="col" class="text-center col-sm-2" data-bs-toggle="tooltip" data-bs-placement="top" title="Latest Build Status">
                            </th>
                            <th scope="col" class="text-center col-sm-2" data-bs-toggle="tooltip" data-bs-placement="top" title="Build App">
                            </th>
                            <th scope="col" class="text-center col-sm-2" data-bs-toggle="tooltip" data-bs-placement="top" title="Update App">
                            </th>
                        </tr>
                        </thead>
                        <tbody>
                            @include('layouts.app-info-list')
                        </tbody>
                    </table>
                    <div class="d-flex justify-content-center">{{ $appInfos->links() }}</div>
                </div>
            </div>
            <div class="card-footer text-muted">
                <i class="fa fa-hashtag" aria-hidden="true"></i>
                <span>Total app count: {{ $appInfos->total() }}</span>
                <span class="float-right">Current builds: {{ $currentBuildCount }} </span>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script type="text/javascript">
        $(function () {
            $('[data-toggle="popover"]').popover()
        })

        $('.popover-dismiss').popover({
            trigger: 'focus'
        })

        $(document).ready(function () {
            $('#exampleModal').on('show.bs.modal', function (event) {

                document.getElementById('is_workspace').checked = getCookie('target_is_ws');

                // Get the button that triggered the modal
                var button = $(event.relatedTarget);

                // Extract value from the custom data-* attribute
                var appId = button.data("title");
                var projectName = button.data("project");

                setCookie('target_app_id', appId, 1);
                setCookie('target_project_name', projectName, 1);

                updateLink(appId);
            });
        });

        function updateLink(appId) {
            if (appId == null) {
                console.log('app_id: ' + getCookie('target_app_id'))
            } else {
                console.log('app_id:' + appId);
            }

            var isWorkspace = document.getElementById('is_workspace').checked;
            console.log('is_workspace: ' + isWorkspace);

            var tfVersion = document.getElementById('tf_version').value;
            console.log('tf_version: ' + tfVersion);

            var tfCustomVersion = document.getElementById('tf_custom_version').value;
            console.log('tf_custom_version: ' + tfCustomVersion);

            var tfBuildNumber = document.getElementById('tf_build_version').value;
            console.log('tf_build_number: ' + tfBuildNumber)

            var buildUrl = "dashboard/build-app/" + getCookie('target_app_id') + '/' + isWorkspace + '/' + tfVersion + '/' + tfCustomVersion + '/' + tfBuildNumber;
            document.getElementById('build_link').href = buildUrl;
        }
    </script>
@endsection
