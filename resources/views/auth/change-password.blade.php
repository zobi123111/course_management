<!DOCTYPE html>
<html lang="en">
@include('layout.includes.head')

<body>
  {{-- <main>
    <div class="container">
      <section class="section resetpassword min-vh-100 d-flex flex-column align-items-center justify-content-center py-4">
        <div class="container">
          <div class="row justify-content-center">
            <div class="col-lg-4 col-md-6 d-flex flex-column align-items-center justify-content-center">

              <div class="card mb-3">

                <div class="card-body">

                  <div class="pt-4 pb-2">
                    <h5 class="login-title text-center pb-0 fs-4">Change Password</h5>
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

                   <form action="{{ route('update-password') }}" method="POST">
                        @csrf
                        <div class="form-group">
                            <label for="current_password">Current Password</label>
                            <input type="password" id="current_password" name="current_password" class="form-control" required>
                        </div>
                    
                        <div class="form-group">
                            <label for="new_password">New Password</label>
                            <input type="password" id="new_password" name="new_password" class="form-control" required>
                        </div>
                    
                        <div class="form-group">
                            <label for="new_password_confirmation">Confirm New Password</label>
                            <input type="password" id="new_password_confirmation" name="new_password_confirmation" class="form-control" required>
                        </div>
                    
                        <button type="submit" class="btn btn-primary mt-3">Change Password</button>
                    </form>  
                </div>
              </div>

            </div>
          </div>
        </div>
      </section>
    </div>
  </main> --}}
  <main>
    <div class="container">
      <section class="section resetpassword min-vh-100 d-flex flex-column align-items-center justify-content-center py-4">
        <div class="container">
          <div class="row justify-content-center">
            <div class="col-lg-4 col-md-6 d-flex flex-column align-items-center justify-content-center">
  
              <div class="card mb-3">
  
                <div class="card-body">
  
                  <div class="pt-4 pb-2">
                    <h5 class="login-title text-center pb-0 fs-4">Change Password</h5>
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
  
                  <form id="password-change-form" class="row g-3 needs-validation" action="{{ route('update-password') }}" method="POST">
                      @csrf
                      <div class="form-group">
                          <label for="current_password" class="form-label">Current Password</label>
                          <input type="password" id="current_password" name="current_password" class="form-control" required>
                      </div>
                  
                      <div class="form-group">
                          <label for="new_password" class="form-label">New Password</label>
                          <input type="password" id="new_password" name="new_password" class="form-control" required>
                      </div>
                  
                      <div class="form-group">
                          <label for="new_password_confirmation" class="form-label">Confirm New Password</label>
                          <input type="password" id="new_password_confirmation" name="new_password_confirmation" class="form-control" required>
                          <div id="password-error" class="text-danger mt-2" style="display: none;">Passwords do not match.</div>
                      </div>
                  
                      <button type="submit" class="btn btn-primary mt-3">Change Password</button>
                  </form>  
                </div>
              </div>
  
            </div>
          </div>
        </div>
      </section>
    </div>
  </main>
  @include('layout.sections.footer')
</body>

</html>
 
  <script>
    document.getElementById('password-change-form').addEventListener('submit', function(event) {
      const newPassword = document.getElementById('new_password').value;
      const confirmPassword = document.getElementById('new_password_confirmation').value;
      
      if (newPassword !== confirmPassword) {
        event.preventDefault(); // Prevent form submission
        document.getElementById('password-error').style.display = 'block'; // Show error message
      } else {
        document.getElementById('password-error').style.display = 'none'; // Hide error message
      }
    });
  </script>
  