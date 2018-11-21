<?php 
#   PLEASE DO NOT REMOVE OR CHANGE THIS COPYRIGHT BLOCK
#   ====================================================================
#
#    Quran Analysis (www.qurananalysis.com). Full Semantic Search and Intelligence System for the Quran.
#    Copyright (C) 2015  Karim Ouda
#
#    This program is free software: you can redistribute it and/or modify
#    it under the terms of the GNU General Public License as published by
#    the Free Software Foundation, either version 3 of the License, or
#    (at your option) any later version.
#
#    This program is distributed in the hope that it will be useful,
#    but WITHOUT ANY WARRANTY; without even the implied warranty of
#    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
#    GNU General Public License for more details.
#
#    You should have received a copy of the GNU General Public License
#    along with this program.  If not, see <http://www.gnu.org/licenses/>.
#
#    You can use Quran Analysis code, framework or corpora in your website
#	 or application (commercial/non-commercial) provided that you link
#    back to www.qurananalysis.com and sufficient credits are given.
#
#  ====================================================================
require_once(dirname(__FILE__)."/../global.settings.php");
require_once(dirname(__FILE__)."/core.lib.php");

function convertConceptIDtoGraphLabel($conceptID)
{
	return ucfirst(str_replace("_", " ", $conceptID));;
}
function convertWordToConceptID($word)
{
	return (str_replace(" ", "_", $word));;
}

function formatEnglishConcept($conceptEN)
{
	return ucfirst(removeBasicEnglishStopwordsNoNegation($conceptEN));
}
function textToGraph($searchResultTextArr,$excludes,$capping=300)
{
	global $pauseMarksFile, $lang;
	
	$MAX_CAP = $capping;
	
	$graphObj = array();
	$graphObj["capped"]=0;
	
	$graphNodes = array();
	$graphLinks = array();
	
	$pauseMarksArr = getPauseMarksArrByFile($pauseMarksFile);
	
	/** SHOULD BE ZERO BASED FOR D3 TO WORK - o.target.weight = NULL**/
	$nodeSerialNumber = 0;
	
	$lastWord = null;
	
	foreach($searchResultTextArr as $index => $text)
	{
	
		$textWordsArr = preg_split("/ /",$text);
		
		//echoN($text);
		
		foreach($textWordsArr as $word)
		{
			
			
			if ( $lang == "EN")
			{
				$word = cleanAndTrim($word);
				$word = strtolower($word);
			}
			
			//echoN($word);
			
			
			if ( $pauseMarksArr[$word]) continue;
			
			if ( $excludes[$word]==1) continue;
		

			
			if ( !isset($graphNodes[$word]) )
			{
				$graphNodes[$word]= array("id"=>$nodeSerialNumber++,"word"=>$word,"size"=>1,"x"=>rand(1,800),"y"=>rand(1,400));
			}
			else
			{
				$graphNodes[$word]["size"]=$graphNodes[$word]["size"]+1;
			}
			
			
			if ( $lastWord!=null )
			{
				$graphLinks[]=array("source"=>$graphNodes[$lastWord]["id"],"target"=>$graphNodes[$word]["id"]);
			}
			
			$lastWord = $word;
			
		}
		
		if ( count($graphNodes) > $MAX_CAP )
		{
			$graphObj["capped"]=$MAX_CAP;
			break;
		}
		
		
	}
	
	$graphObj["nodes"]=$graphNodes;
	$graphObj["links"]=$graphLinks;	
	
	//preprint_r($graphLinks);
	//preprint_r($graphNodes);
	
	return $graphObj;
}

