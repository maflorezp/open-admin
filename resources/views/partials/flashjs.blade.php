@if(Session::has('flashjs'))
    <script>
        {!! admin_flashjs_script() !!}
    </script>
@endif
