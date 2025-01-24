<!DOCTYPE html>
<html lang="en">
@include('layout.includes.head')

<body>
  <main>
    <div class="container">
      <section class="section resetpassword min-vh-100 d-flex flex-column align-items-center justify-content-center py-4">
        <div class="container">
          <div class="row justify-content-center">
            <div class="col-lg-4 col-md-6 d-flex flex-column align-items-center justify-content-center">

              <div class="card mb-3">

                <div class="card-body">

                  <div class="pt-4 pb-2">
                    <h5 class="login-title text-center pb-0 fs-4">Reset Password</h5>
                    <p class="text-center small">Enter New password to Reset Password</p>

                    @if(session()->has('message'))
                        <div class="alert alert-success fade show" role="alert">
                                    <i class="bi bi-check-circle me-1"></i>
                                    {{ session()->get('message') }}
                        </div>
                    @endif
                    @if(session()->has('error'))
                    <div class="alert alert-danger fade show" role="alert">
                                    <i class="bi bi-exclamation-octagon me-1"></i>
                                    {{ session()->get('error') }}
                    </div>
                    @endif
                  </div>

                  <form class="row g-3 needs-validation" novalidate action="{{ route('submit.reset.password') }}" method="post">
                    <!-- csrf token -->
                    <input type="hidden" name="_token" value="{{ csrf_token() }}" />
                    <!-- reset token -->
                    <input type="hidden" name="token" value="{{ $token }}">
                    <div class="col-12">
                      <label for="email" class="form-label">Email</label>
                      <div class="input-group has-validation">
                        <span class="input-group-text" id="inputGroupPrepend">@</span>
                        <input type="text" name="email" class="form-control" id="email" value="{{ $email }}" readonly>
                        <div class="invalid-feedback">Please enter your Email.</div>
                      </div>
                    </div>

                    <div class="col-12">
                      <label for="password" class="form-label">New Password</label>
                      <input type="password" name="password" class="form-control" id="password" required>
                      <div class="invalid-feedback">Please enter your password!</div>
                      @if ($errors->has('password'))
                      <span class="text-danger">{{ $errors->first('password') }}</span>
                      @endif
                    </div>

					          <div class="col-12">
                      <label for="password_confrimation" class="form-label">Confirm Password</label>
                      <input type="password" name="password_confirmation" class="form-control" id="password_confrimation" required>
                      <div class="invalid-feedback">Please enter your password!</div>
                    </div>
                    <div class="col-12">
                      <button class="btn btn-primary w-100" type="submit">Reset Password</button>
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
</body>

</html>