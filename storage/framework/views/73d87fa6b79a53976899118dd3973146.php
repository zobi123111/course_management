<?php $__env->startSection('title', 'Login'); ?>
<?php echo $__env->make('layout.includes.head', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<main>
    <div class="container">
    <section class="section register min-vh-100 d-flex flex-column align-items-center justify-content-center py-4">
        <div class="container">
          <div class="row justify-content-center">
            <div class="col-lg-4 col-md-6 d-flex flex-column align-items-center justify-content-center">

              <div class="d-flex justify-content-center py-4">
                <a href="index.html" class="logo d-flex align-items-center w-auto">
                  <img src="assets/img/logo.png" alt="">
                  <span class="d-none d-lg-block">Management</span>
                </a>
              </div><!-- End Logo -->

              <div class="card mb-3">

                <div class="card-body">

                  <div class="pt-4 pb-2">
                    <h5 class="card-title text-center pb-0 fs-4">Login to Your Account</h5>
                    <p class="text-center small">Enter your username & password to login</p>
                  </div>
                  <?php if(session()->has('message')): ?>
                        <div id="successMessage" class="alert alert-success fade show" role="alert">
                         <i class="bi bi-check-circle me-1"></i>
                                 <?php echo e(session()->get('message')); ?>

                          </div>
                    <?php endif; ?>
                  <form class="row g-3 needs-validation" novalidate id="login_form" >
                    <?php echo csrf_field(); ?>
                    <div class="col-12">
                      <label for="yourUsername" class="form-label">Username</label>
                      <div class="input-group has-validation">
                        <span class="input-group-text" id="inputGroupPrepend">@</span>
                        <input type="text"  class="form-control" id="yourUsername" name="email" required>
                        <div class="invalid-feedback">Please enter your username.</div>
                      </div>
                    </div>

                    <div class="col-12">
                      <label for="yourPassword" class="form-label">Password</label>
                      <input type="password" name="password" class="form-control" id="yourPassword" name="password"  required>
                      <div class="invalid-feedback">Please enter your password!</div>
                    </div>

                    <div class="col-12">
                      <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="remember" value="true" id="rememberMe">
                        <label class="form-check-label" for="rememberMe">Remember me</label>
                      </div>
                    </div>
                    <span class="text-danger credential_error"><?php echo e($errors->first('credentials_error')); ?></span>
                    <div class="col-12">
                      <button class="btn btn-primary w-100" type="submit">Login</button>
                    </div>
                    <div class="col-12">
                      <p class="small mb-0">Don't have account? <a href="pages-register.html">Create an account</a></p>
                       <p class="small mb-0"><a href="<?php echo e(url('forgot-password')); ?> ">Forgot Password ? </a></p>
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
<?php echo $__env->make('layout.sections.footer', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
<?php echo $__env->make('layout.includes.js', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

<script>
 $(document).ready(function(){
//Submit login form
$("#login_form").submit(function(event) {
        event.preventDefault();
        $('.error_e').html('');
        var formData = new FormData(this);
        $.ajax({
            type: 'POST',
            url: "<?php echo e(route('login')); ?>",
            data: formData,
            cache: false,
            processData: false,
            contentType: false,
            success: function(data) {
                if (data.success) {
                   window.location.href = "<?php echo e(route('dashboard')); ?>";
                }else{
                    $('.error_ee').html('');
                    $('.credential_error').html(data.credentials_error);
                }
            },
            error: function(xhr, textStatus, errorThrown) { 
                if (xhr.status === 422) {
                    var errorResponse = xhr.responseText;
                    var errorResponse = JSON.parse(errorResponse);
                    if (errorResponse) {
                       $('#otp_msg').hide();
                        $.each(errorResponse.errors, function(key, value) {                      
                            var html1 = '<p>' + value + '</p>';
                            $('#' + key + '_error').html(html1);
                        });
                    }
                }
            }
        });
    });

    setTimeout(function() {
  $('#successMessage').fadeOut('fast');
}, 1000);

 });
  </script>


<?php /**PATH D:\Courses-Management\resources\views/Login/index.blade.php ENDPATH**/ ?>