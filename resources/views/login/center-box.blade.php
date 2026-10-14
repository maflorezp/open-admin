{{-- Diseño "center-box": una tarjeta con marca, formulario y copyright, centrada sobre el fondo opcional.
     La marca va dentro de la tarjeta para que se lea sobre cualquier imagen de fondo. --}}
<body class="bg-light login-center-box {{ config('admin.skin') }}">
	<div class="d-flex justify-content-center align-items-center min-vh-100" @if(config('admin.login_background_image'))style="background: url('{{ config('admin.login_background_image') }}') center/cover no-repeat;"@endif>
		<div class="container m-4" style="max-width:400px;">
			<div class="bg-body p-4 shadow-sm rounded-3">
				@include('admin::login.brand')
				@include('admin::login.form')
			</div>
		</div>
	</div>
</body>