function createNewConceptObj(&$nodeSerialNumber,$lang,$finalNodeLabel,$ontologyConceptArr,$randomXLocation,$randomYLocation,$nodeLevel)
{
	

	
	$conceptLabelAR = $ontologyConceptArr['label_ar'];
	$conceptLabelEN = $ontologyConceptArr['label_en'];
	$conceptFrequency = $ontologyConceptArr['frequency'];
	$conceptWeight = $ontologyConceptArr['weight'];
	
	if ( $lang=="EN")
	{
		$conceptShortDesc = ucfirst(trim(htmlspecialchars($ontologyConceptArr['meaning_wordnet_en'],ENT_QUOTES)));
	}
	else 
	{
		$conceptShortDesc = htmlspecialchars($ontologyConceptArr['meaning_wordnet_translated_ar'],ENT_QUOTES);
	}
	
	$conceptImage = $ontologyConceptArr['image_url'];
	
	if ( $lang=="EN")
	{
		$conceptLongDesc = htmlspecialchars($ontologyConceptArr['long_description_en'],ENT_QUOTES);
		
		//preprint_r($ontologyConceptArr);
	}
	else
	{
		$conceptLongDesc = htmlspecialchars($ontologyConceptArr['long_description_ar'],ENT_QUOTES);
	}
	
	$conceptWikipediaLink = $ontologyConceptArr['wikipedia_link'];
	
	if ( empty($finalNodeLabel))
	{
		$finalNodeLabel = "NA/$nodeSerialNumber";
	}
	
	// IT IS CRUCIAL FOR SIZE TO BE A VALID NUMBER, ELSE D3 WILL NOT FUNCTION CORRECTLY ( LINK-DISTANCE+FORCE CALCULATION)
	if ( empty($conceptWeight)) { $conceptWeight=1;}
	
	// IT IS CRUCIAL FOR SIZE TO BE A VALID NUMBER, ELSE D3 WILL NOT FUNCTION CORRECTLY ( LINK-DISTANCE+FORCE CALCULATION)
	if ( empty($conceptFrequency)) { $conceptFrequency=1;}
	
	
	//echoN($finalNodeLabel."--".$nodeSerialNumber);
	
	return array("id"=>$nodeSerialNumber++,"word"=>ucfirst($finalNodeLabel),
			"size"=>$conceptFrequency,"frequency"=>$conceptFrequency,
			"level"=>$nodeLevel,
			"short_desc"=>$conceptShortDesc,"long_desc"=>$conceptLongDesc,
			"external_link"=>$conceptWikipediaLink,"image_url"=>$conceptImage,
			"x"=>$randomXLocation,"y"=>$randomYLocation);
}

