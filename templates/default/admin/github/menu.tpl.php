<li <?php if (preg_match('/\/admin\/github\/$/', $_SERVER['REQUEST_URI'])) echo 'class="active"'; ?>>
    <a href="<?php echo \Idno\Core\Idno::site()->config()->url; ?>admin/github/">Github</a>
</li>
