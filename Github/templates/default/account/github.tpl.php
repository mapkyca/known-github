<div class="row">

    <div class="col-md-10 col-md-offset-1">
        <h3>Github</h3>
        <?=$this->draw('account/menu')?>
    </div>

</div>
<div class="row">
    <div class="col-md-10 col-md-offset-1">
        <form action="/account/github/" class="form-horizontal" method="post">
            <?php
                if (empty(\Idno\Core\site()->session()->currentUser()->github)) {
            ?>
                    <div class="control-group">
                        <div class="controls">
                            <p>
                                If you have a Github account, you may connect it here. You will then be able to reply to Github issues via your site.
                            </p>
                            <p>
                                <a href="<?=$vars['login_url']?>" class="btn btn-large btn-success">Click here to connect Github to your account</a>
                            </p>
                        </div>
                    </div>
                <?php

                } else {

                    ?>
                    <div class="control-group">
                        <div class="controls">
                            <p>
                                Your account is currently connected to Github. You can now reply to Github issues via your site.
                            </p>
                            <p>
                                <input type="hidden" name="remove" value="1" />
                                <button type="submit" class="btn btn-large btn-primary">Click here to remove Github from your account.</button>
                            </p>
                        </div>
                    </div>

                <?php

                }
            ?>
            <?= \Idno\Core\site()->actions()->signForm('/account/github/')?>
        </form>
    </div>
</div>