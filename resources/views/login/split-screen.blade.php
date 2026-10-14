{{-- Diseño "split-screen": imagen y cita a la izquierda (solo desde md), formulario a la derecha --}}
<body class="bg-light login-split-screen {{ config('admin.skin') }}">
	<div class="row g-0 min-vh-100">
		<div class="login-visual col-md-6 d-none d-md-block position-relative" style="background: {{ config('admin.login_background_image') ? "url('".config('admin.login_background_image')."') center/cover no-repeat" : 'linear-gradient(135deg, #343a40 0%, #6c757d 100%)' }};">
			@if(config('admin.login_quote'))
				<div class="position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center" style="background: rgba(0,0,0,0.35);">
					<figure class="login-quote text-white text-center px-3 m-0" style="max-width:760px;">
						<div class="p-4 p-md-5 rounded-3 mx-2" style="background: rgba(0,0,0,0.55); box-shadow: 0 10px 30px rgba(0,0,0,0.6);">
							<blockquote class="blockquote fs-4 fst-italic mb-2" style="color:#fff;">
								&ldquo;{{ config('admin.login_quote') }}&rdquo;
							</blockquote>
							@if(config('admin.login_quote_author'))
								<figcaption class="blockquote-footer text-white-50 mt-2 mb-0">{{ config('admin.login_quote_author') }}</figcaption>
							@endif
						</div>
					</figure>
				</div>
			@endif
		</div>
		<div class="col-md-6 d-flex align-items-center justify-content-center bg-body">
			<div class="container m-4" style="max-width:400px;">
				@include('admin::login.brand')
				@include('admin::login.form')
			</div>
		</div>
	</div>
</body>
