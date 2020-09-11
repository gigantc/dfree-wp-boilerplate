<div class="wrap">

    <h2>Cronjob Scheduler <small>by chrispage1</small></h2>

    <div id="poststuff">
        <div id="post-body" class="metabox-holder columns-2">
            <?php echo $this->load_view('right_column'); ?>

            <div id="postbox-container-2" class="postbox-container">
                <?php
                    if (!$this->cron_configured()) {
                        $this->load_view('plugin_misconfigured');
                    } else {
                        $this->load_view('plugin_settings');
                    }
                ?>
            </div>
        </div>
    </div>
</div>