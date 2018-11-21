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
require_once(dirname(__FILE__)."/../libs/core.lib.php");
require_once(dirname(__FILE__)."/custom.translation.table.lib.php");
require_once(dirname(__FILE__)."/../libs/PorterStemmer.php");

function mapQACPoSToWordnetPoS($qacPOS)
{
	
	$trans = array("PN" => "noun", "N" => "noun", "V" => "verb", "ADJ" => "adj", "LOC" => "adv", "T" => "adv");
	
	// since concept extracted from relations may have "SUBJECT" or "OBJECT" POS
	if ( !isset($trans[$qacPOS]) )
	{
		return "noun";
	}
	return  strtr($qacPOS,$trans);
}
function trimVerb($verb)
{
	//very bad idea, spoils everything
	return preg_replace("/^(وَ|فَ)/um", "", $verb);
}

function isNounPhrase($posPattern)
{
	return ( $posPattern=="N" || $posPattern=="PN" || $posPattern=="DET N"
	);
	//REMOVED || $posPattern=="N PRON"  نصيبك
}


function generateEmptyConceptMetadata()
{
	return array("LEM"=>"","FREQ"=>0,
			"POS"=>"","SEG"=>array(),"SIMPLE_WORD"=>"",
			"ROOT"=>"","WEIGHT"=>"","AKA"=>array(),
			"TRANSLATION_EN"=>"","TRANSLITERATION_EN"=>"",
			"MEANING_AR"=>array(),"MEANING_EN"=>array(),
			"DBPEDIA_LINK"=>"","WIKIPEDIA_LINK"=>"", "IMAGES"=>"", "DESC_EN"=>array(), "DESC_AR"=>array());
}

function getTermArrBySimpleWord($finalTerms, $sentSimpleWord)
{


	foreach ($finalTerms as $lemaUthmani=>$termArr)
	{
			
		$mySimpleWord = $termArr['SIMPLE_WORD'];
			
		//echoN("$sentSimpleWord==$mySimpleWord");
			
		if ( $sentSimpleWord==$mySimpleWord)
		{
			return $termArr;
		}
			
	}

	return false;
}

function addNewConcept(&$finalConceptsArr,$newConceptName,$coneptType,$exPhase,$freq,$engTranslation)
{

	if ( !isset($finalConceptsArr[$newConceptName]))
	{
		$conceptMetaDataArr = generateEmptyConceptMetadata();
		
		if ( !empty($engTranslation))
		{
			$conceptMetaDataArr['TRANSLATION_EN']=$engTranslation;
		}
		
		$newConceptName = trim($newConceptName);
		$engTranslation = trim($engTranslation);
		
		$finalConceptsArr[$newConceptName]=array("CONCEPT_TYPE"=>$coneptType,"EXTRACTION_PHASE"=>$exPhase,"FREQ"=>$freq,"EXTRA"=>$conceptMetaDataArr);
		
		return true;
	}
	else 
	{
		//
		// IT WAS MEANT TO BE T-BOX IF IT WAS NOT FOUND, SO IF IT IS FOUND SWITCH IT TO T-BOX SINCE IT IS A PARENT
		if ( $coneptType=="T-BOX")
		{
			// SHOULD SWITCH TO T-BOX SINCE IT IS A PARENT CLASS NOW - FOR OWL SERIALIZATION BUGS
			$finalConceptsArr[$newConceptName]['CONCEPT_TYPE']='T-BOX';
		}
		
		return false;
	}
		
	
	
}

function printRelation($relationArrEntry)
{
	 
	echoN("---SUBJ:<b>".$relationArrEntry['SUBJECT']."</b> VERB:".$relationArrEntry['VERB']." OBJ:<b>".$relationArrEntry['OBJECT']."</b>");
}



function addNewRelation(&$relationArr,$type,$subject,$verbSimple,$object,$posPattern,$verbEngTranslation,$verbUthmani)
{
	$newRelation= array("TYPE"=>$type,"SUBJECT"=>trim($subject),
			"VERB"=>trim($verbSimple),
			"OBJECT"=>trim($object),
			"POS_PATTERN"=>$posPattern,
			"FREQ"=>1,
			"VERB_ENG_TRANSLATION"=>trim($verbEngTranslation),
			"VERB_UTHMANI"=>trim($verbUthmani));
	
	printRelation($newRelation);
	
		
	$relationHash = md5($newRelation['SUBJECT'].$newRelation['VERB'].$newRelation['OBJECT']);
		
	if ( !isset($relationArr[$relationHash]))
	{
	
		$relationArr[$relationHash]=$newRelation;
		return true;
	}
	else
	{
		$relationArr[$relationHash]['FREQ']++;
		return false;
	}
}

