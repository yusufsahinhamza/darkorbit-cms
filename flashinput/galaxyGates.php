<?php
require_once('../System/Init.php');
$db = Database::Connection();

//multiplier gelen itemin miktarını çarpar ve mevcut kapı parçası gelirse gelir

$gates = [
  'alpha' => ['id' => 1, 'mode' => 'alpha', 'total' => 34],
  'beta' => ['id' => 2, 'mode' => 'beta', 'total' => 48],
];

$mode = isset($_GET['alpha']) ? 'alpha' : (isset($_GET['beta']) ? 'beta' : 'standard');
$userId = $Player->Data['userID'];
$data = json_decode($Player->Data['Data']);
$gate_id = ($mode === 'standard' ? $gates['alpha'] : $gates[$mode])['id'];
$parts = json_decode($db->query('SELECT parts FROM player_galaxygates2 WHERE gateId = '.$gate_id.' AND userId = '.$userId.'')->fetch()['parts']);
$extraEnergy = $Player->Data['extraEnergy'];
$multiplier = $db->query('SELECT multiplier FROM player_galaxygates2 WHERE gateId = '.$gate_id.' AND userId = '.$userId.'')->fetch()['multiplier'];
$spinamount_selected = isset($_GET['spinamount']) ? $_GET['spinamount'] : 1;
$items = [];

try {
  $db->beginTransaction();

  if ($_GET['action'] === 'multiEnergy' && ($extraEnergy >= 1 || $data->uridium >= 100)) {

    //TODO multiplier
    if (isset($_GET['multiplier']) || $multiplier == 5) {
      $db->query('UPDATE player_galaxygates2 SET multiplier = 0 WHERE gateId = '.$gate_id.' AND userId = '.$userId.'');
    }

    $multiplierReward = false;

    for ($i=0; $i < $spinamount_selected; $i++) {

      if ($extraEnergy >= 1) {
        $extraEnergy -= 1;
        $db->query('UPDATE player_accounts SET extraEnergy = '.$extraEnergy.' WHERE userID = '.$userId.'');
      } elseif ($data->uridium >= 100) {
        $data->uridium -= 100;
        $db->query("UPDATE player_accounts SET Data = '".json_encode($data, JSON_UNESCAPED_UNICODE)."' WHERE userID = ".$userId."");
      } else {
        return;
        //hata mesaji bilmiyorum nasıl otomatik veriyor
      }

      if (rand(0, 100) <= 13) {
          $total = $gates[$mode]['total'];
          $part_id = rand(1, $total);

          if (!in_array($part_id, $parts)) {
            if (count($parts) < $total) {
              array_push($parts, $part_id);

              array_push($items, '<item type="part" '.(count($parts) === $total ? 'state="finished"' : '').' current="'.count($parts).'" gate_id="'.$gate_id.'" part_id="'.$part_id.'" spins="'.$spinamount_selected.'" />');

              $db->query('UPDATE player_galaxygates2 SET parts = "'.json_encode($parts).'" WHERE gateId = '.$gate_id.' AND userId = '.$userId.'');
            }
          } else {
            array_push($items, '<item type="part" duplicate="1" gate_id="'.$gate_id.'" part_id="'.$part_id.'" spins="'.$spinamount_selected.'" />');

            if ($multiplier < 5) {
              $multiplierReward = true;
              $multiplier++;
              $db->query('UPDATE player_galaxygates2 SET multiplier = '.$multiplier.' WHERE gateId = '.$gate_id.' AND userId = '.$userId.'');
            }
          }
      } else {
        if (rand(0, 100) >= 50) {
          array_push($items, '<item type="logfile" amount="'.rand(1, 3).'" spins="'.$spinamount_selected.'" />');
        } else {
          array_push($items, '<item type="nanohull" item_id="1" amount="'.rand(1000, 5000).'" spins="'.$spinamount_selected.'" />');
        }
      }
    }

    if ($multiplierReward && $multiplier >= 1) {
      array_push($items, '<item type="multiplier" amount="'.$multiplier.'" />');
    }
  } else if ($_GET['action'] === 'setupGate') {
    $db->query('UPDATE player_galaxygates2 SET prepared = 1, lives = 5, parts = "[]" WHERE gateId = '.$_GET['gateID'].' AND userId = '.$userId.'');
    //TODO bilmiyorum
  }

  $db->commit();
} catch (Exception $e) {
  $db->rollback();
}

