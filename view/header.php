<?php global $MY_BASE; ?>
<header>
    <div id="controls" data-id="<?= $_user_id ?>">
        <?php if (hasMinAuth('admin')) { ?>
            <a href="<?= $MY_BASE ?>/user" title="Users"><i class="fas fa-users"></i></a>
        <?php } ?>
        <a href="<?= $MY_BASE ?>/user/profile" title="Profile">
            <i class="fa-solid fa-user"></i>
        </a>
        <a href="logout" title="Logout"><i title="Logout" class="fas fa-power-off"></i></a>
    </div>
    <div id="course">
        <a href="<?= $MY_BASE ?>/">
            <i class="fa-solid fa-flask-vial" title="Manalabs Videos"></i>&nbsp;
        </a>
        <?php if (isset($course) && $course) { ?>
        <a title="Back to Course Overview" href="<?= $MY_BASE ?>/<?= strtolower($course ?: '') ?>/<?= $block ?>/">
            <span id="course_num"><?= strtoupper($course ?: '') ?></span>
            <span data-id="<?= $offering_id ?>" id="offering"> <?= $block ?> </span>
            <i class="fa-regular fa-calendar"></i>
        </a>
        <?php } ?>
    </div>
    <h1>
        <span class="title">
            <?= $title ?>
        </span>
    </h1>
</header>
