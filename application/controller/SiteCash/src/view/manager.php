<?php $form = Form::getInstance(); ?>
<ul class="nav nav-tabs">
    <li class="active">
        <a href="#cashman" data-toggle="tab">
            Ajouter/Retirer des crédits
        </a>
    </li>
    <li>
        <a href="#config" data-toggle="tab">
            Configuration
        </a>
    </li>
</ul>
<div class="tab-content">
    <div class="tab-pane active" id="cashman">
        <form method="post" class="form-horizontal">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">
                    Ajouter/Retirer des cr&eacute;dits
                    </h3>
                </div>
                <div class="panel-body">
                    <?php if (isset($msg)){ echo $msg; } 
                        echo $form->input('perso', 'Nom du membre');
                        echo $form->select('action', 'Action', array('down' => 'Retirer', 'up' => 'Ajouter'));
                        echo $form->input('somme', 'Somme');
                        ?>
                </div>
            </div>
        <?php echo $form->submit('submit', 'Enregistrer'); ?>
        </form>
    </div>
    <div class="tab-pane fade" id="config">
        <form method="post" class="form-horizontal">
            
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">
                    Configuration g&eacute;n&eacute;ral
                    </h3>
                </div>
                <div class="panel-body">
                    <?php
                    echo $form->input('devise', 'Devise', array('help' => 'Monnaie du site', 'value' => $devise)) .
                        $form->input('ratio', 'Ratio 1 &euro; =&gt; ' . $devise, array('value' => $ratio)) .
                        $form->input('allopay', 'Ratio 1 code =&gt; ' . $devise, array('value' => $ratioallo));
                    ?>
                </div>
            </div>

            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">
                        <a href="https://www.paypal.com/" target="_blank">
                            Paypal
                        </a>
                    </h3>
                </div>
                <div class="panel-body">
                    <p>
                        Pour avoir acc&egrave;s &agrave; l'API Paypal, il vous faut surclasser votre compte en buisness.<br>
                        Cela est compl&eacute;tement gratuit et imm&eacute;diat.
                    </p>
                    <?php
                    echo $form->input('apiusername', 'Nom d\'utilisateur API', array('value' => isset($paypal['apiusername']) ? $paypal['apiusername'] : '')) .
                        $form->input('apimdp', 'Mot de passe API', array('value' => isset($paypal['apimdp']) ? $paypal['apimdp'] : '')) .
                        $form->input('apisign', 'Signature', array('value' => isset($paypal['apisign']) ? $paypal['apisign'] : ''));
                     ?>
                </div>
            </div>
            
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">
                        <a href="http://www.rentabiliweb.com/fr/?trackADV=356376" target="_blank">
                            Rentabiliweb
                        </a>
                    </h3>
                </div>
                <div class="panel-body">
                    <p>
                        Le syst&egrave;me de Rentabiliweb allie tarif attractif et modulable &agrave; souhait.<br>
                        Vous pouvez partager vos gains sur un autre compte. Par exemple le miens "356376".
                    </p>
                    <?php
                    echo $form->input('rentaSiteId', 'Rentabiliweb SiteId', array('value' => isset($rentabiliweb['siteId']) ? $rentabiliweb['siteId'] : 0)) .
                        $form->input('rentaDocId', 'Rentabiliweb DocId', array('value' => isset($rentabiliweb['docId']) ? $rentabiliweb['docId'] : 0));
                    ?>
                </div>
            </div>
            
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">
                        <a href="http://www.starpass.fr/inscription.php?parr=be535bk" target="_blank">StarPass</a>
                    </h3>
                </div>
                <div class="panel-body">
                    <p>
                        Le syst&egrave;me de StarPass.
                    </p>
                    <?php
                    echo $form->input('starpassId', 'StarPass id', array('value' => isset($starpassId) ? $starpassId : 0));
                    ?>
                </div>
            </div>
            
            <?php echo $form->submit('submit', 'Enregistrer', array('type' => 'submit')); ?>
        </form>
    </div>
</div>
