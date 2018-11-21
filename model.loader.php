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
require_once(dirname(__FILE__)."/global.settings.php");
require_once(dirname(__FILE__)."/libs/core.lib.php");
require_once(dirname(__FILE__)."/libs/wordnet.lib.php");
require_once(dirname(__FILE__)."/libs/search.lib.php");


///////// ONTOLOGY 
require_once(dirname(__FILE__)."/libs/owllib/OWLLib.php");
require_once(dirname(__FILE__)."/libs/owllib/reader/OWLReader.php");
require_once(dirname(__FILE__)."/libs/owllib/memory/OWLMemoryOntology.php");

require_once(dirname(__FILE__)."/libs/ontology.lib.php");







$MODEL_CORE = array();
$MODEL_CORE['LOADED']=0;
$MODEL_CORE['SUPPORTED_LANGUAGES']=$supportedLanguages;

//$MODEL_SEARCH = array();
//$MODEL_QAC = array();
//$MODEL_QURANA = array();
//
//$MODEL_WORDNET = array();

/*

$MODEL_QA_ONTOLOGY = array();
$MODEL_QA_ONTOLOGY['CONCEPTS'] =  array();;
$MODEL_QA_ONTOLOGY['RELATIONS'] =  array();;
$MODEL_QA_ONTOLOGY['GRAPH_INDEX_SOURCES'] =  array();;
$MODEL_QA_ONTOLOGY['GRAPH_INDEX_TARGETS'] =  array();;
$MODEL_QA_ONTOLOGY['VERB_INDEX'] =  array();;
/*
/*
foreach ($MODEL_CORE['SUPPORTED_LANGUAGES'] as $supportedLang)
{
	$MODEL_CORE[$supportedLang] = array();
	//$MODEL_SEARCH[$supportedLang] = array();
}
*/

$META_DATA = array();
$META_DATA['SURAS']=array();

//
//$UTHMANI_TO_SIMPLE_WORD_MAP = array();
//$UTHMANI_TO_SIMPLE_LOCATION_MAP = array();
//
//$TRANSLATION_MAP_EN_TO_AR = array();
//$TRANSLATION_MAP_AR_TO_EN = array();
//$TRANSLITERATION_WORDS_MAP = array();
//$TRANSLITERATION_VERSES_MAP = array();
//$TRANSLITERATION_WORDS_LOCATION_MAP = array();
//$TRANSLITERATION_WORDS_INDEX = array();

