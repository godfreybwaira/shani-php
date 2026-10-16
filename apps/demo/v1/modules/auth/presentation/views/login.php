<div class="space-around">
    <div class="card width-sm-max width-md-1q height-md-max pos-c">
        <h1 class="card-title">Please login</h1>
        <form class="card-body" action="<?= $web->url(); ?>" method="POST" autocomplete="off">
            <div class="row row-stretch">
                <div class="col">
                    <?= $web->csrf(); ?>
                    <div class="input-line">
                        <!--order matters-->
                        <input type="text" name="username" placeholder="admin" required minlength="1" maxlength="200">
                        <label>Your Username</label>
                    </div>
                </div>
            </div>
            <div class="row row-stretch">
                <div class="col">
                    <div class="input-line">
                        <!--order matters-->
                        <input type="password" name="password" placeholder="****" required minlength="1" maxlength="200">
                        <label>Your Password</label>
                    </div>
                </div>
            </div>
            <div class="row row-stretch">
                <div class="col">
                    <button class="button accent-color button-block">
                        Login
                    </button>
                </div>
            </div>
            <?php if (!empty($web->attr->exists('error'))): ?>
                <p class="padding-xy color-danger"><?= $web->attr->getOne('error'); ?></p>
            <?php endif; ?>
        </form>
    </div>
</div>