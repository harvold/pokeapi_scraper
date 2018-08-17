<?php
//802 is the last one
for ($i = 501; $i <= 802; $i++)
{
	$get_data = callAPI('GET', "https://pokeapi.co/api/v2/pokemon/" . $i . "/", false);
	$response = json_decode($get_data, true);
	$name = $response['species']['name'];

	for ($x = 0, $y = 1; $x < count($response['abilities']); $x++)
	{
		if ($response['abilities'][$x]['is_hidden'])
		{
			$ability_hidden = $response['abilities'][$x]['ability']['name'];
		}
		else if ($y == 1)
		{
			$ability1 = $response['abilities'][$x]['ability']['name'];
			$y++;
		}
		else
		{
			$ability2 = $response['abilities'][$x]['ability']['name'];
		}
	}
	$num_abilities = count($response['abilities']);
	if ($num_abilities == 2)
	{
		$ability2 = "";
	}

	$base_stats = $response['stats'];

	$spe_ev_given = $base_stats[0]['effort'];
	$base_spe = $base_stats[0]['base_stat'];
	$spd_ev_given = $base_stats[1]['effort'];
	$base_spd = $base_stats[1]['base_stat'];
	$spa_ev_given = $base_stats[2]['effort'];
	$base_spa = $base_stats[2]['base_stat'];
	$def_ev_given = $base_stats[3]['effort'];
	$base_def = $base_stats[3]['base_stat'];
	$atk_ev_given = $base_stats[4]['effort'];
	$base_atk = $base_stats[4]['base_stat'];
	$hp_ev_given = $base_stats[5]['effort'];
	$base_hp = $base_stats[5]['base_stat'];

	$moves = [];
	$found = false;

	for ($x = 0; $x < count($response['moves']); $x++)
	{
		$move_target = $response['moves'][$x];
		$move['name'] = $move_target['move']['name'];
		//echo $move['name'] . "<br/>";
		for ($y = 0; $y < count($move_target['version_group_details']); $y++)
		{
			if ($move_target['version_group_details'][$y]['version_group']['name'] === 'sun-moon')
			{
				$move['level'] = $move_target['version_group_details'][$y]['level_learned_at'];
				$move['learn_method'] = $move_target['version_group_details'][$y]['move_learn_method']['name'];
				
				$moves[] = $move;
				$found = true;
				break;
			}
		}
		if (!$found)
		{
			for ($y = 0; $y < count($move_target['version_group_details']); $y++)
			{
				if ($move_target['version_group_details'][$y]['version_group']['name'] === 'x-y' || 
				$move_target['version_group_details'][$y]['version_group']['name'] === 'omega-ruby-alpha-sapphire' ||
				$move_target['version_group_details'][$y]['version_group']['name'] === 'heartgold-soulsilver')
				{
					$move['level'] = $move_target['version_group_details'][$y]['level_learned_at'];
					$move['learn_method'] = $move_target['version_group_details'][$y]['move_learn_method']['name'];
					
					$moves[] = $move;
					$found = true;
					break;
				}
			}
			if (!$found)
			{
				for ($y = 0; $y < count($move_target['version_group_details']); $y++)
				{
					if ($move_target['version_group_details'][$y]['version_group']['name'] === 'black-white' || $move_target['version_group_details'][$y]['version_group']['name'] === 'black-2-white-2')
					{
						$move['level'] = $move_target['version_group_details'][$y]['level_learned_at'];
						$move['learn_method'] = $move_target['version_group_details'][$y]['move_learn_method']['name'];
						
						$moves[] = $move;
						$found = true;
						break;
					}
				}
				if (!$found)
				{
					for ($y = 0; $y < count($move_target['version_group_details']); $y++)
					{
						if ($move_target['version_group_details'][$y]['version_group']['name'] === 'diamond-pearl')
						{
							$move['level'] = $move_target['version_group_details'][$y]['level_learned_at'];
							$move['learn_method'] = $move_target['version_group_details'][$y]['move_learn_method']['name'];
							
							$moves[] = $move;
							$found = true;
							break;
						}
					}
					if (!$found)
					{
						for ($y = 0; $y < count($move_target['version_group_details']); $y++)
						{
							if ($move_target['version_group_details'][$y]['version_group']['name'] === 'emerald-pearl')
							{
								$move['level'] = $move_target['version_group_details'][$y]['level_learned_at'];
								$move['learn_method'] = $move_target['version_group_details'][$y]['move_learn_method']['name'];
								
								$moves[] = $move;
								$found = true;
								break;
							}
						}
						if (!$found)
						{
							//echo ($move['name'] . " not found in diamond-pearl");
							//echo ("<br/>");
						}
					}
				}
			}
		}
		$found = false;
	}

	$num_types = count($response['types']);
	$type1 = $response['types'][0]['type']['name'];

	if ($num_types == 2)
	{
		$type2 = $response ['types'][1]['type']['name'];
	}
	else 
	{
		$type2 = "";
	}

	$moves_to_enter = json_encode($moves);

	$con = new mysqli("localhost", "root", "", "harvold");
	$sql = "INSERT INTO pokemon_ref (`id`, `name`, `type_1`, `type_2`, `ability_1`, `ability_2`, `ability_hidden`, `hp_ev_given`, `atk_ev_given`, `spa_ev_given`, `spd_ev_given`, `spe_ev_given`, `def_ev_given`,`base_atk`, `base_def`, `base_hp`, `base_spd`, `base_spa`, `base_spe`, `move_list`) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?);";

	$stmt = $con->prepare($sql);
	$stmt->bind_param("issssssiiiiiiiiiiiis", $i, $name, $type1, $type2, $ability1, $ability2, $ability_hidden, $hp_ev_given, $atk_ev_given, $spa_ev_given, 
			$spd_ev_given, $spa_ev_given, $def_ev_given, $base_atk, $base_def, $base_hp, $base_spd, $base_spa, $base_spe, $moves_to_enter);
	$stmt->execute();
	$assoc = $stmt->affected_rows;
	echo $con->error;
		
	$stmt->close();
	//echo "Executed! $assoc <br/>";
}
//echo ($base_atk);

function callAPI($method, $url, $data){
   $curl = curl_init();
   switch ($method){
      case "POST":
         curl_setopt($curl, CURLOPT_POST, 1);
         if ($data)
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
         break;
      case "PUT":
         curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
         if ($data)
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);                         
         break;
      default:
         if ($data)
            $url = sprintf("%s?%s", $url, http_build_query($data));
   }
   // OPTIONS:
   curl_setopt($curl, CURLOPT_URL, $url);
   curl_setopt($curl, CURLOPT_HTTPHEADER, array(
      'APIKEY: 111111111111111111111',
      'Content-Type: application/json',
   ));
   curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
   curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
   // EXECUTE:
   $result = curl_exec($curl);
   if(!$result){die("Connection Failure");}
   curl_close($curl);
   return $result;
}

?>