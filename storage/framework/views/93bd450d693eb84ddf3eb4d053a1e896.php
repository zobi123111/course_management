<?php $__env->startSection('title', 'Users'); ?>
<?php $__env->startSection('sub-title', 'Users'); ?>

<?php $__env->startSection('content'); ?>
<div class="create_btn">
    <a href="<?php echo e(url('create/course')); ?>" class="btn btn-primary" id="create_course" >Create Course</a>
</div>
<?php if(session()->has('message')): ?>
    <div id="successMessage" class="alert alert-success fade show" role="alert">
        <i class="bi bi-check-circle me-1"></i>
        <?php echo e(session()->get('message')); ?>

    </div>
<?php endif; ?>
<div class="container mt-5">
  <div class="row">
    <div class="col-sm-4">
      <h3>Column 1</h3>
      <div class="card">
    <div class="card-header">Header</div>
    <div class="card-body">Content</div> 
    <div class="card-footer">Footer</div>
  </div>
    </div>
    <div class="col-sm-4">
      <h3>Column 2</h3>
      <div class="card">
    <div class="card-header">Header</div>
    <div class="card-body">Content</div> 
    <div class="card-footer">Footer</div>
  </div>
    </div>
    <div class="col-sm-4">
      <h3>Column 3</h3>        
      <div class="card">
    <div class="card-header">Header</div>
    <div class="card-body">Content</div> 
    <div class="card-footer">Footer</div>
  </div>
    </div>
  </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('js_scripts'); ?>

<script>
$(document).ready(function() {
    $('#user_table').DataTable();

});
</script>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layout.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\Courses-Management\resources\views/Courses/all_courses.blade.php ENDPATH**/ ?>