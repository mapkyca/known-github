<li <?php if (preg_match('/\/account\/github\/$/', $_SERVER['REQUEST_URI'])) echo 'class="active"'; ?>>
    <a href="<?php echo \Idno\Core\Idno::site()->config()->url; ?>account/github/">Github</a>
</li>
