<?php $__env->startSection('title', 'Users'); ?>
<?php $__env->startSection('sub-title', 'Users'); ?>

<?php $__env->startSection('content'); ?>
<div class="create_btn">
    <a href="#" class="btn btn-primary create-button" id="createUser" data-toggle="modal"
        data-target="#userModal">Create User</a>
</div>
<?php if(session()->has('message')): ?>
    <div id="successMessage" class="alert alert-success fade show" role="alert">
        <i class="bi bi-check-circle me-1"></i>
        <?php echo e(session()->get('message')); ?>

    </div>
    <?php endif; ?>
<table class="table" id="user_table">
  <thead>
    <tr>
      <th scope="col">First Name</th>
      <th scope="col">Last Name</th>
      <th scope="col">Email</th>
      <th scope="col">Edit</th>
      <th scope="col">Delete</th>
    </tr>
  </thead>
  <tbody>
    <?php $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $val): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
    <tr>
    <th scope="row" class="fname"><?php echo e($val->fname); ?></th>
      <th scope="row" class="lname"><?php echo e($val->lname); ?></th>
      <td><?php echo e($val->email); ?></td>
      <td><i class="fa fa-edit edit-user-icon" style="font-size:25px; cursor: pointer;" data-user-id="<?php echo e($val->id); ?>"></i></td>
      <td><i class="fa-solid fa-trash delete-icon" style="font-size:25px; cursor: pointer;" data-user-id="<?php echo e($val->id); ?>"></i></td>
    </tr>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
  </tbody>
</table>

<!-- Create User -->
<div class="modal fade" id="userModal" tabindex="-1" role="dialog" aria-labelledby="userModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="userModalLabel">Create Employee</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="" method="POST" id="Create_user" class="row g-3 needs-validation">
                    <?php echo csrf_field(); ?>
                    <div class="form-group">
                        <label for="firstname" class="form-label">First Name<span class="text-danger">*</span></label>
                        <input type="text" name="firstname" class="form-control">
                        <div id="firstname_error" class="text-danger error_e"></div>
                    </div>
                    <div class="form-group">
                        <label for="lastname" class="form-label">Last Name<span class="text-danger">*</span></label>
                        <input type="text" name="lastname" class="form-control">
                        <div id="lastname_error" class="text-danger error_e"></div>
                    </div>
                    <div class="form-group">
                        <label for="email" class="form-label">Email<span class="text-danger">*</span></label>
                        <input type="email" name="email" class="form-control">
                        <div id="email_error" class="text-danger error_e"></div>
                    </div>

                    <div class="form-group">
                        <label for="password" class="form-label">Password<span class="text-danger">*</span></label>
                        <input type="password" name="password" class="form-control">
                        <div id="password_error" class="text-danger error_e"></div>
                    </div>
                    <div class="form-group">
                        <label for="confirmpassword" class="form-label">Confirm Password<span
                                class="text-danger">*</span></label>
                        <input type="password" name="password_confirmation" class="form-control" id="confirmpassword">
                        <div id="password_confirmation_error" class="text-danger error_e"></div>
                    </div>
                    <hr>
                    <div class="form-group">
                        <label for="role" class="form-label">Role<span class="text-danger">*</span></label>
                        <select name="role_name" class="form-select" id="role">
                            <?php $__currentLoopData = $roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $val): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($val->id); ?>"><?php echo e($val->role_name); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                        </select>
                        <div id="role_name_error" class="text-danger error_e"></div>
                    </div>

                    <div class="modal-footer">
                        <a href="#" type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</a>
                        <a href="#" type="button" id="saveuser" class="btn btn-primary sbt_btn">Save </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!--End of create user-->