function addRelation(&$relationsArr,$type, $subject,$verb,$object,$joinedPattern,$verbEngTranslation="",$fullVerbQuranWord="")
{
	global $WORDS_TRANSLATIONS_AR_EN;
	global $is_a_relation_name_en;
	
		
	
	if ( empty($subject) || empty($object) )
	{
		return false;
	}
	
	
	// make shallow last resort, since it spoils words and lead to duplicate oncepts
	if ( !isSimpleQuranWord($subject) )
	{
		//CONVERT UTHMANI TO SIMPLE
		$subjectSimple = getItemFromUthmaniToSimpleMappingTable($subject);
			
		// IF NOT CORRESPONDING SIMPLE WORD, CONVERT USING SHALLOW CONVERSION ALGORITHM
		if ( empty($subjectSimple))
		{
			$subjectSimple = shallowUthmaniToSimpleConversion($subject);
		}
	}
	else 
	{
		$subjectSimple = $subject;
	}

	// SAME AS ABOVE BUT FOR OBJECT
	if ( !isSimpleQuranWord($object) )
	{
		$objectSimple = getItemFromUthmaniToSimpleMappingTable($object);

		//object simple to avoid null in case when not in the mapping table
		if ( empty($objectSimple))
		{
			$objectSimple = shallowUthmaniToSimpleConversion($object);
		}
	}
	else
	{
		$objectSimple = $object;
	}
		
	
	$verbUthmani = $verb;
	$verbSimple = "";
	
	///////// VERB TRANSLATION
	if ( empty($verbEngTranslation))
	{
		$verbEngTranslation ="";
	
		// SINGLE WORD VERB
		if ( !isMultiWordStr($verb))
		{
			$verb = trim($verb);
			
			$translatableVerb = $fullVerbQuranWord;
			
			// VERB IS SIMPLE
			if ( isSimpleQuranWord($verb) )
			{
				$translatableVerb = getItemFromUthmaniToSimpleMappingTable($fullVerbQuranWord);

			}
			else
			{

				$verbSimple = getItemFromUthmaniToSimpleMappingTable($verb);;
			}
			
			$verbEngTranslation = cleanEnglishTranslation($WORDS_TRANSLATIONS_AR_EN[$translatableVerb]);
			
			//IF NOT IN TRANSLATION TABLE - EX: ONE OF THE SEGMENTS TRIMMED
			if ( empty($verbEngTranslation))
			{
				// CHECK IF IS ALSO NOTO IN TRANSLATION ENTRY
				if (!isFoundInTranslationTable($translatableVerb,"VERB"))
				{
					

					// TRANSLATE USING MICROSOFT API
					$verbEngTranslation = translateText($translatableVerb,"ar","en");
					
					// ADD TO QA CUSTOM TRANSLATION TABLE
					addTranslationEntry($verbEngTranslation, "VERB", $translatableVerb,"AR");
					
					//no need
					//persistTranslationTable();
				}
				else
				{
					$customTranslationEntryArr =getTranlationEntryByEntryKeyword($translatableVerb);
					
					$verbEngTranslation = $customTranslationEntryArr['EN_TEXT'];
				}
			}
		}
		// MUTIWORD VERB (PHRASE) such as negated verbs
		else
		{
			
			//SPLIT PHRASE
			$verbPhraseArr = preg_split("/ /", $verb);
				
			foreach($verbPhraseArr as $verbPart)
			{
				
				$translatableVerb = $verbPart;
				
				// IF SIMPLE
				if ( isSimpleQuranWord($verbPart) )
				{
					//GET UTHMANI WORD TO BE ABEL TO TRANSLATE
					$translatableVerb = getItemFromUthmaniToSimpleMappingTable($verbPart);
				}
				else
				{
					// GET SIMPLE WORD TO BE ADDED IN RELATION META
					$simplePart = getItemFromUthmaniToSimpleMappingTable($verbPart);
					
					//if not in translation table, use shalow conversion
					if ( empty($simplePart))
					{
						$simplePart = shallowUthmaniToSimpleConversion($verbPart);
					}
					
					$verbSimple = $verbSimple." ".$simplePart;
					
					// THIS VARIABLE NEEDED FOR TRANSLATION
					$translatableVerb = $simplePart;
				}
				
				// TRANSLATE
				$verbPartTranslated = cleanEnglishTranslation($WORDS_TRANSLATIONS_AR_EN[$translatableVerb]);
				
				//IF NOT IN TRANSLATION TABLE - EX: ONE OF THE SEGMENTS TRIMMED
				if ( empty($verbPartTranslated))
				{
					// CHECK IF IS ALSO NOTO IN TRANSLATION ENTRY
					if (!isFoundInTranslationTable($verbPart,"VERB"))
					{
							

						
						// TRANSLATE USING MICROSOFT API
						$verbPartTranslated = translateText($verbPart,"ar","en");
							
						// ADD TO QA CUSTOM TRANSLATION TABLE
						addTranslationEntry($verbPartTranslated, "VERB", $verbPart,"AR");
							
						
						//persistTranslationTable();
					}
					else
					{
						$customTranslationEntryArr =getTranlationEntryByEntryKeyword($verbPart);
							
						$verbPartTranslated = $customTranslationEntryArr['EN_TEXT'];
					}
				}
				
				// TRANSLATION ACCUMILATION
				$verbEngTranslation = $verbEngTranslation . " " .$verbPartTranslated;
			}
		}
	}
	
	if ( $verbEngTranslation!="is kind of" && $verbEngTranslation!="part of" && $verbEngTranslation!=$is_a_relation_name_en)
	{
		//$verbEngTranslation = removeBasicEnglishStopwordsNoNegation($verbEngTranslation);
	}
		
	$verbSimple = trim($verbSimple);
	
	if ( empty($verbSimple))
	{
		$verbSimple = removeTashkeel(shallowUthmaniToSimpleConversion($verbUthmani));
	}

		
	return addNewRelation($relationsArr,$type,$subjectSimple,$verbSimple,$objectSimple,$joinedPattern,$verbEngTranslation,$verbUthmani);
}