function GetGate($mode, $get)
{
  global $db, $userId, $gates;

  $gate_id = ($mode === 'standard' ? $gates['alpha'] : $gates[$mode])['id'];
  $result = $db->query('SELECT * FROM player_galaxygates2 WHERE gateId = '.$gate_id.' AND userId = '.$userId.'')->fetch()[$get];

  return $result;
}
?>
<?xml version="1.0" encoding="UTF-8"?>
<jumpgate>
   <mode><?php echo $mode;?></mode>
   <money><?php echo $data->uridium; ?></money>
   <samples><?php echo $extraEnergy; ?></samples>
   <spinamount_selected><?php echo $spinamount_selected; ?></spinamount_selected>
   <energy_cost mode="standard">100</energy_cost>
   <hadesGateDialog>0</hadesGateDialog>
   <multipliers>
      <multiplier mode="alpha" value="<?php echo GetGate('alpha', 'multiplier'); ?>" state="0" />
      <multiplier mode="beta" value="<?php echo GetGate('beta', 'multiplier'); ?>" state="0" />
      <multiplier mode="gamma" value="0" state="0" />
      <multiplier mode="delta" value="0" state="0" />
      <multiplier mode="epsilon" value="0" state="0" />
      <multiplier mode="zeta" value="0" state="0" />
      <multiplier mode="kappa" value="0" state="0" />
      <multiplier mode="lambda" value="0" state="0" />
      <multiplier mode="hades" value="0" state="0" />
      <multiplier mode="streuner" value="0" state="0" />
   </multipliers>

   <?php
   if ($_GET['action'] === 'multiEnergy') {
     echo '<items>';
     foreach ($items as $item) {
       echo $item;
     }
     echo '</items>';
   }
   ?>

   <?php
   if ($_GET['action'] === 'setupGate') {
     echo '<setup gate_id="'.$_GET['gateID'].'" />';
   }
   ?>

   <?php if ($_GET['action'] === 'init') { ?>
   <probabilities>
      <probability mode="alpha">
         <!--<cat id="ammunition" percentage="67" />-->
         <!--<cat id="resource" percentage="12" />-->
         <!--<cat id="voucher" percentage="3" />-->
         <cat id="logfile" percentage="43.50" /> <!-- 1 -->
         <cat id="part" percentage="13" /> <!-- 13 -->
         <cat id="ammo_x2" percentage="35" />
         <cat id="ammo_x3" percentage="25" />
         <cat id="ammo_x4" percentage="10" />
         <cat id="ammo_abs" percentage="20" />
         <cat id="ammo_rocket" percentage="5" />
         <cat id="ammo_mine" percentage="5" />
         <cat id="gate_1" percentage="0" />
         <cat id="gate_2" percentage="0" />
         <cat id="gate_3" percentage="0" />
         <cat id="gate_4" percentage="100" />
         <cat id="gate_5" percentage="0" />
         <cat id="gate_6" percentage="0" />
         <cat id="gate_7" percentage="0" />
         <cat id="gate_8" percentage="0" />
         <cat id="gate_13" percentage="0" />
         <cat id="gate_19" percentage="0" />
         <cat id="special" percentage="43.50" /> <!-- 4 -->
         <cat id="special_hitpoints" percentage="100" />
      </probability>
      <probability mode="beta">
         <cat id="ammunition" percentage="67" />
         <cat id="resource" percentage="12" />
         <cat id="voucher" percentage="3" />
         <cat id="logfile" percentage="1" />
         <cat id="part" percentage="13" />
         <cat id="ammo_x2" percentage="35" />
         <cat id="ammo_x3" percentage="25" />
         <cat id="ammo_x4" percentage="10" />
         <cat id="ammo_abs" percentage="20" />
         <cat id="ammo_rocket" percentage="5" />
         <cat id="ammo_mine" percentage="5" />
         <cat id="gate_1" percentage="0" />
         <cat id="gate_2" percentage="0" />
         <cat id="gate_3" percentage="0" />
         <cat id="gate_4" percentage="100" />
         <cat id="gate_5" percentage="0" />
         <cat id="gate_6" percentage="0" />
         <cat id="gate_7" percentage="0" />
         <cat id="gate_8" percentage="0" />
         <cat id="gate_13" percentage="0" />
         <cat id="gate_19" percentage="0" />
         <cat id="special" percentage="4" />
         <cat id="special_hitpoints" percentage="100" />
      </probability>
      <probability mode="gamma">
         <cat id="ammunition" percentage="67" />
         <cat id="resource" percentage="12" />
         <cat id="voucher" percentage="3" />
         <cat id="logfile" percentage="1" />
         <cat id="part" percentage="13" />
         <cat id="ammo_x2" percentage="35" />
         <cat id="ammo_x3" percentage="25" />
         <cat id="ammo_x4" percentage="10" />
         <cat id="ammo_abs" percentage="20" />
         <cat id="ammo_rocket" percentage="5" />
         <cat id="ammo_mine" percentage="5" />
         <cat id="gate_1" percentage="0" />
         <cat id="gate_2" percentage="0" />
         <cat id="gate_3" percentage="0" />
         <cat id="gate_4" percentage="100" />
         <cat id="gate_5" percentage="0" />
         <cat id="gate_6" percentage="0" />
         <cat id="gate_7" percentage="0" />
         <cat id="gate_8" percentage="0" />
         <cat id="gate_13" percentage="0" />
         <cat id="gate_19" percentage="0" />
         <cat id="special" percentage="4" />
         <cat id="special_hitpoints" percentage="100" />
      </probability>
      <probability mode="delta">
         <cat id="ammunition" percentage="67" />
         <cat id="resource" percentage="12" />
         <cat id="voucher" percentage="3" />
         <cat id="logfile" percentage="1" />
         <cat id="part" percentage="13" />
         <cat id="ammo_x2" percentage="35" />
         <cat id="ammo_x3" percentage="25" />
         <cat id="ammo_x4" percentage="10" />
         <cat id="ammo_abs" percentage="20" />
         <cat id="ammo_rocket" percentage="5" />
         <cat id="ammo_mine" percentage="5" />
         <cat id="gate_1" percentage="0" />
         <cat id="gate_2" percentage="0" />
         <cat id="gate_3" percentage="0" />
         <cat id="gate_4" percentage="100" />
         <cat id="gate_5" percentage="0" />
         <cat id="gate_6" percentage="0" />
         <cat id="gate_7" percentage="0" />
         <cat id="gate_8" percentage="0" />
         <cat id="gate_13" percentage="0" />
         <cat id="gate_19" percentage="0" />
         <cat id="special" percentage="4" />
         <cat id="special_hitpoints" percentage="100" />
      </probability>
      <probability mode="epsilon">
         <cat id="ammunition" percentage="67" />
         <cat id="resource" percentage="12" />
         <cat id="voucher" percentage="3" />
         <cat id="logfile" percentage="1" />
         <cat id="part" percentage="13" />
         <cat id="ammo_x2" percentage="35" />
         <cat id="ammo_x3" percentage="25" />
         <cat id="ammo_x4" percentage="10" />
         <cat id="ammo_abs" percentage="20" />
         <cat id="ammo_rocket" percentage="5" />
         <cat id="ammo_mine" percentage="5" />
         <cat id="gate_1" percentage="0" />
         <cat id="gate_2" percentage="0" />
         <cat id="gate_3" percentage="0" />
         <cat id="gate_4" percentage="0" />
         <cat id="gate_5" percentage="100" />
         <cat id="gate_6" percentage="0" />
         <cat id="gate_7" percentage="0" />
         <cat id="gate_8" percentage="0" />
         <cat id="gate_13" percentage="0" />
         <cat id="gate_19" percentage="0" />
         <cat id="special" percentage="4" />
         <cat id="special_hitpoints" percentage="100" />
      </probability>
      <probability mode="zeta">
         <cat id="ammunition" percentage="67" />
         <cat id="resource" percentage="12" />
         <cat id="voucher" percentage="3" />
         <cat id="logfile" percentage="1" />
         <cat id="part" percentage="13" />
         <cat id="ammo_x2" percentage="35" />
         <cat id="ammo_x3" percentage="25" />
         <cat id="ammo_x4" percentage="10" />
         <cat id="ammo_abs" percentage="20" />
         <cat id="ammo_rocket" percentage="5" />
         <cat id="ammo_mine" percentage="5" />
         <cat id="gate_1" percentage="0" />
         <cat id="gate_2" percentage="0" />
         <cat id="gate_3" percentage="0" />
         <cat id="gate_4" percentage="0" />
         <cat id="gate_5" percentage="0" />
         <cat id="gate_6" percentage="100" />
         <cat id="gate_7" percentage="0" />
         <cat id="gate_8" percentage="0" />
         <cat id="gate_13" percentage="0" />
         <cat id="gate_19" percentage="0" />
         <cat id="special" percentage="4" />
         <cat id="special_hitpoints" percentage="100" />
      </probability>
      <probability mode="kappa">
         <cat id="ammunition" percentage="67" />
         <cat id="resource" percentage="12" />
         <cat id="voucher" percentage="3" />
         <cat id="logfile" percentage="1" />
         <cat id="part" percentage="13" />
         <cat id="ammo_x2" percentage="35" />
         <cat id="ammo_x3" percentage="25" />
         <cat id="ammo_x4" percentage="10" />
         <cat id="ammo_abs" percentage="20" />
         <cat id="ammo_rocket" percentage="5" />
         <cat id="ammo_mine" percentage="5" />
         <cat id="gate_1" percentage="0" />
         <cat id="gate_2" percentage="0" />
         <cat id="gate_3" percentage="0" />
         <cat id="gate_4" percentage="0" />
         <cat id="gate_5" percentage="0" />
         <cat id="gate_6" percentage="0" />
         <cat id="gate_7" percentage="100" />
         <cat id="gate_8" percentage="0" />
         <cat id="gate_13" percentage="0" />
         <cat id="gate_19" percentage="0" />
         <cat id="special" percentage="4" />
         <cat id="special_hitpoints" percentage="100" />
      </probability>
      <probability mode="lambda">
         <cat id="ammunition" percentage="67" />
         <cat id="resource" percentage="12" />
         <cat id="voucher" percentage="3" />
         <cat id="logfile" percentage="1" />
         <cat id="part" percentage="13" />
         <cat id="ammo_x2" percentage="35" />
         <cat id="ammo_x3" percentage="25" />
         <cat id="ammo_x4" percentage="10" />
         <cat id="ammo_abs" percentage="20" />
         <cat id="ammo_rocket" percentage="5" />
         <cat id="ammo_mine" percentage="5" />
         <cat id="gate_1" percentage="0" />
         <cat id="gate_2" percentage="0" />
         <cat id="gate_3" percentage="0" />
         <cat id="gate_4" percentage="0" />
         <cat id="gate_5" percentage="0" />
         <cat id="gate_6" percentage="0" />
         <cat id="gate_7" percentage="0" />
         <cat id="gate_8" percentage="100" />
         <cat id="gate_13" percentage="0" />
         <cat id="gate_19" percentage="0" />
         <cat id="special" percentage="4" />
         <cat id="special_hitpoints" percentage="100" />
      </probability>
      <probability mode="hades">
         <cat id="ammunition" percentage="67" />
         <cat id="resource" percentage="12" />
         <cat id="voucher" percentage="3" />
         <cat id="logfile" percentage="1" />
         <cat id="part" percentage="13" />
         <cat id="ammo_x2" percentage="35" />
         <cat id="ammo_x3" percentage="25" />
         <cat id="ammo_x4" percentage="10" />
         <cat id="ammo_abs" percentage="20" />
         <cat id="ammo_rocket" percentage="5" />
         <cat id="ammo_mine" percentage="5" />
         <cat id="gate_1" percentage="0" />
         <cat id="gate_2" percentage="0" />
         <cat id="gate_3" percentage="0" />
         <cat id="gate_4" percentage="0" />
         <cat id="gate_5" percentage="0" />
         <cat id="gate_6" percentage="0" />
         <cat id="gate_7" percentage="0" />
         <cat id="gate_8" percentage="0" />
         <cat id="gate_13" percentage="100" />
         <cat id="gate_19" percentage="0" />
         <cat id="special" percentage="4" />
         <cat id="special_hitpoints" percentage="100" />
      </probability>
      <probability mode="streuner">
         <cat id="ammunition" percentage="67" />
         <cat id="resource" percentage="12" />
         <cat id="voucher" percentage="3" />
         <cat id="logfile" percentage="1" />
         <cat id="part" percentage="13" />
         <cat id="ammo_x2" percentage="35" />
         <cat id="ammo_x3" percentage="25" />
         <cat id="ammo_x4" percentage="10" />
         <cat id="ammo_abs" percentage="20" />
         <cat id="ammo_rocket" percentage="5" />
         <cat id="ammo_mine" percentage="5" />
         <cat id="gate_1" percentage="0" />
         <cat id="gate_2" percentage="0" />
         <cat id="gate_3" percentage="0" />
         <cat id="gate_4" percentage="0" />
         <cat id="gate_5" percentage="0" />
         <cat id="gate_6" percentage="0" />
         <cat id="gate_7" percentage="0" />
         <cat id="gate_8" percentage="0" />
         <cat id="gate_13" percentage="0" />
         <cat id="gate_19" percentage="100" />
         <cat id="special" percentage="4" />
         <cat id="special_hitpoints" percentage="100" />
      </probability>
   </probabilities>
   <gates>
      <gate total="34" current="<?php echo count(json_decode(GetGate('alpha', 'parts'))); ?>" id="1" prepared="<?php echo GetGate('alpha', 'prepared'); ?>" totalWave="40" currentWave="0" state="<?php echo count(json_decode(GetGate('alpha', 'parts'))) == 34 && GetGate('alpha', 'prepared') == 0 ? 'finished' : 'in_progress'; ?>" livesLeft="<?php echo GetGate('alpha', 'lives'); ?>" lifePrice="5000" />
      <gate total="48" current="0" id="2" prepared="0" totalWave="40" currentWave="0" state="in_progress" livesLeft="-1" lifePrice="-1" />
      <gate total="82" current="0" id="3" prepared="0" totalWave="40" currentWave="0" state="in_progress" livesLeft="-1" lifePrice="-1" />
      <gate total="128" current="0" id="4" prepared="0" totalWave="29" currentWave="0" state="in_progress" livesLeft="-1" lifePrice="-1" />
      <gate total="99" current="0" id="5" prepared="0" totalWave="30" currentWave="0" state="in_progress" livesLeft="-1" lifePrice="-1" />
      <gate total="111" current="0" id="6" prepared="0" totalWave="46" currentWave="0" state="in_progress" livesLeft="-1" lifePrice="-1" />
      <gate total="120" current="0" id="7" prepared="0" totalWave="29" currentWave="0" state="in_progress" livesLeft="-1" lifePrice="-1" />
      <gate total="45" current="0" id="8" prepared="0" totalWave="21" currentWave="0" state="in_progress" livesLeft="-1" lifePrice="-1" />
      <gate total="21" current="0" id="12" prepared="0" totalWave="50" currentWave="0" state="in_progress" livesLeft="-1" lifePrice="-1">
         <gatebuilders name="Alpha" current="0" total="4" />
         <gatebuilders name="Beta" current="0" total="3" />
         <gatebuilders name="Gamma" current="0" total="1" />
         <gatebuilders name="Delta" current="0" total="1" />
         <gatebuilders name="Epsilon" current="0" total="4" />
         <gatebuilders name="Zeta" current="0" total="1" />
         <gatebuilders name="Kappa" current="0" total="2" />
         <gatebuilders name="Lambda" current="0" total="5" />
      </gate>
      <gate total="45" current="0" id="13" prepared="0" totalWave="12" currentWave="0" state="in_progress" livesLeft="-1" lifePrice="-1" />
      <!--<gate total="100" current="0" id="19" prepared="0" totalWave="54" currentWave="0" state="in_progress" livesLeft="-1" lifePrice="-1" />-->
   </gates>
   <spinamounts>
      <spinamount>1</spinamount>
      <spinamount>5</spinamount>
      <spinamount>10</spinamount>
      <spinamount>100</spinamount>
   </spinamounts>
   <boosts>
   </boosts>
   <?php } ?>
   <spinOnSale>0</spinOnSale>
   <spinSalePercentage>0</spinSalePercentage>
   <galaxyGateDay>0</galaxyGateDay>
   <bonusRewardsDay>0</bonusRewardsDay>
</jumpgate>