<!-- Edit user -->
<div class="modal fade" id="editUserDataModal" tabindex="-1" role="dialog" aria-labelledby="editUserDataModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editUserDataModalLabel">Edit Employee</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="" method="POST" id="Create_user3" class="row g-3 needs-validation">
                    <?php echo csrf_field(); ?>
                    <div class="form-group">
                        <label for="firstname" class="form-label">First Name<span class="text-danger">*</span></label>
                        <input type="text" name="fname" class="form-control">
                        <div id="fname_error" class="text-danger error_e"></div>
                    </div>
                    <div class="form-group">
                        <label for="lastname" class="form-label">Last Name<span class="text-danger">*</span></label>
                        <input type="text" name="lname" class="form-control">
                        <div id="l_error" class="text-danger error_e"></div>
                    </div>
                    <div class="form-group">
                        <label for="email" class="form-label">Email<span class="text-danger">*</span></label>
                        <input type="email" name="email" class="form-control">
                        <div id="email_error" class="text-danger error_e"></div>
                    </div>
                   
                    <div class="form-group">
                        <label for="role" class="form-label">Role<span class="text-danger">*</span></label>
                        <select name="edit_role_name" class="form-select" id="edit_role">
                            <?php $__currentLoopData = $roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $val): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($val->id); ?>"><?php echo e($val->role_name); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                        </select>
                        <div id="edit_role_name_error" class="text-danger error_e"></div>
                    </div>

                    <div class="modal-footer">
                        <a href="#" type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</a>
                        <a href="#" type="button" id="saveuser" class="btn btn-primary sbt_btn">Save </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!--End of Edit user-->

<!--Delete  Modal -->
<form action="<?php echo e(url('/users/delete')); ?>" method="POST">
    <?php echo csrf_field(); ?>
    <div class="modal fade" id="deleteUserModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Delete</h5>
                    <input type="hidden" name="id" id="userid" value="">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to delete this user "<strong><span id="append_name"> </span></strong>" ?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary close_btn" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary user_delete">Delete</button>
                </div>
            </div>
        </div>
    </div>
</form>
<!-- End of Delete Model -->
<?php $__env->stopSection(); ?>

<?php $__env->startSection('js_scripts'); ?>

<script>
$(document).ready(function() {
    $('#user_table').DataTable();

    $('#createUser').on('click', function() {
        $('.error_e').html('');
        $('.alert-danger').css('display', 'none');
        $('#userModal').modal('show');
    });

    $('#saveuser').click(function(e) {
        e.preventDefault();
        $('.error_e').html('');
        $.ajax({
            url: '<?php echo e(url("/save_user")); ?>',
            type: 'POST',
            data: $('#Create_user').serialize(),
            success: function(response) {
                $('#userModal').modal('hide');
                location.reload();
            },
            error: function(xhr, status, error) {
                var errorMessage = JSON.parse(xhr.responseText);
                var validationErrors = errorMessage.errors;
                $.each(validationErrors, function(key, value) {
                    var html1 = '<p>' + value + '</p>';
                    $('#' + key + '_error').html(html1);
                });
            }
        });
    });

    $('.edit-user-icon').click(function(e) {
        e.preventDefault();
        $('.error_ee').html('');
        var userId = $(this).data('user-id');
        vdata = {
            id: userId, 
            "_token": "<?php echo e(csrf_token()); ?>",
        };
        $.ajax({
            type: 'post',
            url: "<?php echo e(url('users/edit')); ?>", 
            data: vdata,
            success: function(response) {
                console.log(response.user.fname);
                $('input[name="fname"]').val(response.user.fname);
                $('input[name="lname"]').val(response.user.lname);
                $('input[name="email"]').val(response.user.email);

                // Primary role
                var userRoleId = response.user.role;
                $('#role_id option').removeAttr('selected');
                $('#edit_role option[value="' + userRoleId + '"]').attr('selected',
                    'selected');

                //Secondary role
                var secondary_role = response.user.role_id1;
              //  $('#secondary_role').val('');
                $('#secondary_role option').removeAttr('selected');
                $('#secondary_role option[value="' + secondary_role + '"]').attr('selected','selected');
                $('#editUserDataModal').modal('show');
            },
            error: function(xhr, status, error) {
                console.error(xhr.responseText);
            }
        });
});

$('.delete-icon').click(function(e) {
    e.preventDefault();
        $('#deleteUserModal').modal('show');
        var userId = $(this).data('user-id');
        var fname = $(this).closest('tr').find('.fname').text();
          var lname = $(this).closest('tr').find('.lname').text();
          var name = fname + ' ' + lname;
        $('#append_name').html(name);
        $('#userid').val(userId);
      
});

});
</script>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layout.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\course_management\resources\views/User/allusers.blade.php ENDPATH**/ ?>