function resolvePronouns($qacLocation)
{
	global $MODEL_QURANA;
	$pronArr = array();
	$index=0;
	//echoN($qacLocation);
	//if ( $qacLocation=="3:146:11")
	//preprint_r($MODEL_QURANA['QURANA_PRONOUNS']);
	foreach($MODEL_QURANA['QURANA_PRONOUNS'][$qacLocation] as $coneptArr)
	{

		$coneptId = $coneptArr['CONCEPT_ID'];
		$conceptName = $MODEL_QURANA['QURANA_CONCEPTS'][$coneptId]['AR'];

		echoN($conceptName);

		// qurana null concept
		//if ( $conceptName=="null") continue;

		$pronArr[$index++]=$conceptName;
	}

	return $pronArr;
}

function flushProperRelations(&$relationsArr,&$conceptsArr,&$verb,&$lastSubject,$ssPoSPattern,&$filledConcepts)
{


	if ( count($conceptsArr)>=2   )
	{

		if (empty($verb))
		{
			$verb = "n/a";
		}
			
			

		if ( $conceptsArr[0]!=$conceptsArr[1])
		{
			$type = "NON-TAXONOMIC";
			addRelation($relationsArr,$type, $conceptsArr[0],$verb,$conceptsArr[1],$ssPoSPattern);

			if ( count($conceptsArr)>2 )
			{
				addRelation($relationsArr,$type, $conceptsArr[1],"n/a",$conceptsArr[2],$ssPoSPattern);
				addRelation($relationsArr,$type, $conceptsArr[0],"n/a",$conceptsArr[2],$ssPoSPattern);
			}
		}
			
		$conceptsArr=array();
		$verb = null;
		$filledConcepts=0;
	}
		
		
	if ( count($conceptsArr)==1 && !empty($verb) && !empty($lastSubject) && $conceptsArr[0]!=$lastSubject)
	{

		//echoN("||||".$conceptsArr[0]."|".$lastSubject);




		$temp = $conceptsArr[0];
		$conceptsArr[0] = $lastSubject;
		$conceptsArr[1] = $temp;


		// many problems
		if ( $conceptsArr[0]!=$conceptsArr[1])
		{
			$type = "NON-TAXONOMIC";
			addRelation($relationsArr,$type, $conceptsArr[0],$verb,$conceptsArr[1],$ssPoSPattern);
		}
			
			

			
		$conceptsArr=array();
		$verb = null;

		$filledConcepts=0;
	}
}
	
	


