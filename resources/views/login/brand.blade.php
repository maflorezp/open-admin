{{-- Logo (HTML de config('admin.logo'), igual que en la cabecera del panel) y subtítulo opcional --}}
<div class="login-brand text-center mb-3">
	<a href="{{ admin_url('/') }}" class="text-decoration-none text-dark h2 d-inline-block mb-2">{!! config('admin.logo', e(config('admin.name'))) !!}</a>
	@if(config('admin.login_subtitle'))
		<p class="login-subtitle text-muted small mb-0">{{ config('admin.login_subtitle') }}</p>
	@endif
</div>
