<?php $form = Form::getInstance(); ?>
<div class="coll-sm-12">
    <div class="well well-small">
        <p>
            Un syst&egrave;me de Feedback est en r&eacute;alit&eacute; un moyen simple et efficace d'avoir un retour de la part de la communaut&eacute;.<br>
            Ici nous avons utilis&eacute; le th&egrave;me du d&eacute;veloppement, l'utilisateur peut d&egrave;s lors, signaler un probl&egrave;me, poser une question ou demander de nouvelles fonctionnalit&eacute;s.
        </p>
        <p>
            Les utilisateurs peuvent indiquer, que tel requ&ecirc;te ou r&eacute;ponse est un spam. Cela n'efface pas le message, le message est class&eacute; plus loin dans la discussion, car moins pertinent.
        </p>
    </div>
</div>

<div class="col-sm-6">
    <form action="<?php echo Router::url('feedback'); ?>" method="post" class="form-horizontal">
    <?php
        echo $form->select('cat', '', array(
            'bug' => 'Signaler un probl&egrave;me',
            'question' => 'Poser une question',
            'feature' => 'Proposer quelque chose de nouveau'
            ));
        echo $form->input('mail', '', array('placeholder' => 'Votre e-mail'));
        echo $form->input('subject', '', array('placeholder' => 'Sujet'));
        echo $form->input('description', '', array('type' => 'textarea', 'style' => 'height: 300px;', 'placeholder' => 'Votre message'));
        echo $form->submit('submit', 'Contribuer');
    ?>
    </form>
</div>
<div class="col-sm-6">
    <div class="feedback">
        <ul class="nav nav-tabs">
            <?php
            $active = function($cat){
                return ($this->cat == $cat) ? ' class="active"' : '';
            };
            ?>
            <li<?php echo $active('bug'); ?> id="bug"><a href="<?php echo Router::url('feedback/cat/cat:bug'); ?>">Bug</a></li>
            <li<?php echo $active('question'); ?> id="question"><a href="<?php echo Router::url('feedback/cat/cat:question'); ?>">Question</a></li>
            <li<?php echo $active('feature'); ?> id="feature"><a href="<?php echo Router::url('feedback/cat/cat:feature'); ?>">Demande de fonctionnalit&eacute;</a></li>
        </ul>
        <div class="tab-content">
            <div class="tab-pane fade active in">
                <?php
                if (count($listen)) {
                ?>
                <ul class="list-block" id="listen">
                    <?php
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
                                <?php echo nl2br( truncatestr( clean( $listen[$i]->description, 'str'), 250,false, '...') );  ?>
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
                                            nl2br( truncatestr( clean( $alias->description, 'str') , 250,false, '... ') ).
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
                </ul>
                <?php } ?>
            </div>
            <?php $display = count($listen) ? 'none' : 'block'; ?>
            <div id="nomore" class="well well-small text-center"style="display: <?php echo $display; ?>;margin:20px;">Rien pour le moment</div>
            <div id="load" class="text-center" style="display: none;margin:20px;">
                <i class="fa fa-spinner fa-spin fa-2x"></i>
            </div>
        </div>
    </div>
</div>