function getConceptBySegment($conceptsArr, $segment)
{
	foreach ($conceptsArr as $conceptName=>$conceptArr)
	{
		$extraArr = $conceptArr['EXTRA'];
		$simpleWord = $extraArr['SIMPLE_WORD'];
			
		foreach ($extraArr['SEG'] as $uthmaniSegment=>$simpleName)
		{
			//echoN("$uthmaniSegment==$segment");

			if ( $uthmaniSegment==$segment)
			{
					
				return $simpleWord;
			}
		}

			
	}

	return false;
}

function getConceptByLemma($conceptsArr, $lemma)
{
	foreach ($conceptsArr as $conceptName=>$conceptArr)
	{
		$extraArr = $conceptArr['EXTRA'];
		$simpleWord = $extraArr['SIMPLE_WORD'];


		//echoN("$uthmaniSegment==$segment");

		if ( $extraArr['LEM']==$lemma)
		{

			return $simpleWord;
		}
			


	}

	return false;
}

function getConceptTypeFromDescriptionText($abstract)
{
	$matches = array();
		

	$taggesSentenceArr = posTagText($abstract);

	//printTag($taggesSentenceArr);

	$counter =0;
	reset($taggesSentenceArr);
	while(current($taggesSentenceArr))
	{
		$currentTagArr = current($taggesSentenceArr);
		$nextTagArr = next($taggesSentenceArr);
			
		if ( ($currentTagArr['tag']=="VBZ" || $currentTagArr['tag']=="VBD" )
		&& $nextTagArr['tag']=="DT")
		{
			$thirdTagArr = next($taggesSentenceArr);


			if ( ($thirdTagArr['tag']=="NN" || $thirdTagArr['tag']=="VBG" )&& strtolower($thirdTagArr['token'])!="name")
			{
				$forthTagArr = next($taggesSentenceArr);
				if ( !empty($forthTagArr) && $forthTagArr['tag']=="IN"  )
				{
					//echoN("########".$nextTagArr['token']);
					return $thirdTagArr['token'];
				}
			}

		}
			
		if ( $counter++ > 20 ) return false;
			
			
			
	}


	return false;
		
}


