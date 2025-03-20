@section('title', 'ForgotPassword')
@include('layout.includes.head')
	<main>
		<div class="container">
			<section class="section register min-vh-100 d-flex flex-column align-items-center justify-content-center py-4">
				<div class="container">
					<div class="row justify-content-center">
						<div class="col-lg-4 col-md-6 d-flex flex-column align-items-center justify-content-center">

							<div class="d-flex justify-content-center py-4 text-center">
								<a href="{{ route('login') }}" class="">
								<img src="{{env('PROJECT_LOGO')}}" alt="" class="avms_logo_login">
									<!-- <img src="assets/img/logo/modern-hill-logo.png" alt=""> -->
								</a>
							</div><!-- End Logo -->

							<div class="card mb-3"> 

								<div class="card-body">

									<div class="pt-4 pb-2">
										<h5 class="login-title text-center pb-0 fs-4">Forgot Password?</h5>
										<p class="text-center small">Enter your email to reset your password.</p>

										@if(session()->has('message'))
										<div class="alert alert-success fade show" role="alert" id="header-alert">
											<i class="bi bi-check-circle me-1"></i>
											{{ session()->get('message') }}
										</div>
										@endif

										@if(session()->has('error'))

										<div class="alert alert-danger fade show" role="alert" id="header-alert">
											<i class="bi bi-exclamation-octagon me-1"></i>
											{{ session()->get('error') }}
										</div>
										@endif
									</div>

									<form class="row g-3 needs-validation" novalidate action="{{ url('forgot-password') }}" method="post">
										<input type="hidden" name="_token" value="{{ csrf_token() }}" />
										<div class="col-12">
											<label for="email" class="form-label">Email</label>
											

											<input type="text" name="email" class="form-control" id="email" required>
											
												<div class="invalid-feedback">Please enter your Email.</div>
											
										

											@if ($errors->has('email'))
												<span class="text-danger my-2">{{ $errors->first('email') }}</span>
												@endif
										</div>

										<div class="col-12">
											<div class="col">
												<a href="{{ route('login') }}">Login To Account</a>
											</div>
										</div>

										<div class="col-12">
											<button class="btn btn-primary w-100" type="submit">Forgot Password</button>
										</div>
									</form>
								</div>
							</div>

						</div>
					</div>
				</div>
			</section>
		</div>
	</main><!-- End #main -->
 @include('layout.sections.footer')