<?php if (!defined('ACCESS')) {
    exit('Not access');
} ?>

        </div>
        <div id="footer">
            <span>Version <?php echo VERSION; ?> By <?php echo AUTHOR; ?></span>
        </div>
    </body>
</html>
<?php ob_end_flush(); ?>
