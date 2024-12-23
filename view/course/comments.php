<div id="comments">
    <h2>Questions & Comments</h2>
    <?php
    $comment_count = 0;
    foreach ($comments as $vid_pdf => $vid_comments) {
        if ($vid_pdf == $info['parts'][1]) {
            foreach ($vid_comments as $comment) {
                $comment_count++;
                ?>
    <div class="author">
        <?= $comment['knownAs'] ?> <?= $comment['lastname'] ?>
        <span class="date">created: <?= $comment['created'] ?></span>
        <?php if ($comment['edited']) { ?>
        <span class="date">edited: <?= $comment['edited'] ?></span>
        <?php } ?>
        <?php if (hasMinAuth('instructor') || $_user_id == $comment['user_id']) { ?>
        <form method="post" action="delComment">
            <input type="hidden" name="id" value="<?= $comment['id'] ?>" />
            <input type="hidden" name="tab" value="<?= $file_idx ?>" />
            <i title="Delete" class="far fa-trash-alt" data-id=""></i>
        </form>
        <i title="Edit" class="far fa-edit" data-id="<?= $comment['id'] ?>"></i>
        <?php } ?>
        <div class="vote" data-id="<?= $comment['id'] ?>" 
            <?php if ($comment['vote_id']) { ?> 
            data-vid="<?= $comment['vote_id'] ?>" 
            <?php } ?> 
            <?php if ($comment['vote']) { ?> 
            data-type="<?= $comment['vote'] > 0 ? 'up' : 'down' ?>" 
            <?php } ?>>
            <i class="fas fa-angle-up <?= $comment['vote'] > 0 ? 'selected' : '' ?>" title="Vote Up"></i>
            <i class="fas fa-angle-down <?= $comment['vote'] < 0 ? 'selected' : '' ?>" title="Vote Down"></i>
        </div>
    </div>
    <div class="comment mdBox" id="q<?= $comment['id'] ?>">
        <div class="qText"><?= $parsedown->text($comment['text']) ?></div>
        <?php foreach ($replies[$comment['id']] as $reply) { ?>
        <div class="author">
            <?= $reply['knownAs'] ?> <?= $reply['lastname'] ?>
            <span class="date">created: <?= $reply['created'] ?></span>
            <?php if ($reply['edited']) { ?>
            <span class="date">edited: <?= $reply['edited'] ?></span>
            <?php } ?>
            <?php if (hasMinAuth('instructor') || $_user_id == $reply['user_id']) { ?>
            <form method="post" action="delReply">
                <input type="hidden" name="id" value="<?= $reply['id'] ?>" />
                <input type="hidden" name="tab" value="<?= $file_idx ?>" />
                <i title="Delete" class="far fa-trash-alt" data-id=""></i>
            </form>
            <i title="Edit" class="far fa-edit" data-id="<?= $reply['id'] ?>"></i>
            <?php } ?>
            <div class="vote" data-id="<?= $reply['id'] ?>" 
                <?php if ($reply['vote_id']) { ?> 
                data-vid="<?= $reply['vote_id'] ?>" 
                <?php } ?> 
                <?php if ($reply['vote']) { ?> 
                data-type="<?= $reply['vote'] > 0 ? 'up' : 'down' ?>" 
                <?php } ?>>
                <i class="fas fa-angle-up <?= $reply['vote'] > 0 ? 'selected' : '' ?>" title="Vote Up"></i>
                <i class="fas fa-angle-down <?= $reply['vote'] < 0 ? 'selected' : '' ?>" title="Vote Down"></i>
            </div>
        </div>
        <div class="reply mdBox" id="r<?= $reply['id'] ?>"><?= $parsedown->text($reply['text']) ?></div>
        <?php } ?>
        <div class="addReply">add reply</div>
    </div>
    <?php } // end of comments loop?>
    <?php } // if the comment is for the current vid_pdf?> 
    <?php } // end of vid_pdfs loop?>
    <?php if ($comment_count == 0) { ?>
    <div>No questions or comments yet</div>
    <?php } ?>
    <h3>Add a question or comment:</h3>
    <form method="post" action="comment" class="textContainer commentForm">
        <input type="hidden" name="vid_pdf" value="<?= $info['parts'][1] ?>" />
        <input type="hidden" name="tab" value="<?= $idx ?>" />
        <textarea name="text" class="commentText" placeholder="Use **markdown** syntax in your text like:&#10;&#10;```javascript&#10;const code = &quot;highlighted&quot;&semi;&#10;```"></textarea>
        <div>
            <div class="commentActions">
                <button class="previewBtn">Preview Markdown</button>
                <button>Add</button>
            </div>
            <div class="previewArea"></div>
        </div>
    </form>
</div>
