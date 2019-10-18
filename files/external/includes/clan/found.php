<div class="card white-text grey darken-4 padding-15">
  <h6>FOUND CLAN</h6>

  <div class="row">
    <div class="col s7">
      Founding a clan will cost you a total of 300.000 Credits. After founding your clan, you can upload a logo, raise taxes, manage members and much more under Clan Info in the management section.<br><br>

      To found a clan, please enter the following information.<br><br>

      Name: (min. 1 character, max: 50 characters)<br>
      Tag: (min. 1 character, max: 4 characters)<br>
      Description: (max: ~16,000 characters)<br><br>

      Confirm your entries with the "Found" button.
    </div>

    <div class="col s5">
      <div class="row">
        <form id="found_clan" method="post">
          <div class="input-field col s12">
            <input class="white-text validate" type="text" name="name" id="name" maxlength="50" required>
            <label for="name">Clan name</label>
            <span class="helper-text white-text" data-error="Enter a valid clan name!">Enter a clan name.</span>
          </div>
          <div class="input-field col s12">
            <input class="white-text validate" type="text" name="tag" id="tag" maxlength="4" required>
            <label for="tag">Clan tag</label>
            <span class="helper-text white-text" data-error="Enter a valid clan tag!">Enter a clan tag.</span>
          </div>
          <div class="input-field col s12">
            <textarea name="description" class="materialize-textarea white-text validate" placeholder="Enter clan description here."></textarea>
            <span class="helper-text white-text" data-error="Enter a valid clan description!">Enter a clan description. (Optional)</span>
          </div>
          <div class="input-field center col s12">
            <button class="btn grey darken-3 waves-effect waves-light col s12">FOUND</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
