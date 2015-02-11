<ul class="nav nav-tabs">
    <li class="<?php echo (!$act) ? 'active' : ''; ?>">
        <a href="#list" data-toggle="tab">
            Ajouter/Retirer des cr&eacute;dits
        </a>
    </li>
    <li class="pull-left<?php echo ($act === 'edt') ? ' active' : ''; ?>">
        <a href="#addserver" data-toggle="tab">
            Ajouter un serveur
        </a>
    </li>
</ul>
<div class="tab-content">
    <div class="tab-pane<?php echo (!$act) ? ' active' : ''; ?>" id="list">
        <?php 
        if (count($serverList)) {
        ?>
        <table class="table table-striped table-bordered table-hover">
            <thead>
                <tr>
                    <th style="width: 35px;">#</th>
                    <th>Nom du serveur</th>
                    <th>IP/h&ocirc;te</th>
                    <th style="width: 35px;">Port</th>
                    <th style="width: 35px;">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                    for($i=0;$i<count($serverList);$i++): 
                        $alias = $serverList[$i];
                     ?>
                            <tr class="active">
                                <td><?php echo $alias->id; ?></td>
                                <td>
                                    <?php echo clean($alias->name, 'str'); ?>
                                </td>
                                <td>
                                    <?php echo clean($alias->server, 'str'); ?>
                                </td>
                                <td>
                                    <?php echo $alias->port; ?>
                                </td>
                                <td>
                                    <a href="<?php echo Router::url('jsonapi/manager/act:edt/id:' . $alias->id); ?>"><i class="fa fa-pencil"></i></a>
                                    <a href="#" onclick="javascript:bootbox.confirm('Supprimer le serveur ?', function(r){if(r){window.location.href='<?php echo Router::url('jsonapi/manager/act:del/id:' . $alias->id); ?>?token=<?php echo $session->getToken(); ?>';}});return false;"><i class="fa fa-trash-o"></i></a>
                                </td>
                            </tr>
                     <?php 
                     endfor;
                ?>
            </tbody>
        </table>
        <?php
        } else {
           echo '<div class="well">Aucun serveur enregistr&eacute; "Ajouter un serveur"</div>';
        }
        ?>
    </div>
    <div class="tab-pane<?php echo ($act === 'edt') ? ' active' : ''; ?>" id="addserver">
        <div class="well">
            ATTENTION: dans la configuration de votre JsonAPI le champ, use-new-api dois &ecirc;tre sur false
        </div>
        <form method="post" class="form-horizontal">
            <?php
            $form = Form::getInstance();
            echo $form->input('name', 'Nom du serveur', array('help' => 'Celui-ci appra&icirc;tra lors de la commande'));
            echo $form->input('server', 'Adresse du serveur', array('help' => 'Adresse IP ou nom de domaine sans protocole'));
            echo $form->input('port', 'Port');
            echo $form->input('user', 'Identifiant');
        
            echo $form->password('password', 'Mot de passe');
            echo $form->password('salt', '"salt"', array('type' => 'password'));
            echo $form->submit('submit', 'Enregistrer');
            ?>
        </form>
    </div>
</div>