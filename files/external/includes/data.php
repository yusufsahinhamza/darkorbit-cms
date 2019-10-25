<div class="col s12">
  <div id="data" class="card white-text grey darken-4">
    <div class="row center no-margin">
      <div class="col s3">
        Uridium: <span id="uridium"><?php echo number_format($data->uridium, 0, ',', '.'); ?></span>
      </div>
      <div class="col s3">
        Credits: <span id="credits"><?php echo number_format($data->credits, 0, ',', '.'); ?></span>
      </div>
      <div class="col s3">
        Honor: <span><?php echo number_format($data->honor, 0, ',', '.'); ?></span>
      </div>
      <div class="col s3">
        Experience: <span><?php echo number_format($data->experience, 0, ',', '.'); ?></span>
      </div>
    </div>
  </div>
</div>
