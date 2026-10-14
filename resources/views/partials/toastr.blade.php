@if(Session::has('toastr'))
    <script>
        (function () {
            {!! admin_toastr_script() !!}
        }());
    </script>
@endif
