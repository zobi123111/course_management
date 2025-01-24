<?php $__env->startSection('title', 'ForgotPassword'); ?>
<?php echo $__env->make('layout.includes.head', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
	<main>
		<div class="container">
			<section class="section register min-vh-100 d-flex flex-column align-items-center justify-content-center py-4">
				<div class="container">
					<div class="row justify-content-center">
						<div class="col-lg-4 col-md-6 d-flex flex-column align-items-center justify-content-center">

							<div class="d-flex justify-content-center py-4">
								<a href="<?php echo e(route('login')); ?>" class="logo d-flex align-items-center w-auto">
									<!-- <img src="assets/img/logo/modern-hill-logo.png" alt=""> -->
								</a>
							</div><!-- End Logo -->

							<div class="card mb-3"> 

								<div class="card-body">

									<div class="pt-4 pb-2">
										<h5 class="login-title text-center pb-0 fs-4">Forgot Password?</h5>
										<p class="text-center small">Enter your email to reset your password.</p>

										<?php if(session()->has('message')): ?>
										<div class="alert alert-success fade show" role="alert" id="header-alert">
											<i class="bi bi-check-circle me-1"></i>
											<?php echo e(session()->get('message')); ?>

										</div>
										<?php endif; ?>

										<?php if(session()->has('error')): ?>

										<div class="alert alert-danger fade show" role="alert" id="header-alert">
											<i class="bi bi-exclamation-octagon me-1"></i>
											<?php echo e(session()->get('error')); ?>

										</div>
										<?php endif; ?>
									</div>

									<form class="row g-3 needs-validation" novalidate action="<?php echo e(url('forgot-password')); ?>" method="post">
										<input type="hidden" name="_token" value="<?php echo e(csrf_token()); ?>" />
										<div class="col-12">
											<label for="email" class="form-label">Email</label>
											

											<input type="text" name="email" class="form-control" id="email" required>
											
												<div class="invalid-feedback">Please enter your Email.</div>
											
										

											<?php if($errors->has('email')): ?>
												<span class="text-danger my-2"><?php echo e($errors->first('email')); ?></span>
												<?php endif; ?>
										</div>

										<div class="col-12">
											<div class="col">
												<a href="<?php echo e(route('login')); ?>">Login To Account</a>
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
 <?php echo $__env->make('layout.sections.footer', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\course_management\resources\views/auth/forgot-password.blade.php ENDPATH**/ ?>