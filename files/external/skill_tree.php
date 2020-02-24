<?php
$equipment = $mysqli->query('SELECT skill_points, items FROM player_equipment WHERE userId = '.$player['userId'].'')->fetch_assoc();
$skillPoints = json_decode($equipment['skill_points']);
$skillTree = json_decode($equipment['items'])->skillTree;
$requiredLogdisks = Functions::GetRequiredLogdisks((array_sum((array)$skillPoints) + $skillTree->researchPoints) + 1);

$skills = Functions::GetSkills($skillPoints);
?>
      <div id="main">
        <div class="container">
          <div class="row">
            <?php require_once(INCLUDES . 'data.php'); ?>

            <div class="col s12">
              <div class="card white-text grey darken-4 padding-15">
                <h5>SKILL TREE</h5>

                <h6>LOG-DISKS: <span id="logdisks"><?php echo $skillTree->logdisks; ?></span> / REQUIRED: <span id="requiredLogdisks"><?php echo $requiredLogdisks; ?></span> > <button id="exchangeLogdisks" class="btn-small grey darken-3 waves-effect waves-light" <?php echo (($skillTree->logdisks < $requiredLogdisks) || ((array_sum((array)$skillPoints) + $skillTree->researchPoints) >= array_sum(array_column($skills, 'maxLevel'))) ? 'disabled' : '');?>>EXCHANGE</button> >> Research Points: <span id="researchPoints"><?php echo $skillTree->researchPoints; ?></span></h6>
                <br>
                <div class="scrollBackground">
                <?php foreach ($skills as $key => $value) { ?>
                  <div class="skillContainer">
                    <div id="<?php echo $key; ?>" class="skill tooltipped" data-position="left" data-tooltip="<?php echo Functions::GetSkillTooltip($value['name'], $value['currentLevel'], $value['maxLevel']); ?>">
                        <div class="<?php echo ($value['currentLevel'] == $value['maxLevel'] ? 'skill_effect_max' : (isset($value['baseSkill']) && $skills[$value['baseSkill']]['currentLevel'] != $skills[$value['baseSkill']]['maxLevel'] ? 'skill_effect_inactive' : 'skill_effect')); ?> <?php echo ($skillTree->researchPoints <= 0 ? 'noCursor' : ''); ?> customTooltip type_skillTree loadType_normal id_skill_18a_info inner_skillTreeHorScrollable  outer_profilePage top_120 left_300">
                            <div class="skillPoints <?php echo ($value['currentLevel'] == $value['maxLevel'] ? 'skilltree_font_ismax' : 'skilltree_font_fail_skillPoints'); ?>">
                                <span class="currentLevel"><?php echo $value['currentLevel']; ?></span>/<span class="maxLevel"><?php echo $value['maxLevel']; ?></span>
                            </div>
                        </div>
                    </div>
                  </div>
                <?php } ?>
                </div>
                <br>
                <h6>Research Points used: <span id="usedResearchPoints"><?php echo array_sum((array)$skillPoints); ?></span>/<?php echo array_sum(array_column($skills, 'maxLevel')); ?> <button <?php if (array_sum((array)$skillPoints) <= 0) { ?>style="display: none;"<?php } ?> class="btn-small grey darken-3 waves-effect waves-light modal-trigger" href="#modal">RESET SKILLS (<?php echo number_format(Functions::GetResetSkillCost($skillTree->resetCount), 0, '.', '.'); ?> Uridium)</button></h6>
              </div>
           </div>
          </div>
        </div>
      </div>

      <div id="modal" class="modal grey darken-4 white-text">
        <div class="modal-content">
          <p>Do you really want to reset your skills?</p>
        </div>
        <div class="modal-footer grey darken-4">
          <a class="modal-close waves-effect waves-light btn grey darken-2">Close</a>
          <a id="resetSkills" class="modal-close waves-effect waves-light btn grey darken-3">OK</a>
        </div>
      </div>
