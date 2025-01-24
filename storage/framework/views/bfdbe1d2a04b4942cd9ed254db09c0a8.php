<?php $__env->startSection('title', 'Create Course'); ?>
<?php $__env->startSection('sub-title', 'Create Course'); ?>

<?php $__env->startSection('content'); ?>

<form action="<?php echo e(url('store_policy')); ?>" method="POST">
    <?php echo csrf_field(); ?>
    <div class="modal-body">
        <div class="row mb-3 mt-4">
            <label for="title" class="col-sm-3 col-form-label required">Name<span class="text-danger">*</span></label>
            <div class="col-sm-9">
                <input type="text" class="form-control" name="course_name" >
                <?php if($errors->has('policy_name')): ?>
                <span class="text-danger"><?php echo e($errors->first('policy_name')); ?></span>
                <?php endif; ?> 
            </div>
        </div>
        <div class="row mb-3 mt-4">
            <label for="title" class="col-sm-3 col-form-label required">Description<span class="text-danger">*</span></label>
            <div class="col-sm-9">
            <textarea id="myeditorinstance" name="course_description"></textarea>
              <?php if($errors->has('description')): ?>
                <span class="text-danger"><?php echo e($errors->first('description')); ?></span>
                <?php endif; ?> 
            </div>
        </div>
    </div>


    <div class="modal-footer back-btn">
        <button type="submit" class="btn btn-primary btn-default">Save</button>
    </div>
</form>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('js_scripts'); ?>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layout.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH D:\Courses-Management\resources\views/Courses/create_course.blade.php ENDPATH**/ ?>