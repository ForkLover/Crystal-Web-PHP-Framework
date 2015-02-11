<div class="list-block">
    <div class="feedback-gravatar">
        <img src="<?php echo get_gravatar(clean($post->mail, 'str'), 50); ?>">
    </div>
    <div class="feedback-container">
        <div class="feedback-date">
            <?php echo dates($post->time, 'fr_date'); ?>
        </div>
        <div class="feedback-description">
            <?php echo nl2br( clean($post->description, 'str') ); ?>
        </div>

        <div class="feedback-action">
            <a href="#" class="feedback-spam btn btn-warning btn-sm" data-report="<?php echo $post->id; ?>">
                <?php if ($post->spam>0): ?>
                <span class="label label-danger" data-report-spam="<?php echo $post->id; ?>"><?php echo $post->spam; ?></span>
                <?php else: ?>
                <span class="label label-danger" style="display: none;" data-report-spam="<?php echo $post->id; ?>"><?php echo $post->spam; ?></span>
                <?php endif; ?>
                C'est un spam
            </a>
        </div>
    </div>
    <form class="form-horizontal" method="post" data-id-form="<?php echo $post->id;  ?>">
        <?php
        $form = Form::getInstance();
        echo $form->input('mail-'.$post->id, '', array('placeholder' => 'Votre e-mail'));
        echo $form->input('reply-'.$post->id, '', array('type' => 'textarea'));
        echo $form->submit('submit-'.$post->id, 'R&eacute;pondre');
        ?>
    </form>
    <div class="feedback-reply-list">
        <ul class="feedback-list-reply">
            <?php
            for($i=0;$i<count($reply);$i++):
            $alias = $reply[$i];
            ?>
            <li>
                <div class="feedback-gravatar">
                    <img src="<?php echo get_gravatar($alias->mail, 50); ?>">
                </div>
                <div class="feedback-container">
                    <div class="feedback-date"><?php echo dates($alias->time, 'fr_date'); ?></div>
                    <div class="feedback-description"><?php echo nl2br( clean($alias->description, 'str') ); ?></div>
                </div>
                <div class="feedback-action">
                    <a href="#" class="feedback-spam btn btn-warning btn-sm" data-report-reply="<?php echo $alias->id;  ?>">
                        <?php if ($alias->spam > 0): ?>
                            <span class="label label-danger" data-report-reply-spam="<?php echo $alias->id; ?>"><?php echo $alias->spam; ?></span>
                        <?php else: ?>
                            <span class="label label-danger" data-report-reply-spam="<?php echo $alias->id; ?>" style="display: none;"><?php echo $alias->spam; ?></span>
                        <?php endif; ?>
                        C'est un spam
                    </a>
                </div>
            </li>
            <?php endfor; ?>
        </ul>
    </div>
    <?php echo pagination(ceil($countReply/30)); ?>
</div>