/** Returns words from QAC by PoS tags - grouped by lemma **/
function getWordsByPos(&$finalTerms,$POS)
{

	global $LEMMA_TO_SIMPLE_WORD_MAP;
	 
	 
	$qacPosEntryArr = getModelEntryFromMemory("AR","MODEL_QAC","QAC_POS",$POS);
	
	$QURAN_TEXT = getModelEntryFromMemory("AR", "MODEL_CORE", "QURAN_TEXT", "");
	
	$TOTALS = getModelEntryFromMemory("AR", "MODEL_CORE", "TOTALS", "");
	
	$PAUSEMARKS = $TOTALS['PAUSEMARKS'];
	
	$WORDS_FREQUENCY = getModelEntryFromMemory("AR", "MODEL_CORE", "WORDS_FREQUENCY", "");
	
	// Get all segment in QAC for that PoS
	foreach($qacPosEntryArr as $location => $segmentId)
	{

		$qacMasterTableEntry = getModelEntryFromMemory("AR","MODEL_QAC","QAC_MASTERTABLE",$location);
		
		// get Word, Lema and root
		$segmentWord = $qacMasterTableEntry[$segmentId-1]['FORM_AR'];
		$segmentWordLema = $qacMasterTableEntry[$segmentId-1]['FEATURES']['LEM'];
		$segmentWordRoot = $qacMasterTableEntry[$segmentId-1]['FEATURES']['ROOT'];
		$verseLocation = substr($location,0,strlen($location)-2);
		//$segmentWord = removeTashkeel($segmentWord);


		// get word index in verse
		$wordIndex = (getWordIndexFromQACLocation($location));


		//$segmentFormARimla2y = $UTHMANI_TO_SIMPLE_WORD_MAP_AND_VS[$segmentWord];

		// get simple version of the word index
		$imla2yWordIndex = getImla2yWordIndexByUthmaniLocation($location);


		// get verse text
		$verseText = getVerseByQACLocation($QURAN_TEXT,$location);
		 

		 
		//echoN("|$segmentWord|$imla2yWord");
		$segmentWordNoTashkeel = removeTashkeel($segmentWordLema);
		 
		$superscriptAlef = json_decode('"\u0670"');
		$alefWasla = "ٱ"; //U+0671
		 
		//$imla2yWord = $LEMMA_TO_SIMPLE_WORD_MAP[$segmentWordLema];
		 
		 
		// this block is important since $LEMMA_TO_SIMPLE_WORD_MAP is not good for  non $superscriptAlef words
		// ex زيت lemma is converted to زيتها which spoiled the ontology concept list results
		if(mb_strpos($segmentWordLema, $superscriptAlef) !==false
		|| mb_strpos($segmentWordLema, $alefWasla) !==false )
		{

			$imla2yWord = getItemFromUthmaniToSimpleMappingTable($segmentWordLema);

			if (empty($imla2yWord))
			{
				$imla2yWord = $LEMMA_TO_SIMPLE_WORD_MAP[$segmentWordLema];
			}



		}
		else
		{
			$imla2yWord = getItemFromUthmaniToSimpleMappingTable($segmentWordLema);

			if ( empty($imla2yWord))
			{
				$imla2yWord = shallowUthmaniToSimpleConversion($segmentWordLema);//$segmentWordNoTashkeel;
					
			}
		}
		 
		 
		 
		/// in case the word was not found after removing tashkeel, try the lema mappign table
		$termWeightArr = $MODEL_CORE['WORDS_FREQUENCY']['WORDS_TFIDF'][$imla2yWord];


		 
		// NOT WORKING BECAUSE LEMMAS WILL NOT BE IN SIMPLE WORDS LIST و الصابيئن =>صَّٰبِـِٔين
		// if the word after removing tashkeel is not found in quran simple words list, then try lemma table
		/*if (!isset($MODEL_CORE['WORDS_FREQUENCY']['WORDS'][$imla2yWord]) )
		 {
		 $imla2yWord = $LEMMA_TO_SIMPLE_WORD_MAP[$segmentWordLema];

		 if ( empty($imla2yWord) )
		 {
		 echoN($segmentWordLema);
		 echoN($imla2yWord);
		 preprint_r($LEMMA_TO_SIMPLE_WORD_MAP);
		 preprint_r($MODEL_CORE['WORDS_FREQUENCY']['WORDS']);
		 exit;
		 }
		 }*/

		 
		if ( empty($termWeightArr))
		{
			//only for weight since the lema table decrease qurana matching
			$imla2yWordForWeight = $LEMMA_TO_SIMPLE_WORD_MAP[$segmentWordLema];
			$termWeightArr = $WORDS_FREQUENCY['WORDS_TFIDF'][$imla2yWordForWeight];


		}
		 
		$termWeight = $termWeightArr['TFIDF'];
		////////////////////////////////////////////

		$termWord = $segmentWordLema;//$imla2yWord;//"|$segmentWord| ".$imla2yWord ." - $location:$segmentId - $wordIndex=$imla2yWordIndex";
		 
		if ( !isset($finalTerms[$termWord]))
		{
			$finalTerms[$termWord] = generateEmptyConceptMetadata();

			$finalTerms[$termWord]['LEM'] = $segmentWordLema;
			$finalTerms[$termWord]['POS'] = $POS;
			$finalTerms[$termWord]['SIMPLE_WORD'] = $imla2yWord;
			$finalTerms[$termWord]['ROOT'] = $segmentWordRoot;
			$finalTerms[$termWord]['WEIGHT'] = $termWeight;


		}
		 
		$finalTerms[$termWord]["FREQ"]=$finalTerms[$termWord]["FREQ"]+1;
			
		if ( !isset($finalTerms[$termWord]["SEG"][$segmentWord]) )
		{
			$finalTerms[$termWord]["SEG"][$segmentWord]=$imla2yWord;
				
		}
			
		if ( !isset($finalTerms[$termWord]["POSES"][$POS]))
		{
			$finalTerms[$termWord]["POSES"][$POS]=1;
		}
			
			
		 
		 





	}
	 
	return $finalTerms;
}

function loadExcludesByType($type)
{
	$fileArr = file("../data/ontology/extraction/cleaner/excluded.$type",FILE_IGNORE_NEW_LINES|FILE_SKIP_EMPTY_LINES);
	
	$excludedItemsArr = array();
	
	foreach($fileArr as  $itemName)
	{

		$itemName = trim($itemName);
		$excludedItemsArr[$itemName]=1;
		
	}
	
	return $excludedItemsArr;
	
}

