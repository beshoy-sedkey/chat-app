<?php $__env->startSection('content'); ?>
    <div id="app">

        <div class="container mx-2 my-5">
            <App :auth="<?php echo e(json_encode(auth()->user())); ?>" />
        </div>

    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layout', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /var/www/html/realtime-chat/resources/views/app.blade.php ENDPATH**/ ?>