function ontologyTextToD3Graph($MODEL_QA_ONTOLOGY,$inputType,$searchResultTextArr,$minFreq=0,$widthHeigthArr,$lang,$mainConceptsOnly=false,$isPhraseSearch=false,$isQuestion=false,$query="")
{
	
	global $thing_class_name_ar, $is_a_relation_name_ar;

	$graphObj = array();


	$graphNodes = array();
	$graphLinks = array();
	
	
	////// calculate start points
	$width  = $widthHeigthArr[0];
	$height  = $widthHeigthArr[1];
	
	$startLocationXMin = ($width/2)-100;
	$startLocationXMax = ($width/2)+100;;
	$startLocationYMin = ($height/2)-100;
	$startLocationYMax = ($height/2)+100;;
	

	
	
	////////////////////////////


	
	/** SHOULD BE ZERO BASED FOR D3 TO WORK - o.target.weight = NULL**/
	$nodeSerialNumber = 0;
	
	$lastWord = null;
	

	
	foreach($searchResultTextArr as $index => $text)
	{
	
		if ( $inputType=="SEARCH_RESULTS_TEXT_ARRAY")
		{
			$textWordsArr = preg_split("/ /",$text);
		}
		// QUERY_TERMS TYPE
		else 
		{
			if ( !$isPhraseSearch)
			{
			
				// extendedQueryParam
				$textWordsArr = array_keys($searchResultTextArr);
			}
			// IS PHRASE SEARCH
			else
			{
				// phrase should be checked as is
				$textWordsArr[0]=$query;
			}
		}
		
		

		
		foreach($textWordsArr as $word)
		{
			
			
			if ( $lang == "EN")
			{
				$word = cleanAndTrim($word);
				$word = strtolower($word);
				
				// translate English name to arabic concept name/id
				//$wordConveretedToConceptID = $MODEL_QA_ONTOLOGY['CONCEPTS_EN_AR_NAME_MAP'][$word];
				
				$wordConveretedToConceptID  = getModelEntryFromMemory("ALL", "MODEL_QA_ONTOLOGY", "CONCEPTS_EN_AR_NAME_MAP", $word);
				
			}
			else 
			{
			
				$wordConveretedToConceptID = convertWordToConceptID($word);
			}
			
		
			
			if ( modelEntryExistsInMemory("ALL", "MODEL_QA_ONTOLOGY", "CONCEPTS", $wordConveretedToConceptID) )
			{

				
				
				//preprint_r($MODEL_QA_ONTOLOGY['CONCEPTS'][$wordConveretedToConceptID]);exit;
				//echoN($wordConveretedToConceptID);
				$mainConceptArr  = getModelEntryFromMemory("ALL", "MODEL_QA_ONTOLOGY", "CONCEPTS", $wordConveretedToConceptID);
				//$mainConceptArr = $MODEL_QA_ONTOLOGY['CONCEPTS'][$wordConveretedToConceptID];
				
				$conceptLabelAR = $mainConceptArr['label_ar'];
				$conceptLabelEN = $mainConceptArr['label_en'];
				$conceptFrequency = $mainConceptArr['frequency'];
				$conceptWeight = $mainConceptArr['weight'];
				
				$finalNodeLabel = $conceptLabelAR;
				
				if ( $lang == "EN")
				{
					$finalNodeLabel = $conceptLabelEN;
				}
				
				/*if ( empty($finalNodeLabel))
				{
					echoN($conceptLabelAR);
					exit;
				}*/
		
					
				if ( $conceptFrequency< $minFreq) continue;
				
				if (  $wordConveretedToConceptID==$thing_class_name_ar) continue;
				
			
				if ( !isset($graphNodes[$wordConveretedToConceptID]) )
				{
				
					
					$randomXLocation = rand($startLocationXMin,$startLocationXMax);
					$randomYLocation = rand($startLocationYMin,$startLocationYMax);
				
		
				
					$graphNodes[$wordConveretedToConceptID]= createNewConceptObj($nodeSerialNumber,$lang, $finalNodeLabel, $mainConceptArr,$randomXLocation,$randomYLocation,1);
				
					
					
				}
					
				
						

					
					
				
			}
			
		}
	}

	


	$tooManyConcepts = (count($graphNodes) > 200);
	

	$ONTOLOGY_RELATIONS = getModelEntryFromMemory("ALL", "MODEL_QA_ONTOLOGY", "RELATIONS", "");
	
	//preprint_r($graphNodes,1);exit;
	
	$linksHashLookupTable = array();
	
	//preprint_r($graphNodes,true);exit;
	

		foreach($graphNodes as $concept => $conceptArr)
		{
		
			$conceptID = convertWordToConceptID($concept);
			//$relationsOfConceptAsSource = $MODEL_QA_ONTOLOGY['GRAPH_INDEX_SOURCES'][$conceptID];
			
			$relationsOfConceptAsSource  = getModelEntryFromMemory("ALL", "MODEL_QA_ONTOLOGY", "GRAPH_INDEX_SOURCES", $conceptID);
			
			//$relationsOfConceptAsTarget = $MODEL_QA_ONTOLOGY['GRAPH_INDEX_TARGETS'][$conceptID];
				
			$relationsOfConceptAsTarget  = getModelEntryFromMemory("ALL", "MODEL_QA_ONTOLOGY", "GRAPH_INDEX_TARGETS", $conceptID);
		
			
			foreach( $relationsOfConceptAsSource as $index => $relArr)
			{
					
				$verb  = $relArr["link_verb"];
				$object = $relArr["target"];
				
			
				
				
				//echoN("$verb==$is_a_relation_name_ar && $object==$thing_class_name_ar");
				// ignore is-a thing relations
				if ( $verb==$is_a_relation_name_ar && $object==$thing_class_name_ar) continue;
				
				if ( $tooManyConcepts && $verb==$is_a_relation_name_ar) continue;
				
				// IF SHOWING MAIN CONCEPTS ONLY, IGNORE CONCEPTS NOT IN MAIN CONCEPTS LIST 
				if ($mainConceptsOnly &&  !isset($graphNodes[$object])) continue;
				
				// NO extending by relations in case of search result text 
				// to reduce number of concepts we only add relations with other concepts 
				// found in the text
				if ( $inputType=="SEARCH_RESULTS_TEXT_ARRAY" &&  !isset($graphNodes[$object])) continue;
				
				//preprint_r($relArr,true);
	
				$randomXLocation = rand($startLocationXMin,$startLocationXMax);
				$randomYLocation = rand($startLocationYMin,$startLocationYMax);
				
				$relHashID = buildRelationHashID($conceptID,$verb,$object);
				
				$fullRelationArr = $ONTOLOGY_RELATIONS[$relHashID];
				
				
				//$conceptArr = $MODEL_QA_ONTOLOGY['CONCEPTS'][$object];
				$conceptArr  = getModelEntryFromMemory("ALL", "MODEL_QA_ONTOLOGY", "CONCEPTS", $object);
				
			
				$finalNodeLabel = $conceptArr['label_ar'];
					
				if ( $lang == "EN")
				{
					$finalNodeLabel = formatEnglishConcept($conceptArr['label_en']);
					$verb = $fullRelationArr['VERB_TRANSLATION_EN'];
				
				}
				
				if ( !isset($graphNodes[$object]))
				{
	
					$graphNodes[$object]= createNewConceptObj($nodeSerialNumber,$lang, $finalNodeLabel, $conceptArr,$randomXLocation,$randomYLocation,2);
				}
			
				$linkArr=array("source"=>$graphNodes[$concept]["id"],
						"target"=>$graphNodes[$object]["id"],"link_verb"=>$verb,"link_frequency"=>$fullRelationArr['FREQUENCY']);
				
				//////// HANDLING MULTIPLE LINKS BETWEEN SAME NODES BEFORE ASSIGNING LINK
				$arrHash = getArrayHashForFields($linkArr,array('source','target'));
				
				/*preprint_r($graphNodes);
				echoN($finalNodeLabel);
				preprint_r($linkArr);*/
				
					
				if ( !isset($linksHashLookupTable[$arrHash]))
				{
					$graphLinks[]=$linkArr;
					
					$linksHashLookupTable[$arrHash]=(count($graphLinks)-1);
				}
				else
				{
					$linkIndex = $linksHashLookupTable[$arrHash];
					
					if ( strpos($graphLinks[$linkIndex]['link_verb'],"$verb")===false )
					{
						$graphLinks[$linkIndex]['link_verb'].= ",".$verb;		
					}		
				}
				
				/*if (  $MODEL_QA_ONTOLOGY['CONCEPTS'][$object]['label_en']=="help")
				{
					echoN(isset($graphNodes[$object])." ".$object," ");
					echoN($concept);
					preprint_r($graphLinks[$linkIndex]);
					preprint_r($graphNodes[$object]);
					preprint_r($graphNodes[$concept]);
					exit;
					
				}*/
				
				
				/////////////////////////////////////////////////////////////
			
			}
				
			foreach( $relationsOfConceptAsTarget as $index => $relArr)
			{
					
				$verb    = $relArr["link_verb"];
				$subject = $relArr["source"];
				$relationIndex = $relArr['relation_index'];
			
				// IF SHOWING MAIN CONCEPTS ONLY, IGNORE CONCEPTS NOT IN MAIN CONCEPTS LIST
				if ($mainConceptsOnly &&  !isset($graphNodes[$subject])) continue;
				
				if ( $tooManyConcepts && $verb==$is_a_relation_name_ar) continue;
				
				
				// NO extending by relations in case of search result text
				// to reduce number of concepts we only add relations with other concepts
				// found in the text
				if ( $inputType=="SEARCH_RESULTS_TEXT_ARRAY" &&  !isset($graphNodes[$object])) continue;
				
		
				
				$relHashID = buildRelationHashID($subject,$verb,$concept);
				$fullRelationArr = $ONTOLOGY_RELATIONS[$relHashID];
			
				$randomXLocation = rand($startLocationXMin,$startLocationXMax);
				$randomYLocation = rand($startLocationYMin,$startLocationYMax);
				
				//$conceptArr = $MODEL_QA_ONTOLOGY['CONCEPTS'][$subject];
				$conceptArr  = getModelEntryFromMemory("ALL", "MODEL_QA_ONTOLOGY", "CONCEPTS", $subject);
		
				$finalNodeLabel = $conceptArr['label_ar'];
				
				if ( $lang == "EN")
				{
					$finalNodeLabel = formatEnglishConcept($conceptArr['label_en']);;
					$verb = $fullRelationArr['VERB_TRANSLATION_EN'];
				}
				
				if ( !isset($graphNodes[$subject]))
				{
				
					
	
					
					$graphNodes[$subject]= createNewConceptObj($nodeSerialNumber,$lang, $finalNodeLabel, $conceptArr,$randomXLocation,$randomYLocation,2);
					
				}
				
				
			
			
				$linkArr = array("source"=>$graphNodes[$subject]["id"],
						"target"=>$graphNodes[$concept]["id"],"link_verb"=>$verb,"link_frequency"=>$fullRelationArr['frequency']);
				
	
				//////// HANDLING MULTIPLE LINKS BETWEEN SAME NODES BEFORE ASSIGNING LINK
				$arrHash = getArrayHashForFields($linkArr,array('source','target'));
				
					
				if ( !isset($linksHashLookupTable[$arrHash]))
				{
					$graphLinks[]=$linkArr;
					
					$linksHashLookupTable[$arrHash]=(count($graphLinks)-1);
				}
				else
				{
					$linkIndex = $linksHashLookupTable[$arrHash];
					
					if ( strpos($graphLinks[$linkIndex]['link_verb'],"$verb")===false )
					{
						$graphLinks[$linkIndex]['link_verb'].= ",".$verb;		
					}					
				}
				
				
				//////////////////////////////////////////////////////////////
					
			}
			
		}
	

	
	//preprint_r($graphLinks);exit;
	

	
	
	$graphNodesArr = array();

	foreach($graphNodes as $word => $nodeArr)
	{

		$graphNodesArr[] = $nodeArr;

	}
	
	//preprint_r($graphNodesArr,1);exit;

	//$graphNodesArr = array_slice($graphNodesArr, 1,10);
	//$graphLinks = array_slice($graphLinks, 1,10);

	$graphObj["nodes"]=$graphNodesArr;
	$graphObj["links"]=$graphLinks;


	return $graphObj;
}



