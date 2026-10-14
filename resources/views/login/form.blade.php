{{-- Formulario de login compartido por los diseños "center-box" y "split-screen" --}}
@if($errors->has('attempts'))
	<div class="alert alert-danger m-0 text-center">{{ $errors->first('attempts') }}</div>
@else
<form action="{{ admin_url('auth/login') }}" method="post">

	<input type="hidden" name="_token" value="{{ csrf_token() }}">
	<div class="mb-3">

		@if($errors->has('username'))
			<div class="alert alert-danger">{{ $errors->first('username') }}</div>
		@endif

		<label for="username" class="form-label">{{ __('admin.username') }}</label>
		<div class="input-group mb-3">
			<span class="input-group-text"><i class="icon-user"></i></span>
			<input type="text" class="form-control" placeholder="{{ __('admin.username') }}" name="username" id="username" value="{{ old('username') }}" required>
		</div>
	</div>

	<div class="mb-3">
		<label for="password" class="form-label">{{ __('admin.password') }}</label>
		<div class="input-group mb-3">
			<span class="input-group-text"><i class="icon-eye"></i></span>
			<input type="password" class="form-control" placeholder="{{ __('admin.password') }}" name="password" id="password" required>
		</div>

		@if($errors->has('password'))
			<div class="alert alert-danger">{{ $errors->first('password') }}</div>
		@endif
	</div>

	@if(config('admin.auth.remember'))
	<div class="mb-3 form-check">
		<input type="checkbox" class="form-check-input" name="remember" id="remember" value="1" {{ old('remember') ? 'checked' : '' }}>
		<label class="form-check-label" for="remember">{{ __('admin.remember_me') }}</label>
	</div>
	@endif

	<button type="submit" class="btn btn-secondary w-100">{{ __('admin.login') }}</button>

</form>
@endif

@if(config('admin.login_copyright'))
	<div class="login-copyright text-center small text-muted mt-4">{{ config('admin.login_copyright') }}</div>
@endif