function loadExcludedSynonymssArr()
{
	$fileArr = file("../data/ontology/extraction/excluded.synonyms",FILE_IGNORE_NEW_LINES|FILE_SKIP_EMPTY_LINES);

	$EXCLUDED_SYN = array();

	foreach($fileArr as  $synonym)
	{
		$synonym = trim($synonym);
		$EXCLUDED_SYN[$synonym]=1;

	}

	return $EXCLUDED_SYN;

}

function getXMLFriendlyString($className)
{
	return strtr($className, " ", "_");
}

function stripOntologyNamespace($className)
{
	global $qaOntologyNamespace;
	
	$hashLocation = strpos($className,"#");
	if ($hashLocation!==false)
	{
		$className = substr($className,$hashLocation+1);
	}
	else
	{
		$className = str_replace(substr($qaOntologyNamespace,0,-1), "", $className);
	}
	
	return $className;
}



function getClassesNames($classesOntologyArray, $lang){
    $names = [];
    if($lang == "En")
        foreach($classesOntologyArray as $key => $value){
            $names[] = extractClassName($key);
        }
    else{

    }
    return $names;
}
        
function conceptHasSubclasses($relationsArr,$concept)
{
	global $is_a_relation_name_ar;
	
	foreach($relationsArr as $hash => $relationArr)
	{
		$relationsType = $relationArr['TYPE'];
	
		$subject = 	$relationArr['SUBJECT'];
		$object = $relationArr['OBJECT'];
		$verbAR = $relationArr['VERB'];
		
		
	
			
		// IF IT IS AN IS-A RELATION
		if ( $verbAR==$is_a_relation_name_ar && $concept==$object)
		{
			return true;
		}
	}
	
	return false;
}
function conceptHasParentClasses($relationsArr,$concept)
{
	global $is_a_relation_name_ar;

	foreach($relationsArr as $hash => $relationArr)
	{
		$relationsType = $relationArr['TYPE'];

		$subject = 	$relationArr['SUBJECT'];
		$object = $relationArr['OBJECT'];
		$verbAR = $relationArr['VERB'];
			
		// IF IT IS AN IS-A RELATION
		if ( $verbAR==$is_a_relation_name_ar && $concept==$subject)
		{
			return true;
		}
	}

	return false;
}


function buildRelationHashID($subject,$verb,$object)
{
	return md5("$subject,$verb,$object");
}

function isWordPartOfAVerbInVerbIndex($word,$lang)
{

	
	$verbIndexIterator = getAPCIterator("ALL\/MODEL_QA_ONTOLOGY\/VERB_INDEX\/.*");
	
	foreach($verbIndexIterator as $verbIndexCursor )
	{
		$verbWord = getEntryKeyFromAPCKey($verbIndexCursor['key']);
	
		$verbArr = $verbIndexCursor['value'];
	

		if ( $lang=="EN")
		{
			$verbWord = strtolower($verbWord);
			
		}
		
		if ( mb_strpos($verbWord, $word)!==false) 
		{
			//echoN("|$verbWord| |$word|".( mb_strpos($verbWord, $word)!==false));
			return $verbArr;
		}
	}
	
	return false;
}