function ontologyToD3Graph($MODEL_QA_ONTOLOGY,$minFreq=0)
{
	global $lang;


	$graphObj = array();
	$graphObj["capped"]=0;

	$graphNodes = array();
	$graphLinks = array();


	/** SHOULD BE ZERO BASED FOR D3 TO WORK - o.target.weight = NULL**/
	$nodeSerialNumber = 0;

	$qaOntologyConceptsIterator = getAPCIterator("ALL\/MODEL_QA_ONTOLOGY\/CONCEPTS\/.*");
	
	$conceptCount = $qaOntologyConceptsIterator->getTotalCount();

	if ( count($conceptCount) < 100)
	{
		$startLocationXMin = 100;
		$startLocationXMax = 200;
		$startLocationYMin = 100;
		$startLocationYMax = 200;
	}
	else
	{
		$startLocationXMin = 100;
		$startLocationXMax = 200;
		$startLocationYMin = 200;
		$startLocationYMax = 300;
	}

	foreach($qaOntologyConceptsIterator as $conceptsCursor )
	{
		$conceptNameID = getEntryKeyFromAPCKey($conceptsCursor['key']);
		
		$conceptArr = $conceptsCursor['value'];

			$conceptLabelAR = $conceptArr['label_ar'];
			$conceptLabelEN = $conceptArr['label_en'];
			$conceptFrequency = $conceptArr['frequency'];
			$conceptWeight = $conceptArr['weight'];
			
			if ( $conceptFrequency< $minFreq) continue;
				
			if ( !isset($graphNodes[$conceptNameID]) )
			{
			
				if ( $lang == "EN")
				{
				
					$conceptNameClean = convertConceptIDtoGraphLabel($conceptLabelEN);
				
				}
				else
				{
						
					$conceptNameClean = convertConceptIDtoGraphLabel($conceptLabelAR);
				}
				
				
				$graphNodes[$conceptNameID]= array("id"=>$nodeSerialNumber++,"word"=>$conceptNameClean,
						"size"=>$conceptFrequency,"x"=>rand($startLocationXMin,$startLocationXMax),
						"y"=>rand($startLocationYMin,$startLocationYMax));
			}


	}
	
	$ONTOLOGY_RELATIONS = getModelEntryFromMemory("ALL", "MODEL_QA_ONTOLOGY", "RELATIONS", "");
	
		foreach($ONTOLOGY_RELATIONS as $index => $relArr)
		{

			$subject = $relArr['SUBJECT'];
			$verbAR = $relArr['VERB'];
			$verbEN = $relArr['VERB_TRANSLATION_EN'];
			$verbUthmani = $relArr['VERB_UTHMANI'];
			$relFreq = $relArr['FREQUENCY'];
			$object = $relArr['OBJECT'];
			
			if ( isset($graphNodes[$subject]) && isset($graphNodes[$object]) )
			{
				$graphLinks[]=array("source"=>$graphNodes[$subject]["id"],
								    "target"=>$graphNodes[$object]["id"],
									"link_frequency"=>$relFreq);
			}
			
		
				
		}

		
		$graphNodesArr = array();
		
		foreach($graphNodes as $word => $nodeArr)
		{
		
			$graphNodesArr[] = $nodeArr;
		
		}



	$graphObj["nodes"]=$graphNodesArr;
	$graphObj["links"]=$graphLinks;


	return $graphObj;
}