function loadModels($modelsToBeLoaded,$lang)
{
	
	global $modelSources,$serializedModelFile,$quranMetaDataFile,$META_DATA,$MODEL_CORE,$MODEL_SEARCH,$MODEL_QAC,$MODEL_QURANA;
	global $UTHMANI_TO_SIMPLE_WORD_MAP, $numberOfSuras,$pauseMarksFile;
	global $TRANSLATION_MAP_EN_TO_AR,$TRANSLATION_MAP_AR_TO_EN,$TRANSLITERATION_WORDS_MAP,$TRANSLITERATION_VERSES_MAP;
	global $wordByWordTranslationFile,$transliterationFile;
	global $MODEL_WORDNET,$qaOntologyNamespace,$qaOntologyFile,$is_a_relation_name_ar,$is_a_relation_name_en;
	global $thing_class_name_ar,$thing_class_name_en;
	global $MODEL_QA_ONTOLOGY,$arabicStopWordsFileL2;
	global $TRANSLITERATION_WORDS_LOCATION_MAP,$TRANSLITERATION_WORDS_INDEX;
	

	//not working
	gc_enable();
	
	if (!function_exists("apc_exists"))
	{
		throw new Exception("APC not found!");
	}



	
	//echoN("MODEL EXISTS IN CACHE?:".apc_exists("EN/MODEL_CORE/TOTALS/"));
	
	##### CHECK MODEL IN CACHE ##### #####
	if ( TRUE && apc_exists("EN/MODEL_CORE/TOTALS/")!==false)
	{
		// split list by comma
		$modelListArr = preg_split("/,/",trim($modelsToBeLoaded));
		

		
		
		/**
		 * TODO: CHANGE THE CODE TO REFERENCE APC MEMORY DIRECTLY INSTEAD OF LOADING DATA IN EACH SCRIPT
		 */
		
		foreach( $modelListArr as $modelName)
		{
			//echoN("$modelName $lang ".time());
		//	echoN(memory_get_peak_usage());
			//echoN($modelName);
			
			
			if ( $modelName=="ontology")
			{
			
				
				/*$MODEL_QA_ONTOLOGY =  apc_fetch("MODEL_QA_ONTOLOGY");
				
				
				
				if ($MODEL_QA_ONTOLOGY===false )
				{
					echo "$MODEL_QA_ONTOLOGY NOT CACHED";exit;
				}
				*/
			}
			
			if ( $modelName=="wordnet")
			{
			
				
			}
				
			if ( ($modelName=="core"))
			{
				
			
				//$MODEL_CORE = json_decode((file_get_contents("$serializedModelFile.core")),TRUE);
				/*$MODEL_CORE  = apc_fetch("MODEL_CORE[$lang]");
				
				
				if ($MODEL_CORE===false )
				{
					echo "CORE MODEL [$lang] NOT CACHED";exit;
				}*/
				
				
			}
			else if ( ($modelName=="search"))
			{
				//$MODEL_SEARCH = json_decode((file_get_contents("$serializedModelFile.search")),TRUE);
				
				//$MODEL_SEARCH  = apc_fetch("MODEL_SEARCH[$lang]");
				
				
				/*if ($MODEL_SEARCH===false )
				{
					echo "SEARCH MODEL [$lang] NOT CACHED";exit;
				}*/
				
				
			}
			else if ( ($modelName=="qac"))
			{
				//$MODEL_QAC = json_decode((file_get_contents("$serializedModelFile.qac")),TRUE);
				
				/*$MODEL_QAC  = apc_fetch("MODEL_QAC");
				
				
				if ($MODEL_QAC===false )
				{
					echo "QAC MODEL NOT CACHED";exit;
				}
				*/
			}
		
		
		}
		
		$MODEL_WORDNET['INDEX']  = apc_fetch("WORDNET_INDEX");
		
		if ($MODEL_WORDNET['INDEX']===false )
		{
			echo "MODEL_WORDNET['INDEX'] NOT CACHED";exit;
		}
		
		
		$MODEL_WORDNET['LEXICO_SEMANTIC_CATEGORIES']= apc_fetch("WORDNET_LEXICO_SEMANTIC_CATEGORIES");
		
		if ($MODEL_WORDNET['LEXICO_SEMANTIC_CATEGORIES']===false )
		{
			echo " MODEL MODEL_WORDNET['LEXICO_SEMANTIC_CATEGORIES'] NOT CACHED";exit;
		}
		
		
		$MODEL_WORDNET['DATA'] = apc_fetch("WORDNET_DATA");
		
		if ($MODEL_WORDNET['DATA']===false )
		{
			echo "MODEL MODEL_WORDNET['DATA'] NOT CACHED";exit;
		}
		
		
		//else if ( ($modelName=="qurana"))
		//{
		//$MODEL_QURANA = json_decode((file_get_contents("$serializedModelFile.qurana")),TRUE);
		
		$MODEL_QURANA  = apc_fetch("MODEL_QURANA");
		
		
		if ($MODEL_QURANA===false )
		{
			echo "QURANA MODEL NOT CACHED";exit;
		}
		//}
		
		return;
	}
	########## ##### ##### ##### ##### #####

	//$quran = file($quranMetaDataFile);
	$quranMetaDataXMLObj = simplexml_load_file($quranMetaDataFile);
	

	###### CONVERT META XML STRUCUTURE TO OUR STRUCTURE
	foreach ($quranMetaDataXMLObj->suras as $index => $surasArr )
	{
	

		foreach ($surasArr->sura as $suraMetaArr )
		{
	
				
			$tempArr = array();
	
	
	
				$tempArr['index'] =  	(string)$suraMetaArr['index'];
				$tempArr['ayas'] =  	(string)$suraMetaArr['ayas'];
	
				$tempArr['name_ar'] =   (string)$suraMetaArr['name'];
				$tempArr['name_trans'] =  (string)$suraMetaArr['tname'];
				$tempArr['name_en'] =   (string)$suraMetaArr['ename'];
				$tempArr['type'] =  	(string)$suraMetaArr['type'];
				$tempArr['order'] = 	(string)$suraMetaArr['order'];
	
					
				$META_DATA['SURAS'][] = $tempArr;
		}
			
			
	}
	##############################################
	
	

	/////////// LOAD ONTOLOGY
	$reader = new OWLReader();
	$ontology = new OWLMemoryOntology();
	$thingClassName = "$thing_class_name_ar";
	$ontology->setNamespace($qaOntologyNamespace);
	
	$reader->readFromFile($qaOntologyFile, $ontology);
	
	
	
	//preprint_r($ontology->{'owl_data'}['classes']);
	
	//preprint_r($ontology->{'owl_data'}['properties']);
	//preprint_r($ontology->{'owl_data'}['labels']);
	
	//preprint_r($ontology->{'owl_data'}['annotations']);
	//preprint_r($ontology->{'owl_data'}['instances']);
	
	$classes = $ontology->{'owl_data'}['classes'];
	$instances = $ontology->{'owl_data'}['instances'];
	
	
	
	$qaOntologyConceptsArr = array();
	
	$qaOntologyRelationsArr = array();
	
	$relationsCount =0;
	
	foreach($classes as $className => $infoArr)
	{
		
		
		$className = stripOntologyNamespace($className);
		
		$qaOntologyConceptsArr[$className]=array("type"=>"class");
		
		
		//echoN($className);
		//preprint_r($infoArr);
		
		foreach($infoArr[0]['properties'] as $index => $propertiesArr)
		{
			
		
			/** INCASE THIS INSTANCE HAS MULTIPLE PROPERTIES WITH SAME VERB **/
			foreach($propertiesArr as $index2 => $onePropertyArr)
			{
				if ( empty($onePropertyArr)) continue;
		
				
				$verb = key($onePropertyArr);
				$objectClassArr = current($onePropertyArr);
					
				$objectConceptName = stripOntologyNamespace($objectClassArr[0]);
					
				//echoN("CLASS:***** $className => $verb -> $objectConceptName");
					
				$attributedArr = next($onePropertyArr);
					
				$freq = $attributedArr['frequency'];
				$engTranslation = $attributedArr['verb_translation_en'];
				$verbUthmani = $attributedArr['verb_uthmani'];
					
				$relHashID = buildRelationHashID($className,$verb,$objectConceptName);
				$qaOntologyRelationsArr[$relHashID]= array("SUBJECT"=>$className,"VERB"=>$verb,
						"OBJECT"=>$objectConceptName,"FREQUENCY"=>$freq,
						"VERB_TRANSLATION_EN"=>$engTranslation,"VERB_UTHMANI"=>$verbUthmani);
				//preprint_r($qaOntologyRelationsArr[$relHashID]);
				$relationsCount++;
		
			}
		
		
			
		}
	}
	

	
	
	foreach($instances as $instanceName => $intancesArr)
	{
		
		
		foreach($intancesArr as $index => $infoArr)
		{
			
		
		
			
			$subjectConceptName = stripOntologyNamespace($instanceName);
			
			$parent = stripOntologyNamespace($infoArr['class']);
			
			//echoN("$subjectConceptName $parent");
			
			$relHashID = buildRelationHashID($subjectConceptName,$is_a_relation_name_ar,$parent);
			
			$qaOntologyRelationsArr[$relHashID]= array("SUBJECT"=>$subjectConceptName,"VERB"=>"$is_a_relation_name_ar","OBJECT"=>$parent,"VERB_TRANSLATION_EN"=>"$is_a_relation_name_en");
			
			
			if ( $parent!=$thing_class_name_ar)
			{
				$relationsCount++;
			}
			
			$propertiesArr = $infoArr['properties'];
			//echoN($instanceName);
			
			//echoN("$instanceName:@@@");
			//preprint_r($propertiesArr);
		
			/** INCASE THIS INSTANCE HAS MULTIPLE PROPERTIES WITH SAME VERB **/
			foreach($propertiesArr as $index2 => $onePropertyArr)
			{
		
					if ( empty($onePropertyArr)) continue;
				
					$verb = key($onePropertyArr);
					$objectClassArr = current($onePropertyArr);
					
					$objectConceptName = stripOntologyNamespace($objectClassArr[0]);
					
					//echoN("***** $verb -> $objectConceptName");
					
					$attributedArr = next($onePropertyArr);
					
					$freq = $attributedArr['frequency'];
					$engTranslation = $attributedArr['verb_translation_en'];
					$verbUthmani = $attributedArr['verb_uthmani'];
					
					$relHashID = buildRelationHashID($subjectConceptName,$verb,$objectConceptName);
					$qaOntologyRelationsArr[$relHashID]= array("SUBJECT"=>$subjectConceptName,"VERB"=>$verb,
												"OBJECT"=>$objectConceptName,"FREQUENCY"=>$freq,
												"VERB_TRANSLATION_EN"=>$engTranslation,"VERB_UTHMANI"=>$verbUthmani);
					$relationsCount++;
				
			}

			
			// if it is class dont make it instance even if it is a subject (subclass of another class
			// BUG: animal was not apearing on ontology graph page since it was instance
			if ( empty($qaOntologyConceptsArr[$subjectConceptName]) 
					|| $qaOntologyConceptsArr[$subjectConceptName][type]!='class' )
			{
				$qaOntologyConceptsArr[$subjectConceptName]=array("type"=>"instance");
			}
	
		}
		
		
	}

	
	
	
	
	

	
	
	
	foreach($qaOntologyConceptsArr as $conceptName => $infoArr)
	{
		
	
		
		$fullConceptName = $qaOntologyNamespace.$conceptName;
		$labelsArr = $ontology->{'owl_data'}['labels'][$fullConceptName];
		
		foreach($labelsArr as $labelLang => $label)
		{
			/*if ( mb_strlen($label)==1)
			{
				echon($fullConceptName);
				preprint_r($ontology->{'owl_data'}['labels'][$fullConceptName]);
			}*/
			$qaOntologyConceptsArr[$conceptName]['label_'.strtolower($labelLang)] = $label;
		}
		
	
		
		// "Thing" does not have annotations
		if ( isset($ontology->{'owl_data'}['annotations'][$fullConceptName]))
		{
			$annotationsArr = $ontology->{'owl_data'}['annotations'][$fullConceptName];
			
			foreach($annotationsArr as $index => $annotArr)
			{
				$key = $annotArr['KEY'];
				$val = $annotArr['VAL'];
				
				$qaOntologyConceptsArr[$conceptName][$key] = $val;
				
				//echoN("[$conceptName][$key] = $val");
			}
			
		}
		
	}
	
	
	////////// OUTPUT STATS
	/*echoN("INSTANCES COUNT:".count($ontology->{'owl_data'}['instances']));
	echoN("CLASSES COUNT:".count($ontology->{'owl_data'}['classes']));
	echoN("PROPERTIES COUNT - DECLERATIONS ONLY:".count($ontology->{'owl_data'}['properties']));;
	echoN("CONCEPTS COUNT:".count($qaOntologyConceptsArr));
	echoN("RELATIONS COUNT:".$relationsCount);
	preprint_r($qaOntologyRelationsArr);*/
	//////////////////
	
	///////////// QUALITY CHECK CONCEPTS
	$qaOntologyConceptsArr2 = array();
	foreach($qaOntologyConceptsArr as $key => $val)
	{
		$newKey = strtr($key, "_", " ");
		
		$qaOntologyConceptsArr2[$newKey] = $value;
	}
	
	$ONTOLOGY_EXTRACTION_FOLDER = "../data/ontology/extraction/";
	$finalConcepts = unserialize(file_get_contents("$ONTOLOGY_EXTRACTION_FOLDER/temp.final.concepts.final"));
		
	$diffArr = array_diff(array_keys($qaOntologyConceptsArr2),array_keys($finalConcepts));

	$conceptsDiffCount = count($matchingTable);
	
	if ( $relationsDiffCount> 0)
	echoN("<b>### OWL-PROPRIETARY-CONCEPTS-DIFF-COUNT:</b>".$conceptsDiffCount);
	//preprint_r($diffArr);
	//////////////////////////////////////////////////////////////
	
	
	
	//////// quality check relations
	$relationsArr = unserialize(file_get_contents("$ONTOLOGY_EXTRACTION_FOLDER/temp.final.relations"));
	
	$matchingTable = array();

	foreach($qaOntologyRelationsArr as $index => $relArr)
	{
		$trippleStr =$relArr['SUBJECT']."->".$relArr['VERB']."->".$relArr['OBJECT'];
		
		//since Thing relations are not in the list we are comparing with
		if ( $relArr['OBJECT']==$thing_class_name_ar ) continue;
		//echoN($trippleStr);
	
		$trippleStr = trim($trippleStr);
		
		$matchingTable[$trippleStr]++;
	}
	
	
	foreach($relationsArr as $index => $relArr)
	{
		$relArr['SUBJECT'] = strtr($relArr['SUBJECT'], " ", "_");
		$relArr['VERB'] = strtr($relArr['VERB'], " ", "_");
		$relArr['OBJECT'] = strtr($relArr['OBJECT'], " ", "_");
		
		$trippleStr =$relArr['SUBJECT']."->".$relArr['VERB']."->".$relArr['OBJECT'];
		
		$trippleStr = trim($trippleStr);
	
		$matchingTable[$trippleStr]++;
	}
	

	
	function filterFunc($v)
	{
		return	$v <=1;
	}
	
	$matchingTable = array_filter($matchingTable, 'filterFunc');
	
	$relationsDiffCount = count($matchingTable);
	
	if ( $relationsDiffCount> 0)
	{
		echoN("<b>### OWL-PROPRIETARY-RELATIONS-DIFF-COUNT:</b>".$relationsDiffCount);
		preprint_r($matchingTable);
	}
	//////////////////////////////////////////////
	
	//echoN( join("<br>",array_keys($qaOntologyConceptsArr)));
	

	$qaOntologyVerbIndex = array();
	$qaOntologyGraphSourcesIndex = array();
	$qaOntologyGraphTargetsIndex = array();
	
	//preprint_r($qaOntologyRelationsArr);
	//exit;
	
	foreach($qaOntologyRelationsArr as $index => $relArr)
	{
		$subject  =$relArr['SUBJECT'];
		$verb = $relArr['VERB'];
		$verb_translation_en = $relArr['VERB_TRANSLATION_EN'];
		$object = $relArr['OBJECT'];
		
	
		
		
		//$qaOntologyVerbIndex[$verb][]=array("SUBJECT"=>$subject,"OBJECT"=>$object);
		//$qaOntologyVerbIndex[$verb_translation_en][]=array("SUBJECT"=>$subject,"OBJECT"=>$object);
		
		addValueToMemoryModel("ALL", "MODEL_QA_ONTOLOGY", "VERB_INDEX", $verb, array("SUBJECT"=>$subject,"OBJECT"=>$object));
		addValueToMemoryModel("ALL", "MODEL_QA_ONTOLOGY", "VERB_INDEX", $verb_translation_en, array("SUBJECT"=>$subject,"OBJECT"=>$object));
		
		//$qaOntologyGraphSourcesIndex[$subject][]=array("link_verb"=>$verb,"target"=>$object,"relation_index"=>$index);
		//$qaOntologyGraphTargetsIndex[$object][]=array("source"=>$subject,"link_verb"=>$verb,"relation_index"=>$index);
		
		
		addToMemoryModelList("ALL", "MODEL_QA_ONTOLOGY", "GRAPH_INDEX_SOURCES", $subject, array("link_verb"=>$verb,"target"=>$object,"relation_index"=>$index));
		addToMemoryModelList("ALL", "MODEL_QA_ONTOLOGY", "GRAPH_INDEX_TARGETS", $object, array("source"=>$subject,"link_verb"=>$verb,"relation_index"=>$index));
		
		
		
		
	}
	
	
	
	
	
	
	
	$qaOntologyConceptsENtoARMapArr = array();

	foreach($qaOntologyConceptsArr as $arName => $conceptArr)
	{
		$enLabel = trim(strtolower($conceptArr['label_en']));
	
		//$qaOntologyConceptsENtoARMapArr[$enLabel]=$arName;
		//$qaOntologyConceptsENtoARMapArr[$enLabel]=$arName;
		
		
		
		addValueToMemoryModel("ALL", "MODEL_QA_ONTOLOGY", "CONCEPTS_EN_AR_NAME_MAP", $enLabel, $arName);
		
		
		
	}
	
	$qaSynonymsIndex = array();
	
	foreach($qaOntologyConceptsArr as $arName => $conceptArr)
	{
		addValueToMemoryModel("ALL", "MODEL_QA_ONTOLOGY", "CONCEPTS", $arName, $conceptArr);
	
			
		$i=1;
		while(isset($conceptArr['synonym_'.$i]))
		{
			
			
			if (empty($conceptArr['synonym_'.$i])) 
			{
				$i++;
				continue;
			}
			
			$synonymLabel = trim(strtolower($conceptArr['synonym_'.$i]));
	
			$qaSynonymsIndex[$synonymLabel]=$arName;
			
			addValueToMemoryModel("ALL", "MODEL_QA_ONTOLOGY", "SYNONYMS_INDEX", $synonymLabel, $arName);
			
			$i++;

		}
	}
	
	
	//preprint_r($qaOntologyConceptsArr);exit;
	
	//$MODEL_QA_ONTOLOGY['CONCEPTS'] = $qaOntologyConceptsArr;
	
	
	
	//$MODEL_QA_ONTOLOGY['RELATIONS'] = $qaOntologyRelationsArr;
	
	
	addValueToMemoryModel("ALL", "MODEL_QA_ONTOLOGY", "RELATIONS", "", $qaOntologyRelationsArr);
	
	//$MODEL_QA_ONTOLOGY['GRAPH_INDEX_SOURCES'] = $qaOntologyGraphSourcesIndex;
	//$MODEL_QA_ONTOLOGY['GRAPH_INDEX_TARGETS'] = $qaOntologyGraphTargetsIndex;
	

	 //$MODEL_QA_ONTOLOGY['CONCEPTS_EN_AR_NAME_MAP'] = $qaOntologyConceptsENtoARMapArr;
	 //$MODEL_QA_ONTOLOGY['VERB_INDEX']  = $qaOntologyVerbIndex;
	
	//$MODEL_QA_ONTOLOGY['SYNONYMS_INDEX']  = $qaSynonymsIndex;
	
	//$res = apc_store("MODEL_QA_ONTOLOGY",$MODEL_QA_ONTOLOGY);
	
	//if ( $res===false){ throw new Exception("Can't cache MODEL_QA_ONTOLOGY"); }
	
	//preprint_r($MODEL_QA_ONTOLOGY);exit;
	//////// END ONTOLOGY LOADING
	

	
	
	
	////////////////////////////
	
	/// WORDNET
	loadWordnet($MODEL_WORDNET);
	

	/////////////
	

	
	//free resources
	$quranMetaDataXMLObj = null;
	unset($quranMetaDataXMLObj);
	
	foreach ($modelSources as $supportedLang => $modelSourceArr)
	{
		$type = $modelSourceArr['type'];
		$file = $modelSourceArr['file'];
		
		//echoN("$lang $type $file");
		
		loadModel($supportedLang,$type,$file);
		
		//not working
		$gced = gc_collect_cycles();
		//echoN($gced);
		
	}
	
	//echoN(json_encode($MODEL));
	
	
	############ Uthmani/Simple mapping table #################
	############ AND WORD-WORD TRANSLATION AND TRANSLITERATION #################
	
	$pauseMarksArr = getPauseMarksArrByFile($pauseMarksFile);
	
	$wordByWordFileArr = file($wordByWordTranslationFile,FILE_SKIP_EMPTY_LINES|FILE_IGNORE_NEW_LINES);
	
	$translitertationArr = file($transliterationFile,FILE_SKIP_EMPTY_LINES|FILE_IGNORE_NEW_LINES);


	
	$WORD_SENSES_EN = array();
	$WORD_SENSES_AR = array();
	
	$quranTextEntryFromAPC_AR = getModelEntryFromMemory("AR", "MODEL_CORE", "QURAN_TEXT", "");
	$quranTextEntryFromAPC_UTH = getModelEntryFromMemory("AR_UTH", "MODEL_CORE", "QURAN_TEXT", "");
	
	/* SURA'S LOOP **/
	for ($s=0;$s<$numberOfSuras;$s++)
	{
			
		 
		$suraSize = count($quranTextEntryFromAPC_AR[$s]);
			
		/* VERSES LOOP **/
		for ($a=0;$a<$suraSize;$a++)
		{
		  $i++;
		  $verseTextSimple = $quranTextEntryFromAPC_AR[$s][$a];
		  $simpleWordsArr = preg_split("/ /", $verseTextSimple);
		  $verseTextUthmani = $quranTextEntryFromAPC_UTH[$s][$a];
		  $uthmaniWordsArr = preg_split("/ /", $verseTextUthmani);
		  
		  

		  
		  $simpleWordsArr = removePauseMarksFromArr($pauseMarksArr,$simpleWordsArr);
		  
		  $uthmaniWordsArr = removePauseMarksFromArr($pauseMarksArr,$uthmaniWordsArr);
		  
		  $verseLocation = ($s+1).":".($a+1);
		  $UTHMANI_TO_SIMPLE_LOCATION_MAP[$verseLocation]=array();
		  
		  
		  ///////// Transliteration /////////////
		  $transliterationLine = current($translitertationArr);
		  next($translitertationArr);
		  $lineParts = preg_split("/\|/", $transliterationLine);
		  $verseTransliteration = $lineParts[2];
		  
		  
		  //echoN($transliterationLine);
		  
		  $TRANSLITERATION_VERSES_MAP[$verseLocation]=$verseTransliteration;
		  
		  $wordsTransliterationArr = preg_split("/ /", $verseTransliteration);
		 // preprint_r($wordsTransliterationArr);exit;
		  
		  /////////////////////////////////////////////////
		  
		  $wtwIndex =0;

		  foreach($uthmaniWordsArr as $index => $wordUthmani)
		  {
	
		  	  $qacMasterID = ($s+1).":".($a+1).":".($index+1);
		  		
		  	  $qacMasterTableEntry = getModelEntryFromMemory("AR","MODEL_QAC","QAC_MASTERTABLE",$qacMasterID);
		  		
		  		
		  	  $lemma = $qacMasterTableEntry[0]['FEATURES']['LEM'];
		  	
		  	  // to handle multi segment words such as الدنيا 
		  	  if ( empty($lemma))
		  	  {
		  	  	$lemma = $qacMasterTableEntry[1]['FEATURES']['LEM'];
		  	  }
		  	  //echoN("|$lemma|$wordUthmani");
		  	  
		  	  //$wtwIndex (INDEX_IN_AYA_EMLA2Y) needs to be 1 based  ( UTHMANI=IMLA2Y )
		  	  $UTHMANI_TO_SIMPLE_LOCATION_MAP[($s+1).":".($a+1)][($index+1)]=($wtwIndex+1);
		  	
		  	  $wordSimple = $simpleWordsArr[$wtwIndex++];
		  	  
		  	  //$UTHMANI_TO_SIMPLE_LOCATION_MAP[($s+1).":".($a+1)][($index+1)."-".$wordUthmani]=($wtwIndex)."-".$wordSimple;
		  	  
		  	  /* for ayas which are different in size, do the following
		  	   * if the current word is  ويا  or  ها or   يا
		  	   * then join it with the next word and make them one word
		  	   */ 
		  	  
		  	  if (count($uthmaniWordsArr) != count($simpleWordsArr) 
		  	  	&& ($wordSimple=="يا" || $wordSimple=="ها" ||$wordSimple =="ويا" || $wordUthmani=="وَأَلَّوِ") )
		  	  	{
		  	  		if ($wordUthmani=="يَبْنَؤُمَّ")
		  	  		{
		  	  			// example 0 => 1
		  	  			$UTHMANI_TO_SIMPLE_LOCATION_MAP[($s+1).":".($a+1)][($index+1)]=($wtwIndex+1);
		  	  			
		  	  			//[($index+1)."-".$wordUthmani]=($wtwIndex+1)."-".$wordSimple;
		  	  			$wordSimple = $wordSimple ." ".$simpleWordsArr[$wtwIndex++]." ".$simpleWordsArr[$wtwIndex++];
		  	  		}
		  	  		else
		  	  		{
			  	  		// example 0 => 1
			  	  		$UTHMANI_TO_SIMPLE_LOCATION_MAP[($s+1).":".($a+1)][($index+1)]=($wtwIndex+1);
			  	  		
			  	  		//[($index+1)."-".$wordUthmani]=($wtwIndex+1)."-".$wordSimple;
			  	  		$wordSimple = $wordSimple ." ".$simpleWordsArr[$wtwIndex++];
		  	  		}
		  	  		
		  	  		
		  	  		//echoN("$wordUthmani:$wordSimple");
		  	  		
		  	  	}
		  	  	
		  	  //	printHTMLPageHeader();
		  	  //	echoN("$wordSimple|$wordUthmani");
		  	  
		  	  	
		  	  ///////// english translation ////////
		  	  $wordByWordTranslationLine = current($wordByWordFileArr);
		  	  next($wordByWordFileArr);
		  
		  	  $linePartsArr = preg_split("/\|/",$wordByWordTranslationLine);
		  	  $englishTranslationForCurrentWord = $linePartsArr[5];
		  	  /////////////////////////////////////////////////
		  	  
		  	  
		  	
		  	  $WORD_SENSES_EN[$englishTranslationForCurrentWord][$wordUthmani]++;
		  	  $WORD_SENSES_AR[$wordUthmani][$englishTranslationForCurrentWord]++;
		  	
		  	  	
		  	  $TRANSLATION_MAP_EN_TO_AR[$englishTranslationForCurrentWord]=$wordUthmani;
		  	  $TRANSLATION_MAP_AR_TO_EN[$wordUthmani]=$englishTranslationForCurrentWord;
		  	  $TRANSLITERATION_WORDS_MAP[$wordUthmani]=$wordsTransliterationArr[$index];
		  	  
		  	  $clenaedTranliteration  = cleanTransliteratedText($wordsTransliterationArr[$index]);
		  	  $TRANSLITERATION_WORDS_INDEX[$clenaedTranliteration]=1;
		  	  $TRANSLITERATION_WORDS_LOCATION_MAP["$s:$a:$index"]=$wordsTransliterationArr[$index];
		  	  
		  	  
		  	  
		  	  
		  	  
		  	  //preprint_r($TRANSLITERATION_WORDS_LOCATION_MAP);
		  	 // preprint_r($TRANSLATION_MAP_AR_TO_EN);
		  	 // preprint_r($TRANSLITERATION_WORDS_MAP);
		  	  	
		     
		  	  	
			  $UTHMANI_TO_SIMPLE_WORD_MAP[$wordUthmani]=$wordSimple;
			  addValueToMemoryModel("AR", "OTHERS", "UTHMANI_TO_SIMPLE_WORD_MAP", $wordUthmani, $wordSimple);
			  $UTHMANI_TO_SIMPLE_WORD_MAP[$wordSimple]=$wordUthmani;
			  addValueToMemoryModel("AR", "OTHERS", "UTHMANI_TO_SIMPLE_WORD_MAP", $wordSimple, $wordUthmani);
			  
			  if (!empty($lemma))
			  {
			  	if (!isset($LEMMA_TO_SIMPLE_WORD_MAP[$lemma]))
			  	{
			 	 	$LEMMA_TO_SIMPLE_WORD_MAP[$lemma]=$wordSimple;
			  	}
			  	else
			  	{
			  		$oldSimple = $LEMMA_TO_SIMPLE_WORD_MAP[$lemma];
			  		
			  		if ( myLevensteinEditDistance($oldSimple, $lemma) >  myLevensteinEditDistance($wordSimple, $lemma) )
			  		{
			  			$LEMMA_TO_SIMPLE_WORD_MAP[$lemma]=$wordSimple;
			  		}
			  		
			  	}
			  }
			  
		
			  
			  
		  }
		  

		  
		  
		}
	}
	
	
	/////// ADD UTHMANI TO SIMPLE LOCATION MAP TO MEMORY
	foreach($UTHMANI_TO_SIMPLE_LOCATION_MAP as $verseLocation => $verseMappingArr)
	{
		/*foreach($mappingArr as $uhtmaniIndex=>$imal2yIndex)
		{
			
		}*/
		
		addValueToMemoryModel("AR", "OTHERS", "UTHMANI_TO_SIMPLE_LOCATION_MAP", $verseLocation, $verseMappingArr);
		
	}
	///////////////////////////////////////////////////////
	  
	//preprint_r($TRANSLATION_MAP_EN_TO_AR);exit;
	//preprint_r($WORD_SENSES_AR);exit;
	

	
	
	// CAN'T BE ADDED IN THE CORE_MODEL since the mapping happens after loadModel
	//$res = apc_store("UTHMANI_TO_SIMPLE_WORD_MAP",$UTHMANI_TO_SIMPLE_WORD_MAP);
	
	//if ( $res===false){ throw new Exception("Can't cache UTHMANI_TO_SIMPLE_WORD_MAP"); }

	//$res = apc_store("UTHMANI_TO_SIMPLE_LOCATION_MAP",$UTHMANI_TO_SIMPLE_LOCATION_MAP);
	
	//if ( $res===false){ throw new Exception("Can't cache UTHMANI_TO_SIMPLE_LOCATION_MAP"); }
	
	$res = apc_store("LEMMA_TO_SIMPLE_WORD_MAP",$LEMMA_TO_SIMPLE_WORD_MAP);
	
	if ( $res===false){ throw new Exception("Can't cache LEMMA_TO_SIMPLE_WORD_MAP"); }

	$res = apc_store("WORDS_TRANSLATIONS_EN_AR",$TRANSLATION_MAP_EN_TO_AR);
	
	if ( $res===false){ throw new Exception("Can't cache WORDS_TRANSLATIONS_EN_AR"); }
	
	$res = apc_store("WORDS_TRANSLATIONS_AR_EN",$TRANSLATION_MAP_AR_TO_EN);
	
	if ( $res===false){ throw new Exception("Can't cache WORDS_TRANSLATIONS_AR_EN"); }
	
	$res = apc_store("WORDS_TRANSLITERATION",$TRANSLITERATION_WORDS_MAP);
	
	if ( $res===false){ throw new Exception("Can't cache WORDS_TRANSLITERATION"); }
	
	$res = apc_store("TRANSLITERATION_WORDS_LOCATION_MAP",$TRANSLITERATION_WORDS_LOCATION_MAP);
	
	if ( $res===false){ throw new Exception("Can't cache TRANSLITERATION_WORDS_LOCATION_MAP"); }
	
	$res = apc_store("TRANSLITERATION_VERSES_MAP",$TRANSLITERATION_VERSES_MAP);
	
	if ( $res===false){ throw new Exception("Can't cache TRANSLITERATION_VERSES_MAP"); }
		
	$res = apc_store("TRANSLITERATION_WORDS_INDEX",$TRANSLITERATION_WORDS_INDEX);
	
	if ( $res===false){ throw new Exception("Can't cache TRANSLITERATION_WORDS_INDEX"); }
	
	
	
	

	$res = apc_store("WORD_SENSES_EN",$WORD_SENSES_EN);
	
	if ( $res===false){ throw new Exception("Can't cache WORD_SENSES_EN"); }
	
	$res = apc_store("WORD_SENSES_AR",$WORD_SENSES_AR);
	
	if ( $res===false){ throw new Exception("Can't cache $WORD_SENSES_AR"); }
	
	
	//// ENRICH INVERTED INDEX BY UTHMANI-EMLA2Y INDEXES
	//echoN(count($MODEL_SEARCH['AR']['INVERTED_INDEX']));
	

	
	foreach(getAPCIterator("AR\/MODEL_SEARCH\/INVERTED_INDEX\/.*") as $invertedIndexCursor )
	{
		

		
		 $wordDataArr = $invertedIndexCursor['value'];
		 $key = $invertedIndexCursor['key'];
		 $word = getEntryKeyFromAPCKey($key);
		 
	
		 
		foreach($wordDataArr as $index => $documentArrInIndex)
		{
			$WORD_TYPE = $documentArrInIndex['WORD_TYPE'];
			$SURA = $documentArrInIndex['SURA'];
			$AYA = $documentArrInIndex['AYA'];
			
			//echoN($word." ".$WORD_TYPE);
			
			if ($WORD_TYPE=="NORMAL_WORD")
			{
				$INDEX_IN_AYA_EMLA2Y = $documentArrInIndex['INDEX_IN_AYA_EMLA2Y'];
				
				foreach($UTHMANI_TO_SIMPLE_LOCATION_MAP[($SURA+1).":".($AYA+1)] as $uhtmaniIndex=>$imal2yIndex)
				{
					if ( $imal2yIndex==$INDEX_IN_AYA_EMLA2Y)
					{
						$INDEX_IN_AYA_UTHMANI = $uhtmaniIndex;
						break;
					}
				}
				
				//echoN($INDEX_IN_AYA_UTHMANI);
				
				$wordDataArr[$index]['INDEX_IN_AYA_UTHMANI']=$INDEX_IN_AYA_UTHMANI;
			}
			else
			{
				// needed for highlighting pronoun charcters in search
				$INDEX_IN_AYA_UTHMANI = $documentArrInIndex['INDEX_IN_AYA_UTHMANI'];
					
				$INDEX_IN_AYA_EMLA2Y = getSimpleWordIndexByUthmaniWordIndex(($SURA+1).":".($AYA+1), $INDEX_IN_AYA_UTHMANI);
					
				$wordDataArr[$index]['INDEX_IN_AYA_EMLA2Y']=$INDEX_IN_AYA_EMLA2Y;
			}
		}
		
	
		
		//UPDATE
		updateModelData($key,$wordDataArr);
	}
	
	//$res = apc_store("MODEL_SEARCH[AR]",$MODEL_SEARCH['AR']);
	
	//if ( $res===false){ throw new Exception("Can't cache MODEL_SEARCH[AR]"); }
	
	//preprint_r($TRANSLITERATION_WORDS_LOCATION_MAP);

	/// ADD TRANSLITERATION TO INVERETD INDEX WWITH ENGLISH WORDS
	if ($lang=="EN")
	{
		 
		$invertedIndexBatchApcArr = array();
	
		foreach($TRANSLITERATION_WORDS_LOCATION_MAP as $location => $transliteratedWord )
		{
			$locationArr = explode(":",$location);
			 
			$s = $locationArr[0];
			$a = $locationArr[1];
			$wordIndex = $locationArr[2];
			 
			//echoN("$transliteratedWord,$s,$a,$wordIndex");
	
			
			$transliteratedWord = strtolower(strip_tags($transliteratedWord));
			
			//$MODEL_SEARCH['EN']['INVERTED_INDEX'][$word]
			
			addToInvertedIndex($invertedIndexBatchApcArr,$lang,$transliteratedWord,$s,$a,$wordIndex,"NORMAL_WORD");
			
		
		}
		
		addToMemoryModelBatch($invertedIndexBatchApcArr);
		
		//$res = apc_store("MODEL_SEARCH[EN]",$MODEL_SEARCH['EN']);
	
	}
	
	
	
	//if ( $res===false){ throw new Exception("Can't cache MODEL_SEARCH[EN]"); }
	

	/////////////////////////////////////////////////////////
	
	//preprint_r($UTHMANI_TO_SIMPLE_WORD_MAP);
	//preprint_r($MODEL_CORE["AR_UTH"]['QURAN_TEXT']);exit;

	
	##############################################################
	
	// get memory usage
	$debug =  ((memory_get_usage(true)/1024)/1024)."/".((memory_get_peak_usage(true)/1024)/1024)."Memory <br>";
	//echoN($debug);


	
	
	//needed to be set here after both languages has been loaded
	
	
	// reload all models from memory to set all variables (WORDNET) - after model generation 
	/* needed to reload all generated models from memory specialy model_core since 
	 * it has 3 languages, if this line is removed: all 3 langauges are loaded although only one language 
	 * is requested, also it caused a bug in getPoSTaggedSubsentences
	 */

	//loadModels($modelsToBeLoaded,$lang);
	
	
	
}

