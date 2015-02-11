<?php
$form = Form::getInstance();
for ($i=0;$i<count($listen);$i++):
    if (count($listen) == ($i+1)){
        echo '<li data-page="' . $page. '">';
    } else {
        echo '<li>';
    }
    ?>
    <div class="feedback-gravatar">
        <img src="<?php echo get_gravatar(clean($listen[$i]->mail, 'str'), 50); ?>" alt=""/>
    </div>
    <div class="feedback-container">
        <div class="feedback-subject">
            <?php echo clean($listen[$i]->subject, 'str'); ?>
        </div>
        <div class="feedback-date">
            <?php echo dates($listen[$i]->time, 'fr_date'); ?>
        </div>
        <div class="feedback-description">
            <?php echo truncatestr(clean($listen[$i]->description, 'str'), 250,false, '... <div><a a href="#" data-id="' . $listen[$i]->id . '">Voir la suite</a></div>');  ?>
        </div>
        <div class="feedback-action">
                                <span class="post">
                                    <a href="<?php echo Router::url('feedback/post/id:' . $listen[$i]->id); ?>" class="feedback-post btn btn-success btn-sm">
                                        Voir la discution
                                    </a>
                                </span>
                                <span class="action">
                                    <a href="#" class="feedback-reply btn btn-primary btn-sm" data-id="<?php echo $listen[$i]->id;  ?>">
                                        <?php
                                        if ($listen[$i]->reply > 0){ echo '<span class="label label-success" data-id-reply="' . $listen[$i]->id . '">'.$listen[$i]->reply.'</span>'; }
                                        else { echo '<span class="label label-success" data-id-reply="' . $listen[$i]->id . '" style="display: none">'.$listen[$i]->reply.'</span>'; }
                                        ?>
                                        R&eacute;pondre
                                    </a> |
                                    <a href="#" class="feedback-spam btn btn-warning btn-sm" data-report="<?php echo $listen[$i]->id;  ?>">
                                        <?php
                                        if ($listen[$i]->spam > 0){ echo '<span class="label label-danger" data-report-spam="' . $listen[$i]->id . '">'.$listen[$i]->spam.'</span>'; }
                                        else { echo '<span class="label label-danger" data-report-spam="' . $listen[$i]->id . '" style="display: none">'.$listen[$i]->spam.'</span>'; }
                                        ?>
                                        C'est un spam
                                    </a>
                                </span>
        </div>
    </div>
    <form class="form-horizontal" action="" style="display: none;" method="post" data-id-form="<?php echo $listen[$i]->id;  ?>">
        <?php
        echo $form->input('mail-'.$listen[$i]->id, '', array('placeholder' => 'Votre e-mail'));
        echo $form->input('reply-'.$listen[$i]->id, '', array('type' => 'textarea'));
        echo $form->submit('submit-'.$listen[$i]->id, 'R&eacute;pondre');
        ?>
    </form>
    <div class="feedback-reply-list">
        <?php
        echo '<ul class="feedback-list-reply">';
        for($c=0;$c<count($listen[$i]->replylist);$c++){
            $alias = $listen[$i]->replylist[$c];
            // pid	spam	ip	mail	description
            // debug($alias);
            echo '<li>';
            echo '<div class="feedback-gravatar">
                    <img src="' . get_gravatar(clean($alias->mail, 'str'), 50) . '" alt=""/>
                </div>';
            echo '<div class="feedback-container">' .
                '<div class="feedback-date">' .
                dates($listen[$i]->time, 'fr_date') .
                '</div>' .
                '<div class="feedback-description">' .
                clean($alias->description, 'str') .
                '</div>' .
                '</div>';
            echo '<div class="feedback-action">';

            echo ' <a href="#" class="feedback-spam btn btn-warning btn-sm" data-report-reply="' . $alias->id . '">';
                if ($alias->spam > 0){ echo '<span class="label label-danger" data-report-reply-spam="' . $alias->id . '">'.$alias->spam.'</span>'; }
                else { echo '<span class="label label-danger" data-report-reply-spam="' . $alias->id . '" style="display: none">'.$alias->spam.'</span>'; }
            echo 'C\'est un spam' .
                '</a>' .
                '</div>';
            echo '</li>';
        }
        echo '</ul>';
        ?>
    </div>
    </li>
<?php endfor; ?>