function ontologyToD3TreemapFlat($MODEL_QA_ONTOLOGY,$minFreq=0)
{
	global $lang;


	$treeRootObj = array();
	
	$treeRootObj["name"]="قرآن";
	$treeRootObj["children"]=array();

	
	$currentArr = &$treeRootObj["children"];

	/** SHOULD BE ZERO BASED FOR D3 TO WORK - o.target.weight = NULL**/
	$nodeSerialNumber = 0;


	$qaOntologyConceptsIterator = getAPCIterator("ALL\/MODEL_QA_ONTOLOGY\/CONCEPTS\/.*");

	foreach($qaOntologyConceptsIterator as $conceptsCursor )
	{
		$conceptNameID = getEntryKeyFromAPCKey($conceptsCursor['key']);
		
		$conceptArr = $conceptsCursor['value'];

		$conceptLabelAR = $conceptArr['label_ar'];
		$conceptLabelEN = $conceptArr['label_en'];
		$conceptFrequency = $conceptArr['frequency'];
		$conceptWeight = $conceptArr['weight'];
			
		if ( $conceptFrequency< $minFreq) continue;

	
		$conceptNameClean = convertConceptIDtoGraphLabel($conceptNameID);
			/*= array("id"=>$nodeSerialNumber++,"word"=>$conceptLabelAR,
					"size"=>$conceptWeight,"x"=>rand($startLocationXMin,$startLocationXMax),
					"y"=>rand($startLocationYMin,$startLocationYMax));*/
		$currentArr[] = array("name"=>$conceptNameClean,"size"=>$conceptWeight,"children"=>array());

		
		


	}

	$ONTOLOGY_RELATIONS = getModelEntryFromMemory("ALL", "MODEL_QA_ONTOLOGY", "RELATIONS", "");
	

	foreach($ONTOLOGY_RELATIONS as $index => $relArr)
	{

	
		$subject = $relArr['SUBJECT'];
		$verbAR = $relArr['VERB'];
		$verbEN = $relArr['VERB_TRANSLATION_EN'];
		$verbUthmani = $relArr['VERB_UTHMANI'];
		$relFreq = $relArr['FREQUENCY'];
		$object = $relArr['OBJECT'];
			
		
		//$treeRootObj[$subject]["children"][]["name"]=$object;
		
		
		$objectConceptArr = getModelEntryFromMemory("ALL", "MODEL_QA_ONTOLOGY", "CONCEPTS", $object);
		//$objectConceptArr = $MODEL_QA_ONTOLOGY['CONCEPTS'][$object];
		
			
		$index = search2DArrayForValue($currentArr,$subject);
		
		
		$isObjectIncludedBefore = search2DArrayForValue($currentArr[$index]["children"],$object);
		
		if ( $isObjectIncludedBefore===false)
		{
			//$currentArr[$index]["children"][] = array("name"=>$object,"size"=>$objectConceptArr['frequency'],"children"=>array());
		}

	}




	return $treeRootObj;
}


