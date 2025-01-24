
<?php echo $__env->make('layout.includes.head', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>



<body>

  <?php echo $__env->make('layout.sections.header', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
  <?php echo $__env->make('layout.sections.sidebar', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>



  <main id="main" class="main">
                
                <div id="app">
                    <div class="container">
                        <div class="row">                             
                            <main class="col table-design" >
                            <div class="pagetitle">
                           <h1><?php echo $__env->yieldContent('sub-title'); ?></h1>
                          </div><!-- End Page Title --> 
                                <!--begin::Main-->
                                <?php echo $__env->yieldContent('content'); ?>
                                <!--end::Main-->
                            
                            </main>
                        </div>
                     </div>
                </div>
    </main>

  <?php echo $__env->make('layout.sections.footer', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

  <?php echo $__env->make('layout.includes.js', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
  <script type="text/javascript">
    $(document).ready(function() {});
    </script>
    <?php echo $__env->yieldContent('js_scripts'); ?>


</body>

</html><?php /**PATH D:\Courses-Management\resources\views/layout/app.blade.php ENDPATH**/ ?>