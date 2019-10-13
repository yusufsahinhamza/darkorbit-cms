<div class="col s12">
  <div id="data" class="card white-text grey darken-4">
    <div class="row center no-margin">
      <div class="col s3">
        Uridium: <label id="uridium"><?php echo number_format($data->uridium, 0, ',', '.'); ?></label>
      </div>
      <div class="col s3">
        Credits: <label><?php echo number_format($data->credits, 0, ',', '.'); ?></label>
      </div>
      <div class="col s3">
        Honor: <label><?php echo number_format($data->honor, 0, ',', '.'); ?></label>
      </div>
      <div class="col s3">
        Experience: <label><?php echo number_format($data->experience, 0, ',', '.'); ?></label>
      </div>
    </div>
  </div>
</div>
