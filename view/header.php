<header>
    <div id="controls" data-id="<?= $_SESSION['user']['id'] ?>">
        <?php if ($_SESSION['user']['type'] === 'admin') : ?>
            <a href="/videos/user" title="Users"><i class="fas fa-users"></i></a>
        <?php endif; ?>
        <a href="logout" title="Logout"><i title="Logout" class="fas fa-power-off"></i></a>
    </div>
    <div id="course">
        <a href="/videos/">
            <i class="fa-solid fa-flask-vial" title="Manalabs Videos"></i>&nbsp;
        </a>
        <a href="/videos/<?= strtolower($course) ?>/<?= $block ?>/">
            <span id="course_num"><?= strtoupper($course) ?></span>
            <span data-id="<?= $offering_id ?>" id="offering"> <?= $block ?> </span>
        </a>
    </div>
    <h1>
        <span class="title" >
            <?= $title ?> 
        </span>
    </h1>
</header>
