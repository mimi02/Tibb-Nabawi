<?php

require_once("global.settings.php");
require_once(dirname(__FILE__)."/libs/core.lib.php");
require_once(dirname(__FILE__)."/libs/owllib/OWLLib.php");
require_once(dirname(__FILE__)."/libs/owllib/reader/OWLReader.php");
require_once(dirname(__FILE__)."/libs/owllib/writer/OWLWriter.php");
require_once(dirname(__FILE__)."/libs/owllib/memory/OWLMemoryOntology.php");
require_once(dirname(__FILE__)."/libs/ontology.lib.php");


/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
function loadOntology()
{
    echoN("in loadOntology");
    gc_enable();
    if (!function_exists("apc_exists"))
	{
		throw new Exception("APC not found!");
	}
    global $qaOntologyNamespace,$qaOntologyFile,$is_a_relation_name_ar,$is_a_relation_name_en;
    $reader = new OWLReader();
    $ontology = new OWLMemoryOntology();
    //$thingClassName = "$thing_class_name_ar";
    $ontology->setNamespace($qaOntologyNamespace);
    $reader->readFromFile($qaOntologyFile, $ontology);
    
    //SAVE ONTOLOGY IN CACHE 
    $res = apc_store("MODEL_TEBB_ONTOLOGY",$ontology);
    if ( $res===false){ throw new Exception("Can't cache MODEL_TEBB_ONTOLOGY"); }
        
       
//    preprint_r(count($ontology->{'owl_data'}['classes']));
//    preprint_r(($ontology->{'owl_data'}));
    
    // extract concepts names
    $classes = $ontology->{'owl_data'}['classes'];
    $subclasses = $ontology->{'owl_data'}['subclasses'];
    $labels = $ontology->{'owl_data'}['labels'];
 echoN("end loadOntology");
    // add concept stems to structure 
    //writeLineByLineToFile(getClassesNames($classes), "concept_names.txt");
   return $ontology;

}

function saveNewOntology($ontology){
    global $apcMemoryOntologyKey;
     echoN("in saveOntology");
    $outputFileName = "newOntology.owl"; $title="";$version="";
    
    $writer = new OWLWriter();
    $writer->writeToFile($outputFileName, $ontology, $title, $version);
     
    $res = apc_store($apcMemoryOntologyKey,$ontology);
    if($res == false)
        echoN ("Cant cache model");
    
}

function loadModels($lang){
     echoN("in loadModel");
   $ontology = loadOntology();
   addStemToOntology($ontology);
   
   saveNewOntology($ontology);
}

//loadModels($lang);