function handleNewConceptFromRelation(&$finalConcepts,$subjectOrObject,$conceptLocationInRelation,&$notInCounceptsCounter,&$statsUniqueSubjects)
{
	global  $WORDS_TRANSLATIONS_AR_EN;
	
	$subjectOrObjectFlag =  null;
		
	// SUBJECT NOT IN MASTER CONCEPTS LIST
	if ( !isset($finalConcepts[$subject]) )
	{
		
		if ( $conceptLocationInRelation=="SUBJECT")
		{
			echoN("NOT IN CONCEPTS:S:$subjectOrObject");
		}
		else
		{
			echoN("NOT IN CONCEPTS:O:$subjectOrObject");
		}
		$notInCounceptsCounter++;
			
		$statsUniqueSubjects[$subjectOrObject]=1;

	
	}
	

	
	$termsArr = getTermArrBySimpleWord($finalTerms,$subjectOrObject);
		
	$freq = $termsArr['FREQ'];
		
	
		
	$isQuranaPhraseConcept = false;
	
	//echoN("^&&*:".(strpos($subjectOrObject," ")!==false));
	
	if( isMultiWordStr($subjectOrObject))
	{
		$quranaConceptArr = getQuranaConceptEntryByARWord($subjectOrObject);
	
	
		$engTranslation = ucfirst($quranaConceptArr['EN']);
			
		echoN("^^$subjectOrObject");
		$isQuranaPhraseConcept = true;
	}
	else
	{
		$uthmaniWord = getItemFromUthmaniToSimpleMappingTable($subjectOrObject);
		$engTranslation = ucfirst(cleanEnglishTranslation($WORDS_TRANSLATIONS_AR_EN[$uthmaniWord]));
	}
		
		
		
	addNewConcept($finalConcepts, $subjectOrObject, "A-BOX", "POPULATION_FROM_RELATIONS", $freq, $engTranslation);
	
	$finalConcepts[$subjectOrObject]['EXTRA']['POS']=$subjectOrObjectFlag;
	$finalConcepts[$subjectOrObject]['EXTRA']['WEIGHT']=$termsArr['WEIGHT'];
	
	if ( $isQuranaPhraseConcept)
	{
		echoN($isQuranaPhraseConcept."||||$subjectOrObject");
		$finalConcepts[$subjectOrObject]['EXTRA']['IS_QURANA_NGRAM_CONCEPT']=true;
	}
}


function doesQuestionIncludesVerb($extendedQueryArr)
{
	foreach($extendedQueryArr as $word => $pos)
	{
		if ( posIsVerb($pos))
		{
			if ( $word!="is" && $word!="are")
			{
				return true;
			}
		}
	}
	return false;
}

function getConceptRichnessScore($coneptArr)
{
	return strlen(print_r($coneptArr,true));
}

function updateNameInAllRelations(&$relationsArr, $nameFrom, $nameTo)
{
	$relationsArrComp = $relationsArr;
	
	foreach($relationsArr as $hash => $relationArr)
	{
		$relationsType = $relationArr['TYPE'];
	
		$subject = 	$relationArr['SUBJECT'];
		$object = $relationArr['OBJECT'];
		$verbAR = $relationArr['VERB'];
		
			
		if ( $subject=="$nameFrom")
		{
			$relationsArr[$hash]['SUBJECT']=$nameTo;
		}
		if ( $object=="$nameFrom")
		{
			$relationsArr[$hash]['OBJECT']=$nameTo;
		}
			
			
		$newHash = md5($relationsArr[$hash]['SUBJECT'].$relationsArr[$hash]['VERB'].$relationsArr[$hash]['OBJECT']);
			
		//echoN("###  $newHash $hash $subject $verbAR $object");
		
		if ( $newHash!=$hash)
		{
			$relationsArrComp[$newHash] = $relationsArr[$hash];
			unset($relationsArrComp[$hash]);
		}
	}
	
	 $relationsArr = $relationsArrComp;
}

function getConceptsFoundInText($text,$lang)
{
	

	global $thing_class_name_ar, $is_a_relation_name_ar;


	
	$conceptsInTextArr = array();



		
		$textWordsArr = preg_split("/ /",$text);
	
		foreach($textWordsArr as $index=>$word)
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
				
			//echoN($wordConveretedToConceptID);
			
			if ( modelEntryExistsInMemory("ALL", "MODEL_QA_ONTOLOGY", "CONCEPTS", $wordConveretedToConceptID) )
			{
				//preprint_r($MODEL_QA_ONTOLOGY['CONCEPTS'][$wordConveretedToConceptID]);exit;
				//echoN($wordConveretedToConceptID);

				//$mainConceptArr = $MODEL_QA_ONTOLOGY['CONCEPTS'][$wordConveretedToConceptID];
				
				$mainConceptArr = getModelEntryFromMemory("ALL", "MODEL_QA_ONTOLOGY", "CONCEPTS", $wordConveretedToConceptID);

				$conceptLabelAR = $mainConceptArr['label_ar'];
				$conceptLabelEN = $mainConceptArr['label_en'];
				$conceptFrequency = $mainConceptArr['frequency'];
				$conceptWeight = $mainConceptArr['weight'];

				$finalNodeLabel = $conceptLabelAR;

				if ( $lang == "EN")
				{
					$finalNodeLabel = $conceptLabelEN;
				}


				if (  $wordConveretedToConceptID==$thing_class_name_ar) continue;

					
	

				$conceptsInTextArr[$wordConveretedToConceptID]= createNewConceptObj($nodeSerialNumber,$lang, $finalNodeLabel, $mainConceptArr,$randomXLocation,$randomYLocation,1);
	
					

			}
				
		}
	

	return $conceptsInTextArr;

}

 function findConceptAnnotaionValue($ontology, $concept, $annotation_key){
        $annotations = $ontology->{'owl_data'}{'annotations'};
        $concept_annotations = $annotations{$concept};
//        print_r($concept_annotations);  
        if(count($concept_annotations) > 0)
            foreach ($concept_annotations as $key => $value){
                if($value['KEY'] == $annotation_key ){
                    return $value['VAL'];
                }
        }
        return '';
    }

