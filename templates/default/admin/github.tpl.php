<div class="row">

    <div class="col-md-10 col-md-offset-1">
        <h1>Github</h1>
        <?=$this->draw('admin/menu')?>
    </div>

</div>
<div class="row">
    <div class="col-md-10 col-md-offset-1">
        <form action="/admin/github/" class="form-horizontal" method="post">
            <div class="control-group">
                <div class="controls">
                    <p>
                        <?= \Idno\Core\Idno::site()->language()->_('To begin using Github, <a href="https://github.com/settings/applications/new" target="_blank">create a new application in the Github apps portal</a>'); ?>.</p>
                    <p>
                        <?= \Idno\Core\Idno::site()->language()->_('Add the following URL to the \'Authorization callback URL\' callback url box <strong>%sgithub/callback</strong>.', [\Idno\Core\site()->config()->url]); ?>
                    </p>
                    <p>
                        <?= \Idno\Core\Idno::site()->language()->_('Once you\'ve finished, fill in the details below:');?>
                    </p>
                </div>
            </div>
            <div class="control-group">
                <label class="control-label" for="name"><?= \Idno\Core\Idno::site()->language()->_('Client ID');?></label>
                <div class="controls">
                    <input type="text" id="name" placeholder="App Key" name="appId" value="<?=htmlspecialchars(\Idno\Core\site()->config()->github['appId'])?>" >
                </div>
            </div>
            <div class="control-group">
                <label class="control-label" for="name"><?= \Idno\Core\Idno::site()->language()->_('Client Secret');?></label>
                <div class="controls">
                    <input type="text" id="name" placeholder="Secret Key" name="secret" value="<?=htmlspecialchars(\Idno\Core\site()->config()->github['secret'])?>" >
                </div>
            </div>
            <div class="control-group">
                <div class="controls">
                    <button type="submit" class="btn btn-primary"><?= \Idno\Core\Idno::site()->language()->_('Save');?></button>
                </div>
            </div>
            <?= \Idno\Core\site()->actions()->signForm('/admin/github/')?>
        </form>
    </div>
</div>