function loadModel($lang,$type,$file)
{
		global $WORDS_FREQUENCY_ARR,$TOTALS_ARR,$MODEL_CORE,$MODEL_SEARCH,$MODEL_QAC,$MODEL_QURANA;
		global $sajdahMark,$saktaLatifaMark,$pauseMarksFile,$serializedModelFile,$basmalaTextUthmani;
		global $numberOfSuras,$numberOfVerses,$quranMetaDataFile,$arabicStopWordsFile,$englishStopWordsFile;
		global $META_DATA,$basmalaText,$englishResourceFile,$arabicResourceFile,$quranCorpusMorphologyFile;
		global $quranaPronounResolutionConceptsFile,$quranaPronounResolutionDataFileTemplate,$quranFileUthmaniAR;
		global $TRANSLATION_MAP_EN_TO_AR,$TRANSLATION_MAP_AR_TO_EN,$TRANSLITERATION_WORDS_MAP,$TRANSLITERATION_VERSES_MAP;
		global $basmalaTextUthmani2,$arabicStopWordsFileL2;
		global $TRANSLITERATION_WORDS_LOCATION_MAP;
				
		$QURAN_TEXT = array();

	
		$invertedIndexBatchApcArr = array();
		$qacMasterTableBatchApcArr = array();
		$qacPOSTableBatchApcArr = array();
		$qacFeatureTableBatchApcArr = array();
		
		
		$TOTALS_ARR = array();
		$TOTALS_ARR['CHARS']=0;
		$TOTALS_ARR['WORDS']=0;
		$TOTALS_ARR['NRWORDS'] = 0;
		$TOTALS_ARR['VERSES']=0;
		$TOTALS_ARR['SURAS']=$numberOfSuras;
		$TOTALS_ARR['CHAPTERS']=30;
		$TOTALS_ARR['TOTAL_PER_SURA'] = array();
		$TOTALS_ARR['SAJDAT_TELAWA'] = array();
		$TOTALS_ARR['PAUSEMARKS'] = array();
		$TOTALS_ARR['MIN_WORD_LENGTH']=0;
		$TOTALS_ARR['AVG_WORD_LENGTH']=0;
		$TOTALS_ARR['MAX_WORD_LENGTH']=0;
		$TOTALS_ARR['MIN_WORD']=null;
		$TOTALS_ARR['MAX_WORD']=null;
		
		$TOTALS_ARR['MIN_VERSE_LENGTH']=0;
		$TOTALS_ARR['AVG_VERSE_LENGTH']=0;
		$TOTALS_ARR['MAX_VERSE_LENGTH']=0;
		$TOTALS_ARR['MIN_VERSE']=null;
		$TOTALS_ARR['MAX_VERSE']=null;
		
		$TOTALS_ARR['SAJDAT_TELAWA']['COUNT']=0;
		$TOTALS_ARR['SAJDAT_TELAWA']['VERSES']=array();
		
		$TOTALS_ARR['SAKTA_LATIFA']['COUNT']=0;
		$TOTALS_ARR['SAKTA_LATIFA']['VERSES']=array();
		
		$INVERTED_INDEX = array();
		
		$WORDS_FREQUENCY_ARR = array();
		$WORDS_FREQUENCY_ARR['WORDS'] = array();
		$WORDS_FREQUENCY_ARR['WORDS_PER_SURA'] = array();
		$WORDS_FREQUENCY_ARR['TOTAL_PER_VERSE']= array();
		$WORDS_FREQUENCY_ARR['WORDS_TFIDF'] = array();
		$WORDS_FREQUENCY_ARR['VERSE_ENDINGS'] = array();
		$WORDS_FREQUENCY_ARR['VERSE_BEGINNINGS'] = array();
		
		/** WORD LENGTH **/
		$minWordLength = 1000;
		$minWord = null;
		$maxWordLength = -1;
		$maxWord = null;
		$avgWordLength = 0;
		
		
		/** VERSE LENGTH **/
		$minVerseLength = 1000;
		$minVerse = null;
		$maxVerseLength = -1;
		$maxVerse = null;
		$avgVerseLength = 0;
	
		
		
		/** QAC Model **/
		// Master model, contains all QAC data
		$qacMasterSegmentTable = array();
		
		//pinters/indexes on the master table for POS and features
		$qacPOSTable = array();
		$qacFeaturesTable = array();
		//$qacWordsTable = array();
		$qacSegmentToWordTable = array();
	
		
		/** QURANA Corpus **/
		$quranaConcecpts = array();
		$quranaResolvedPronouns = array();
		
		
		########### LOAD DATA ACCORDING TO MODEL SOURCE TYPE
		if ( $type=="XML")
		{
			$sourceContent = simplexml_load_file($file);
		}
		else
		{
			$sourceContent = file($file,FILE_SKIP_EMPTY_LINES  | FILE_IGNORE_NEW_LINES);

		}
		
	

		if ( $type=="TXT")
		{
			for ($s=0;$s<$numberOfVerses;$s++ )
			{
				$line = $sourceContent[$s];
				
				
				$lineArr = preg_split("/\|/", $line);
				
				$suraIndex = $lineArr[0];
				$ayaIndex = $lineArr[1];
				$text = $lineArr[2];
				
				//strip "besm allah alrahman al raheem" from furst aya of all suras except the first one
				if ( strpos($lang,"AR")!==false && $ayaIndex==1 && $s!=0)
				{
					if ( $lang=="AR" )
					{
						$text = trim(str_replace($basmalaText,"", $text));
					}
					else if ( $lang=="AR_UTH")
					{
						$text = trim(str_replace($basmalaTextUthmani,"", $text));
						$text = trim(str_replace($basmalaTextUthmani2,"", $text));
						
					}
				}
				
				if (!isset($QURAN_TEXT[$suraIndex-1])) $QURAN_TEXT[$suraIndex-1] = array();
				$QURAN_TEXT[$suraIndex-1][$ayaIndex-1] = $text;
				
			
				
				
			}
		}
		else if ( $type=="XML")
		{
		
			for ($s=0; $s<$numberOfSuras;$s++ )
			{
	
				$suraSize = $META_DATA['SURAS'][$s]['ayas'];
		
				for ($a=0;$a<$suraSize;$a++)
				{
					$QURAN_TEXT[$s][$a] = (string)$sourceContent->sura[$s]->aya[$a]['text'];
					
				}
			}
		}
		else
		{
			throw new Exception("Invalid Source Type ($type)");
		}
		
		
		
		
		##############################################################
		
		
		// free resources
		$sourceContent = null;
		unset($sourceContent);
		
		
		if ( $lang=="AR")
		{
		
			############ LOAD QAC (Quranic Arabic Corpus) FILE ###################################
			
			//dont skip new lines here (FILE_SKIP_EMPTY_LINES) for the skipping "57" condition below to work
			$qacFileLinesArr = file($quranCorpusMorphologyFile,FILE_IGNORE_NEW_LINES);
			
			$rootsLookupArray = array();
			
			$headerIndex=0;
			$segmentIndex=1;
			foreach ($qacFileLinesArr as $line)
			{
				$headerIndex++;
				
				//ignore header sections
				if ( $headerIndex <= 57) continue;
				
				//if ( $segmentIndex >= 2) exit;
				
				
				//echoN($line);
				
				// convert columns to array
				$lineArr = preg_split("/\t/",$line);
				
				$location = $lineArr[0];
				$formOrSegment = $lineArr[1];
				$posTAG = $lineArr[2];
				$featuresList = $lineArr[3];
				
				//preprint_r($lineArr);
				
				// remove brackets from location and keep it only SURA/AYA/WORDINDEX/SEGMENTINDEX
				$masterID = preg_replace("/\(|\)|/", "", $location);
				
				$locationArr = preg_split("/\:/", $masterID);
				
				
				
				$wordSegmentID = $locationArr[count($locationArr)-1];
				
				$wordIndex =  $locationArr[count($locationArr)-2];
				
				$verseID =  $locationArr[count($locationArr)-3];
				
				$suraID =  $locationArr[count($locationArr)-4];
				
				// Remove segment index from location ( will be added as new array below )
				$masterID = substr($masterID,0,strlen($masterID)-2);
				
				// get the reversed buackwalter transliteration for the segment
				$formOrSegmentReverseTransliterated = buckwalterReverseTransliteration($formOrSegment);
				
				//echoN($formOrSegmentReverseTransliterated);
				
				// separate features
				$featuresTempArr = preg_split("/\|/", $featuresList);
				
				//preprint_r($featuresTempArr);
				
				$featuresArr = array();
				foreach($featuresTempArr as $oneFeature)
				{
			
					
					// feature is a key/value set
					if ( strpos($oneFeature,":")!==false )
					{
						$oneFeatureKeyValueArr = preg_split("/\:/",$oneFeature);
						$featureName   = $oneFeatureKeyValueArr[0];
						$featureValue  = $oneFeatureKeyValueArr[1];
						
						if ( $featureName=="LEM" || $featureName=="ROOT")
						{
							//echoN($featureValue);
							$featureValue = buckwalterReverseTransliteration($featureValue);
						}
									
					}
					else
					{
						$featureName   = $oneFeature;
						// 1 here just a dummy value
						$featureValue  = -1;
					
					}
					
					$featureValue = trim($featureValue);
					// fill Features Index table
					//$qacFeaturesTable[$featureName][$masterID]= $featureValue;
					
					$apcMemoryEntryKey = "$lang/MODEL_QAC/QAC_FEATURES/$featureName";
					
					$qacFeatureTableBatchApcArr[$apcMemoryEntryKey][$masterID]=$featureValue;
					
					
					$featuresArr[$featureName] = $featureValue;
					
					// non-word features should not be included
					if ( $featureName=="LEM" || $featureName=="ROOT")
					{
						addToInvertedIndex($invertedIndexBatchApcArr,$lang,trim($featureValue),($suraID-1),($verseID-1),$wordIndex,trim($featureName),$formOrSegmentReverseTransliterated);
						
					
						
						if ( $featureName=="ROOT")
						{
							//$rootsLookupArray[$formOrSegmentReverseTransliterated]=$featureValue;
							
							
							addValueToMemoryModel($lang, "MODEL_QAC", "QAC_ROOTS_LOOKUP", $formOrSegmentReverseTransliterated, $featureValue);
						}
					}
					
					
					
				}
				
				
				//location significant before increment below
				$qacSegmentToWordTable[$segmentIndex] = $wordIndex;
				
				// Fill master table
				//$qacMasterSegmentTable[$masterID][]
				$qacMasterTableEntry = array("FORM_EN"=>$formOrSegment,
															"FORM_AR"=>$formOrSegmentReverseTransliterated,
													  		"TAG"=>$posTAG,
															"SEGMENT_INDEX"=>$segmentIndex++,
															"FEATURES"=>$featuresArr);
				
				$apcMemoryEntryKey = "$lang/MODEL_QAC/QAC_MASTERTABLE/$masterID";
				
				$qacMasterTableBatchApcArr[$apcMemoryEntryKey][]=$qacMasterTableEntry;
				
				// Fill Part of Speech tagging table 
				$qacPOSTable[$posTAG][$masterID]=$wordSegmentID;
				
				$apcMemoryEntryKey = "$lang/MODEL_QAC/QAC_POS/$posTAG";
				
				$qacPOSTableBatchApcArr[$apcMemoryEntryKey][$masterID]=$wordSegmentID;
				
				
				

			}
		
		
			
			//preprint_r($qacMasterSegmentTable);
				
			//preprint_r($qacFeaturesTable);
				
			//preprint_r($qacPOSTable);
		
			
			##############################################################
	
			// free resources
			$qacFileLinesArr = null;
			unset($qacFileLinesArr);
			
			
			// need to fluch tabel in memory since it is needed by Qurana - in segment function
			addToMemoryModelBatch($qacMasterTableBatchApcArr);
			
			
		}
		
			######### Qurana Pronomial Anaphone Corpus ###################	
			
			
			//echoN($quranaPronounResolutionConceptsFile);
	
			// GET XML FILE CONTENT
			$xmlContent = file_get_contents($quranaPronounResolutionConceptsFile);
			
		
			// LOAD XML OBJECT - trim used to avoid first line empty error
			$concepts = simplexml_load_string( trim(stripHTMLComments($xmlContent)) );
			
			// LOAD CONCEPTS
			foreach($concepts->con as $index=>$conceptObj)
			{
				$conceptID = (string)$conceptObj['id'];
				$conceptNameEN = (string)$conceptObj->english;
				$conceptNameAR = (string)$conceptObj->arabic;
				
				$quranaConcecpts[$conceptID]= array("EN"=>trim($conceptNameEN),"AR"=>trim($conceptNameAR),"FREQ"=>0);
				
			}
			
			
			$pronounsCount =0;
			$segmentsCount =0;
			//preprint_r($quranaConcecpts);
			
			// LOAD PRONOUNS // load & parse the file of each SURA and load it in the model
			for ($s=0;$s<$numberOfSuras;$s++)
			{
				
				$suraID = $s+1;
				
				$pronounDataFileName = preg_replace("/%s/", ($suraID), $quranaPronounResolutionDataFileTemplate);
				
				//echoN($pronounDataFileName);
				
				// GET XML FILE CONTENT of the current SURA by customizing file name
				$xmlContent = file_get_contents($pronounDataFileName);
				
		
				
				
				// LOAD XML OBJECT - trim used to avoid first line empty error
				$chapter = simplexml_load_string( trim(stripHTMLComments($xmlContent)) );
				
				
			
				// LOAD CONCEPTS
				foreach($chapter->verse as $index=>$verseObj)
				{
					$verseLocalSegmentIndex=0;
					$versesCount++;
					
					// Loop on all children
					foreach($verseObj->children() as $index=>$childObj)
					{
					
						// get tag name
						$tagName = $childObj->getName();
						
						$verseLocalSegmentIndex++;
						
						$segmentsCount++;
						
						// we got a prounoun tag
						if ( $tagName=="pron")
						{
							$pronounsCount++;
							
							// get the verse including this pronoun
							$verseID = (string)$verseObj['id'];
							
							// get pronoun concept ID and antecendent
							$conceptID = (string)$childObj['con'];
							$pronounAntecedent = (string)$childObj['ant'];
							
							// get segment ID and word form
							$quranaSegmentID = (string)$childObj->seg['id'];
							$quranaSegmentForm  = (string)$childObj->seg->__toString();
			
							
							$quranaSegmentForm = trim($quranaSegmentForm);
							
							// convert Qurana Segment ID to QAC segment for cross referenceing 
							$qacSegment = getQACSegmentByQuranaSeqment($suraID,$verseID,$verseLocalSegmentIndex,$quranaSegmentForm);
							
						
							//echo("$qacSegment,$quranaSegmentID\n");
					
							
							// get the id of the word where the segment is
							$wordId = $qacSegmentToWordTable[$qacSegment];
							
					
							
							
							$quranaConcecpts[$conceptID]["FREQ"]++;
							
							// fill pronouns array
							$quranaResolvedPronouns["$suraID:$verseID:$wordId"][]= array("CONCEPT_ID"=>$conceptID,
																	   "SEGMENT_INDEX"=>$qacSegment,
																		"ANTECEDENT_SEGMENTS"=>preg_split("/ /", $pronounAntecedent));
							
							
							if ( $lang=="EN")
							{
								addToInvertedIndex($invertedIndexBatchApcArr,$lang,strtolower($quranaConcecpts[$conceptID]['EN']),($suraID-1),($verseID-1),$wordId,"PRONOUN_ANTECEDENT",$quranaSegmentForm);
								
								
								
							}
							else
							{
								addToInvertedIndex($invertedIndexBatchApcArr,$lang,$quranaConcecpts[$conceptID]['AR'],($suraID-1),($verseID-1),$wordId,"PRONOUN_ANTECEDENT",$quranaSegmentForm);
							}
							 
							
						
							
						}
					}
					
					
					
				}
				
			}
			
			//echoN("SEG:$segmentsCount PRON:$pronounsCount");
			//preprint_r($quranaResolvedPronouns);
		
	
			
	
			//preprint_r($INVERTED_INDEX);exit;
			
			##############################################################
			
			
			// free resources
			$xmlContent = null;
			$concepts = null;
			unset($xmlContent);
			unset($concepts);		
			
			//echo preprint_r($QURAN_TEXT);;
			
		
		
		

		
	  	if ( strpos($lang,"AR")!==false)
	  	{
			$stopWordsArr = getStopWordsArrByFile($arabicStopWordsFile);
			$stopWordsStrictL2Arr = getStopWordsArrByFile($arabicStopWordsFileL2);
			$pauseMarksArr = getPauseMarksArrByFile($pauseMarksFile);
	  	}
	  	else
	  	{
	  		$stopWordsArr = getStopWordsArrByFile($englishStopWordsFile);
	  		$pauseMarksArr = array();
	  	}

	  	//preprint_r($stopWordsArr);
	  	//preprint_r($pauseMarksArr);
				  		
				  		
						if ( strpos($lang,"AR")!==false)
						{
					  		// SETTING PAUSE MARKS COUNTER ARRAY
					  		foreach($pauseMarksArr as $pauseMark => $constant)
					  		{
					  			$TOTALS_ARR['PAUSEMARKS'][$pauseMark]=0;
					  	
					  		}
						}
				  		
				  		/* SURA'S LOOP **/
				  		for ($s=0;$s<$numberOfSuras;$s++)
				  		{
				  			$TOTALS_ARR['TOTAL_PER_SURA'][$s] = array();
				  			
				  			$suraNameLang = ($lang);
				  			
				  			if ( $suraNameLang=="AR_UTH")
				  			{
				  				$suraNameLang = "AR";
				  			}
				  			
				  			$suraNameLang = strtolower($lang);
				  			
				  			$TOTALS_ARR['TOTAL_PER_SURA'][$s]['NAME']=$META_DATA['SURAS'][$s]['name_'.$suraNameLang];
				  			$TOTALS_ARR['TOTAL_PER_SURA'][$s]['CHARS']=0;
				  			$TOTALS_ARR['TOTAL_PER_SURA'][$s]['NRWORDS']=0;
				  			$TOTALS_ARR['TOTAL_PER_SURA'][$s]['WORDS']=0;
				  			$TOTALS_ARR['TOTAL_PER_SURA'][$s]['VERSES']=0;
				  			
				  			
				  			
				  			
				  			$WORDS_FREQUENCY_ARR['WORDS_PER_SURA'][$s] = array();
				  			
				  			
				  			
				  			
				  		}
			
				  		/* SURA'S LOOP **/
				  		for ($s=0;$s<$numberOfSuras;$s++)
				  		{
				  			//echoN($quranXMLObj->sura[$s]['name']);
				  			
				  			$suraSize = $META_DATA['SURAS'][$s]['ayas'];
				  			
				  		
				  		
				  			/* VERSES LOOP **/
					  		for ($a=0;$a<$suraSize;$a++)
					  		{
					  			//$verseText = 
					  			$verseText = $QURAN_TEXT[$s][$a];
					  			
					  			
					  		
					  		
					  			
					  			//echoN("- ".$verseText);
					  			
					  			$wordsArr = preg_split("/ /", $verseText);
					  			
					  			
					  			
					  			
					  			/** CALCULATE VERSE LENGTH **/
					  			$wordsInVerseIncludingPauses = count($wordsArr);
					  			
					  			$wordsInVerse = $wordsInVerseIncludingPauses - count(array_intersect(($wordsArr),array_keys($pauseMarksArr)));
					  			
					  			if ( $wordsInVerse >= $maxVerseLength)
					  			{
					  				$maxVerseLength = $wordsInVerse;
					  				$maxVerse = $verseText;
					  	
					  				
					  			}
					  			
					  			if ( $wordsInVerse <= $minWordLength)
					  			{
					  				if ($wordsInVerse == $minWordLength)
					  				{
					  					if (  mb_strlen($verseText) < mb_strlen($minVerse) )
					  					{
					  						$minVerseLength = $wordsInVerse;
					  						$minVerse = $verseText;
					  					}
					  				}
					  				else 
					  				{
						  				$minVerseLength = $wordsInVerse;
						  				$minVerse = $verseText;
					  				}
					  			}
					  			
					  			$avgVerseLength+=$wordsInVerse;
					  			/** END CALCULATE VERSE LENGTH **/
					  			
					  			
					  			$wordIndex = 0;
					  			/* WORDS IN VERSE  LOOP **/
					  			foreach ($wordsArr as $word)
					  			{
					  				$word = trim($word);

					  				
					  				// PAUSE MARK
					  				if ( strpos($lang,"AR")!==false && isset($pauseMarksArr[$word]) )
					  				{
					  					$TOTALS_ARR['PAUSEMARKS'][$word]++;
					  					continue;
					  				}
					  				else 
				  					// SAJDAH MARK
				  					if ( $word == $sajdahMark )
				  					{
				  						$TOTALS_ARR['SAJDAT_TELAWA']['COUNT']++;
				  						$TOTALS_ARR['SAJDAT_TELAWA']['VERSES'][]=array($s,$a,$verseText);
				  						continue;
				  					}
				  					else
				  					// SAKTA LATIFA
				  					if ( $word == $saktaLatifaMark )
				  					{
										$TOTALS_ARR['SAKTA_LATIFA']['COUNT']++;
				  						$TOTALS_ARR['SAKTA_LATIFA']['VERSES'][]=array($s,$a,$verseText);
				  						continue;
				  					}
				  					
				  					
				  					// Mainly for english translations
				  					if ( $lang=="EN")
				  					{
				  						$word = strtolower(cleanAndTrim($word));
				  					}
				  					
				  					
				  					// ignore empty words - result of trimming
				  					if ( empty($word))
				  					{
				  						// the case of " - " in english translations
				  						continue;
				  						
				  					}
				  					
				  					$wordIndex++;
				  					
				  					
				  				
				  					
				  					if ( $wordIndex==1)
				  					{
				  						if (!isset($WORDS_FREQUENCY_ARR['VERSE_BEGINNINGS'][$word])) $WORDS_FREQUENCY_ARR['VERSE_BEGINNINGS'][$word]=0;
				  						$WORDS_FREQUENCY_ARR['VERSE_BEGINNINGS'][$word]++;
				  						
				  					}
				  					else 
			  						if ( $wordIndex==count($wordsArr))
			  						{
			  							if (!isset($WORDS_FREQUENCY_ARR['VERSE_ENDINGS'][$word])) $WORDS_FREQUENCY_ARR['VERSE_ENDINGS'][$word]=0;
			  							$WORDS_FREQUENCY_ARR['VERSE_ENDINGS'][$word]++;
			  						
			  						}		


			  						
					  				
					  				$TOTALS_ARR['WORDS']++;
					  				
					  				
					  				
					  				if (!isset($WORDS_FREQUENCY_ARR['TOTAL_PER_VERSE'][$s])) $WORDS_FREQUENCY_ARR['TOTAL_PER_VERSE'][$s]=array();
					  				if (!isset($WORDS_FREQUENCY_ARR['TOTAL_PER_VERSE'][$s][$a])) $WORDS_FREQUENCY_ARR['TOTAL_PER_VERSE'][$s][$a]=array();
					  				if (!isset($WORDS_FREQUENCY_ARR['TOTAL_PER_VERSE'][$s][$a][$word])) $WORDS_FREQUENCY_ARR['TOTAL_PER_VERSE'][$s][$a][$word]=0;
					  				 
					  				$WORDS_FREQUENCY_ARR['TOTAL_PER_VERSE'][$s][$a][$word]++;
					  				
					  				
					  				if (!isset($WORDS_FREQUENCY_ARR['WORDS'][$word])) $WORDS_FREQUENCY_ARR['WORDS'][$word]=0;
					  				 
					  				$WORDS_FREQUENCY_ARR['WORDS'][$word]++;
					  				
					  				$TOTALS_ARR['TOTAL_PER_SURA'][$s]['WORDS']++;
					  				
					  				if (!isset($WORDS_FREQUENCY_ARR['WORDS_PER_SURA'][$s][$word])) $WORDS_FREQUENCY_ARR['WORDS_PER_SURA'][$s][$word]=0;
					  				
					  				$WORDS_FREQUENCY_ARR['WORDS_PER_SURA'][$s][$word]++;
					  				
					  				
					  				//if (!isset($INVERTED_INDEX[$word]) ) $INVERTED_INDEX[$word] = array();
					  				
					  				//$INVERTED_INDEX[$word][] = array("SURA"=>$s,"AYA"=>$a,"INDEX_IN_AYA_EMLA2Y"=>$wordIndex,"WORD_TYPE"=>"NORMAL_WORD");
					  				 
					  			
					  				addToInvertedIndex($invertedIndexBatchApcArr,$lang,$word,$s,$a,$wordIndex,"NORMAL_WORD");
					  				 
					  		
					  			
					  				
					  				
					  				/** CALCULATE WORD LENGTHG **/
					  				$wordLength = mb_strlen($word);
					  				 
					  				if ( $wordLength >= $maxWordLength)
					  				{
					  					$maxWordLength = $wordLength;
					  					$maxWord = $word;
					  				}
					  				 
					  				if ( $wordLength <= $minWordLength)
					  				{
					  					$minWordLength = $wordLength;
					  					$minWord = $word;
					  				}
					  				 
					  				$avgWordLength+=$wordLength;
					  				/** END CALCULATE WORD LENGTHG **/
					  				
					  				
					  				
					  				$charsInWordArr = preg_split("//u",$word, -1,PREG_SPLIT_NO_EMPTY);
					  				
					  			
					  				
					  				/* CHARS IN EACH WORD  LOOP **/
					  				foreach ($charsInWordArr as $char)
					  				{
					  			
					  					//echoN($char." ".in_array($char,$pauseMarksArrTemp));
					  			
					  				
					  					// SPACE
					  					 if ( $char==" " )
					  					{
					  						continue;
					  					}
					  					
					  					$TOTALS_ARR['CHARS']++;
					  					$TOTALS_ARR['TOTAL_PER_SURA'][$s]['CHARS']++;
					  				}
					  				
					  			
					  				
					  			}
					 
					  			
					  			
					  		
					  	
					  			$TOTALS_ARR['VERSES']++;
					  			$TOTALS_ARR['TOTAL_PER_SURA'][$s]['VERSES']++;
					  			
// 					  			if ( $TOTALS_ARR['VERSES']>30)
// 					  				exit;
					  			
					  		}
					  		/** END AYA's LOOP **/
					  		
					  
					  		
					  		
				  		}
				  		/** END SURA's LOOP **/
				  		
				  		
				  		/* SURA'S LOOP **/
				  		for ($s=0;$s<$numberOfSuras;$s++)
				  		{

				  				$TOTALS_ARR['TOTAL_PER_SURA'][$s]['NRWORDS']=count($WORDS_FREQUENCY_ARR['WORDS_PER_SURA'][$s]);

				  				
				  				arsort($WORDS_FREQUENCY_ARR['WORDS_PER_SURA'][$s]);
				  			
				  		}
				  		
				  		$TOTALS_ARR['NRWORDS'] = count($WORDS_FREQUENCY_ARR['WORDS']);
				  		
				  		
				  		$TOTALS_ARR['PAUSEMARKS_AGGREGATION'] = 0;
				  		
				  		// AGGREGATE PAUSE MARKS
				  		foreach($TOTALS_ARR['PAUSEMARKS'] as $pmLabel => $pmCount)
				  		{
				  			//echo $pmLabel.$pmCount;
				  			$TOTALS_ARR['PAUSEMARKS_AGGREGATION']+=$pmCount;
				  		}
				  
				  		
				  		
				  		/**
				  		 * CALCULATING TF-IDF TABLE
				  		 */
				  		foreach ($WORDS_FREQUENCY_ARR['WORDS'] as $wordLabel => $wordFreq )
				  		{
				  			
				  			$termFrequency = $wordFreq;
				  			$termFrequencyPercentage = ($termFrequency/$TOTALS_ARR['WORDS'])*100;
				  			// DOCUMENT = VERSE
				  			$documentFrequency = 0;
				  			$inverseDocumentFrequency = 0;
				  			

				  			//CHECKING VERSES
				  			for ($s=0;$s<$numberOfSuras;$s++)
				  			{
				  				
				  				
				  				//$versesPerSura = $TOTALS_ARR['TOTAL_PER_SURA'][$s]['VERSES'];
				  				
				  				//for ($a=0;$a<$versesPerSura;$a++)
				  				//{

				  				
						  			if ( isset($WORDS_FREQUENCY_ARR['WORDS_PER_SURA'][$s][$wordLabel]) )
						  			{
						  				//= $WORDS_FREQUENCY_ARR['TOTAL_PER_VERSE'][$s][$a][$wordLabel]
						  				$documentFrequency++;
						  			}
				  				//}
 
				  			 
				  			}
				  			
				  			$inverseDocumentFrequency = log( ($numberOfSuras/$documentFrequency), 10 );
				  			
				  			$TFIDF = $termFrequency * $inverseDocumentFrequency;
				  			
				  		
				  			
				  			//echoN("WORD:$wordLabel PRCG:$termFrequencyPercentage TF:$termFrequency DF:$documentFrequency IDF:$inverseDocumentFrequency TFIDF:$TFIDF ");
		
				  			$WORDS_FREQUENCY_ARR['WORDS_TFIDF'][$wordLabel]=array("TF"=>$termFrequency,"TPC"=>$termFrequencyPercentage,
				  			"DF"=>$documentFrequency,"IDF"=>$inverseDocumentFrequency,"TFIDF"=>$TFIDF);
				  			
				  			
				  		}
				  		/** END OF TFIDF TABLE **/
				  		
				  		
				  		rsortBy($WORDS_FREQUENCY_ARR['WORDS_TFIDF'],'TF');
				  		
				  		//preprint_r($WORDS_FREQUENCY_ARR['WORDS_TFIDF']);
				  		
				  		
			
				  		
				  		
				  		
				  		/** Continuing  WORD/VERSE LENGTH CALCULATE **/
				  		$avgWordLength = $avgWordLength/$TOTALS_ARR['WORDS'];
				  		$avgVerseLength = $avgVerseLength/$TOTALS_ARR['VERSES'];
				  		
				  		/*
				  		echoN($minWordLength." - ".$minWord);
				  		echoN($maxWordLength." - ".$maxWord);
				  		echoN($avgWordLength);
				  		
				  		echoN($minVerseLength." - ".$minVerse);
				  		echoN($maxVerseLength." - ".$maxVerse);
				  		echoN($avgVerseLength);
				  		*/
				  		
				  		$TOTALS_ARR['MIN_WORD_LENGTH']=$minWordLength;
				  		$TOTALS_ARR['AVG_WORD_LENGTH']=round($avgWordLength,2);
				  		$TOTALS_ARR['MAX_WORD_LENGTH']=$maxWordLength;
				  		$TOTALS_ARR['MIN_WORD']=$minWord;
				  		$TOTALS_ARR['MAX_WORD']=$maxWord;
				  		
				  		$TOTALS_ARR['MIN_VERSE_LENGTH']=$minVerseLength;
				  		$TOTALS_ARR['AVG_VERSE_LENGTH']=round($avgVerseLength,2);
				  		$TOTALS_ARR['MAX_VERSE_LENGTH']=$maxVerseLength;
				  		$TOTALS_ARR['MIN_VERSE']=$minVerse;
				  		$TOTALS_ARR['MAX_VERSE']=$maxVerse;
				  	
				  		/** end CALCULATE WORD/VERSE LENGTH **/
				  		
				  		//exit;;
				  		
				  		arsort($WORDS_FREQUENCY_ARR['WORDS']);
				  		arsort($WORDS_FREQUENCY_ARR['VERSE_BEGINNINGS']);
				  		arsort($WORDS_FREQUENCY_ARR['VERSE_ENDINGS']);
				  		
				  		//preprint_r($WORDS_FREQUENCY_ARR);
				  		
				  		
				  		/////// LOADING LANGUAGE RESOURCE FILES
				  		
				  		$resourceFile = $englishResourceFile;
				  		if ( strpos($lang,"AR")!==false)
				  		{
				  			$resourceFile = $arabicResourceFile;
				  		}
				  		
				  		 $languageResourcesArr = file($resourceFile,FILE_SKIP_EMPTY_LINES|FILE_IGNORE_NEW_LINES);
				  		 
				  		 $RESOURCES = array();
				  		 foreach($languageResourcesArr as $index=> $resourceLine)
				  		 {
				  		 
				  		 	$resourcePairsArr = preg_split("/\|/", $resourceLine);
				  		 
				  		 	$resourceID = $resourcePairsArr[0];
				  		 	$resourceValue = $resourcePairsArr[1];
				  		 	
				  		 	$RESOURCES[$resourceID]=$resourceValue;
				  		 	
				  		 	
				  		 }
				  		 
				  		


				  		//$MODEL_CORE['LOADED']=1;
				  		//$MODEL_CORE[$lang]['META_DATA'] = $META_DATA;
				  		
				  		addValueToMemoryModel($lang, "MODEL_CORE", "META_DATA", "", $META_DATA);
				  		
				  		//$MODEL_CORE[$lang]['TOTALS'] = $TOTALS_ARR;
				  		
				  		addValueToMemoryModel($lang, "MODEL_CORE", "TOTALS", "", $TOTALS_ARR);
				  		
				  		//$MODEL_CORE[$lang]['WORDS_FREQUENCY'] = $WORDS_FREQUENCY_ARR;
				  		
				  		addValueToMemoryModel($lang, "MODEL_CORE", "WORDS_FREQUENCY", "", $WORDS_FREQUENCY_ARR);
				  		
				  		addValueToMemoryModel($lang, "MODEL_CORE", "WORDS_FREQUENCY", "WORDS", $WORDS_FREQUENCY_ARR['WORDS']);
				  		
				  		
				  		//$MODEL_CORE[$lang]['QURAN_TEXT'] = $QURAN_TEXT;
				  		
				  		addValueToMemoryModel($lang, "MODEL_CORE", "QURAN_TEXT", "", $QURAN_TEXT);
				  		
				  		//$MODEL_CORE[$lang]['RESOURCES']=$RESOURCES;
				  		
				  		addValueToMemoryModel($lang, "MODEL_CORE", "RESOURCES", "", $RESOURCES);

				  		//$MODEL_CORE[$lang]['STOP_WORDS']= $stopWordsArr;
				  		
				  		addValueToMemoryModel($lang, "MODEL_CORE", "STOP_WORDS", "", $stopWordsArr);
				  		
				  		//$MODEL_CORE[$lang]['STOP_WORDS_STRICT_L2']= $stopWordsStrictL2Arr;
				  		
				  		addValueToMemoryModel($lang, "MODEL_CORE", "STOP_WORDS_STRICT_L2", "", $stopWordsStrictL2Arr);
				  		
				  		//file_put_contents("$serializedModelFile.core", (json_encode($MODEL_CORE)));
				  		
				  		
				  		//$MODEL_SEARCH[$lang]['INVERTED_INDEX'] = $INVERTED_INDEX;
				  		
				  		
				  		/*$invertedIndexIterator = getAPCIterator("MODEL_SEARCH.*");
				  			
				  		foreach($invertedIndexIterator as $cursor)
				  		{
				  			preprint_r($cursor);
				  		}*/
				  			
				  			
				  	
				  		addToMemoryModelBatch($invertedIndexBatchApcArr);
				  		

				  		
				  		//$res = apc_store("MODEL_CORE[$lang]",$MODEL_CORE[$lang]);
				  		
				  		//if ( $res===false){ throw new Exception("Can't cache MODEL_CORE[$lang]"); }
				  		
				  		//$res = apc_store("MODEL_SEARCH[$lang]",$MODEL_SEARCH[$lang]);
				  		
				  		//if ( $res===false){ throw new Exception("Can't cache MODEL_SEARCH[$lang]"); }
				  		
				  		
				  		//file_put_contents("$serializedModelFile.search", (json_encode($MODEL_SEARCH)));
				  		
		
				  		if ( $lang=="AR"  )
				  		{
					  		//$MODEL_QAC['QAC_MASTERTABLE'] = $qacMasterSegmentTable;
					  		
				  			
				  			
					  		//$MODEL_QAC['QAC_POS'] = $qacPOSTable;
					  		
				  			
				  			
				  			addToMemoryModelBatch($qacPOSTableBatchApcArr);
				  			
					  		//$MODEL_QAC['QAC_FEATURES'] = $qacFeaturesTable;
					  		
				  			
				  			addToMemoryModelBatch($qacFeatureTableBatchApcArr);
				  			
					  		//$MODEL_QAC['QAC_ROOTS_LOOKUP'] = $rootsLookupArray;
					  		
					  		
					  		
					  		
					  		//file_put_contents("$serializedModelFile.qac", (json_encode($MODEL_QAC)));
					  		
					  		//$res = apc_store("MODEL_QAC",$MODEL_QAC);
					  		
					  		//if ( $res===false){ throw new Exception("Can't cache MODEL_QAC"); }
					  		
					  		
					  		rsortBy($quranaConcecpts,'FREQ');
					  		
					  		$MODEL_QURANA['QURANA_CONCEPTS'] = $quranaConcecpts;
					  		$MODEL_QURANA['QURANA_PRONOUNS'] = $quranaResolvedPronouns;
					  		
					  	
					  		//file_put_contents("$serializedModelFile.qurana", (json_encode($MODEL_QURANA)));
					  		
					  		$res = apc_store("MODEL_QURANA",$MODEL_QURANA);
					  		
					  		if ( $res===false){ throw new Exception("Can't cache MODEL_QURANA"); }
					  		
				  		}
				  
				  		
				  		
		
				  		
				  		
				  
				  		
				  		
				  		
				  		

				  		//preprint_r($MODEL['INVERTED_INDEX'] );exit;
				  		//preprint_r($WORDS_FREQUENCY_ARR['VERSE_ENDINGS']);
				  		
				  		//echo serialize(json_encode($MODEL));
				  
				  	
				  		
				  		//preprint_r($MODEL['EN']);
}

?>