function getTreeNodeChildren($MODEL_QA_ONTOLOGY,$conceptNameID,$minFreq,$lang,$level,$alreadyInLevel1)
{
	global $thing_class_name_ar,$is_a_relation_name_ar;
	
	$childrenArr = array();
	
	if ( $level++ > 5 ) return;
	
	
	$relationsOfConceptAsSource = getModelEntryFromMemory("ALL", "MODEL_QA_ONTOLOGY", "GRAPH_INDEX_TARGETS", $conceptNameID);
	//$relationsOfConceptAsSource = $MODEL_QA_ONTOLOGY['GRAPH_INDEX_TARGETS'][$conceptNameID];
	
		


	foreach( $relationsOfConceptAsSource as $index => $relArr)
	{
	
		$verb  = $relArr["link_verb"];
		$subject = $relArr["source"];

		
		if ( $verb!=$is_a_relation_name_ar ) continue;
			
		//echoN("==".$subject);
		
		$conceptArr = getModelEntryFromMemory("ALL", "MODEL_QA_ONTOLOGY", "CONCEPTS", $subject);
		//$conceptArr = $MODEL_QA_ONTOLOGY['CONCEPTS'][$subject];
	
		$conceptLabelAR = $conceptArr['label_ar'];
		$conceptLabelEN = $conceptArr['label_en'];
		$conceptFrequency = $conceptArr['frequency'];
		$conceptWeight = $conceptArr['weight'];
	
	
		$type = $conceptArr['type'];
	
			
		if ( $conceptFrequency< $minFreq) continue;
	
	
		if ( $lang == "EN")
		{
		
			$conceptNameClean = convertConceptIDtoGraphLabel($conceptLabelEN);
		
		}
		else
		{
				
			$conceptNameClean = convertConceptIDtoGraphLabel($conceptLabelAR);
		}
		
	
		$children= array();
		
		if ( !isset($alreadyInLevel1[$subject]))
		{
			$children = getTreeNodeChildren($MODEL_QA_ONTOLOGY,$subject,$minFreq,$lang,$level,$alreadyInLevel1);
		}
		//else
		//{
		//	preprint_r($conceptArr);
		//	exit;
		//}


	
		
		$childrenArr[] = array("name"=>$conceptNameClean,"size"=>$conceptFrequency,
				"children"=>$children);
		
	
	
	
	}
	

	
	return $childrenArr;
}
function ontologyToD3TreemapHierarchical($MODEL_QA_ONTOLOGY,$minFreq=0,$lang)
{
	global $lang;

	$alreadyInLevel1 = array();
	

	$treeRootObj = array();

	
	$treeRootObj["name"]="قرآن";
	if ( $lang=="EN")
	{
		$treeRootObj["name"]="Quran";
	}
		
	$treeRootObj["children"]=array();



	/** SHOULD BE ZERO BASED FOR D3 TO WORK - o.target.weight = NULL**/
	$nodeSerialNumber = 0;

	
	


	$qaOntologyConceptsIterator = getAPCIterator("ALL\/MODEL_QA_ONTOLOGY\/CONCEPTS\/.*");
	
	foreach($qaOntologyConceptsIterator as $conceptsCursor )
	{
		$conceptNameID = getEntryKeyFromAPCKey($conceptsCursor['key']);
	
		$conceptArr = $conceptsCursor['value'];


		$conceptLabelAR = $conceptArr['label_ar'];
		$conceptLabelEN = $conceptArr['label_en'];
		$conceptFrequency = $conceptArr['frequency'];
		$conceptWeight = $conceptArr['weight'];
		
		
		
		
		$type = $conceptArr['type'];
	
			
		if ( $conceptFrequency< $minFreq) continue;

		
		//ONLY CLASSES (CLUSTERS)
		if ( $type=="class")
		{
			$alreadyInLevel1[$conceptNameID]=1;
			
			//echoN($conceptLabelAR);
			
			//echoN($conceptNameID);
			
			if ( $lang == "EN")
			{

				$conceptNameClean = convertConceptIDtoGraphLabel($conceptLabelEN);

			}
			else
			{
					
				$conceptNameClean = convertConceptIDtoGraphLabel($conceptLabelAR);
			}
			
			
				
			
			/*= array("id"=>$nodeSerialNumber++,"word"=>$conceptLabelAR,
			 "size"=>$conceptWeight,"x"=>rand($startLocationXMin,$startLocationXMax),
					"y"=>rand($startLocationYMin,$startLocationYMax));*/
			$treeRootObj["children"][] = array("name"=>$conceptNameClean,"size"=>$conceptFrequency,
					"children"=>getTreeNodeChildren($MODEL_QA_ONTOLOGY,$conceptNameID,$minFreq,$lang,1,$alreadyInLevel1));
		}

	}

	return $treeRootObj;
}


?>