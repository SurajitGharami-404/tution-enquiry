<?php if (isset($_SESSION["errors"])): ?>
    <div class="position-fixed top-0 end-0 m-3" style="z-index: 9999;">
        <?php foreach ($_SESSION["errors"] as $key => $error): ?>
            <div class="alert alert-danger alert-dismissible fade show  shadow-sm" role="alert">
                <?= $error ?>
                <?php unset($_SESSION["errors"]) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endforeach ?>
    </div>
<?php endif; ?>

<?php if (isset($_SESSION["success"])): ?>
    <div class="position-fixed top-0 end-0 m-3" style="z-index: 9999;">
        <div class="alert alert-success alert-dismissible fade show  shadow-sm" role="alert">
            <?= $_SESSION["success"] ?>
            <?php unset($_SESSION["success"]) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    </div>
<?php endif; ?>