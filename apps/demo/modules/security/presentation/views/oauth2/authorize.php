<p>Allow access?</p>
<form action="<?= $web->url(); ?>" method="POST">
    <?= $web->csrf(); ?>
    <button type="submit">Yes</button>
</form>