function addAnnotationValueToClass(&$ontology, $class, $annot_key, $annot_value){
        $annotations = &$ontology->{'owl_data'}{'annotations'};
        $count = count($annotations{$class});
        if($count == 0){
            $annotations{$class} = [];
        }
        $concept_annotations = &$annotations{$class};
        $new_array = [];
        foreach ($annot_value as $value) {
           $new_array['KEY'] = $annot_key;
            $new_array['VAL'] = $value;   
        }
      
        $concept_annotations[$count] = $new_array;
//        echoN('inside addAnnotationValueToClass');
//        print_r($concept_annotations);
}

function getWordStemEn($concept){
    $concepts = explodeCamelCase($concept);
    $stems = [];
//    echoN(" ---- En stem of: ". $concept);
    if( count($concepts) > 0 ){
        foreach ($concepts as $value) {
            $stems[] = PorterStemmer::Stem($value);
        }
    }
    else{
         $stems[] = PorterStemmer::Stem($concept);
    }
    return $stems;
}
function writeLineByLineToFile($lines, $fileName, $mode){
    $file = fopen($fileName , $mode);
    if($file == false){
        echoN("Cannot open file");
        return ;
    }
    foreach($lines as $line){
//        echoN($line);
        if($line !='')
         fwrite($file, $line.PHP_EOL);
    }
    fclose($file);
}

/**
 * read text from file line by line 
 * @param $fileName
 * @return array of lines
 *          
 */
function readLineByLineFromFile($fileName){

    $lines = [];
    foreach(file($fileName) as $line){
        $lines[] = $line;
    }
    return $lines;
}

function getWordStemAr($concept){
    $concepts = explode(" ", $concept);
    if( count($concepts) == 0 ){
        $concepts[] = $concept;
    }
//    print_r($concepts);
//    echoN("");echoN("");echoN("");
    writeLineByLineToFile($concepts, "farasa_tmp_in", "w");

//    $command = "java -jar farasa/Farasa.jar"
//            . " -l true"
//            . " -i farasa_tmp_in"
//            . " -o farasa_tmp_out"
//            ;
    
    $command = "java -jar /home/sarah/Downloads/FarasaSegmenter/FarasaSegmenterJar.jar -l true "
            . "-i /home/sarah/php_dir/teb/farasa_tmp_in -o /home/sarah/php_dir/teb/farasa_tmp_out";
    
    executeCommand($command);
    $stems = readLineByLineFromFile("farasa_tmp_out");
    
//    print_r($stems);
//    foreach($stems as $tok)
//        echo($tok . " ---- Ar stem of: ". $concept);
    return $stems;
}
    

/***
 * add stem for each concept {ar, en} in the ontology
 * @param &$ontology
 */
function addStemToOntology(&$ontology){
    $ontologyClassesArray = $ontology->{'owl_data'}['classes'];
    $label_key_En = "STEM_EN";
    $label_key_Ar = "STEM_Ar";
     
//    foreach ($ontology->{'owl_data'} as $class => $value) {
//            preprint_r($class);
//    }
//    
    foreach($ontologyClassesArray as $class => $value){
        // for English
        $name_En = stripOntologyNamespace($class);
        $stem_En = getWordStemEn($name_En);
        addAnnotationValueToClass($ontology,$class, $label_key_En, $stem_En);
        
        //for Arabic
        $annotation_key = 'Arabic_Name';
//      echoN("+" .$name_Ar) ;
        $name_Ar = findConceptAnnotaionValue($ontology, $class, $annotation_key);
        $stem_Ar = getWordStemAr($name_Ar);
        addAnnotationValueToClass($ontology, $class, $label_key_Ar, $stem_Ar);
        
    }
    
//    print_r($ontology->{'owl_data'}{'annotations'});
 
}

?>