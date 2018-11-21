<?php
require_once("global.settings.php");
require_once(dirname(__FILE__)."/libs/core.lib.php");
require_once dirname(__FILE__)."/libs/EasyRdf.php";
// Setup namespaces
EasyRdf_Namespace::set('rdfs', 'http://www.w3.org/2000/01/rdf-schema#');
EasyRdf_Namespace::set('uni', 'http://www.semanticweb.org/muna/ontologies/2018/6/TibbNabawi-ontology-15#');
EasyRdf_Namespace::set('owl', 'http://www.w3.org/2002/07/owl#');
EasyRdf_Namespace::set('rdf', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#');
/* 
 * this file contains all sparql queries to query al tibb annabwi ontology file
 */
function getSPARQLEngine(){
    global $sparql_endpoint;
    $sparql = new EasyRdf_Sparql_Client($sparql_endpoint);  
    return $sparql;
}

function getAllIllnesses($sparql,$lang){
    if($lang == 'AR'){
        return getAllIllnessesAr($sparql);
    }else{
        return getAllIllnessesEn($sparql);
    }
}

function getAllIllnessesAr($sparql){
     global $illness_class_name;
     $result = $sparql->query(
        'SELECT * '.
        'WHERE {'.
            "?class rdfs:subClassOf uni:$illness_class_name .".
             "?class uni:Arabic_Name ?word".
//             '?class rdfs:label ?word '.
        '}'
    );
     
     return $result;
}

function getAllIllnessesEn($sparql){
     global $illness_class_name;
     $result = $sparql->query(
        'SELECT * '.
        'WHERE {'.
            "?word rdfs:subClassOf uni:$illness_class_name .".
//             '?class rdfs:label ?word '.
        '}'
    );
     
     return $result;
}

function searchTreatments($sparql,$keyword){
    global $treatment_class_name;
      $result = $sparql->query(
        'SELECT ?class '.
        'WHERE {'.
            "?class rdfs:subClassOf* uni:$treatment_class_name .".
             "FILTER (contains(LCASE(str(?class)) ,'$keyword') )".
        '}'.
        'LIMIT 25'
    );
     return $result;
}

function searchMaterials($sparql, $keyword, $modernOrProphet){
    global $material_class_name;
      $result = $sparql->query(
        'SELECT ?class '.
        'WHERE {'.
            "?class rdfs:subClassOf* uni:$material_class_name .".
             "FILTER (contains(LCASE(str(?class)) ,'$keyword') )".
        '}'.
        'LIMIT 25'
    );
     return $result;
}

function searchClasses($sparql,$keyword){
    
}

function searchIllnesses($sparql,$keyword){
      global $illness_class_name;
      
      $result = $sparql->query(
        'SELECT *'.
        'WHERE {'.
            "?class rdfs:subClassOf* uni:$illness_class_name .".
             "FILTER (contains(LCASE(str(?class)),'$keyword')) .".
//              '?class rdfs:subClassOf ?s .'.
//               ' ?s owl:onProperty uni:isStronglyVerifiedBy .'.
//                '?ind rdf:type uni:Authentic_Hadith.'.
//                '?ind uni:Arabic_Hadeeth ?text.'.
//                '?s ?y ?ind'. 
        '}'.
        'LIMIT 25'
    );
     return $result;
}
function getIllnessTreatment($sparql, $illness, $modernOrProphet){
      $result = $sparql->query(
            ##gentamicin SubClassOf treat some plague_Infection
           ' SELECT distinct ?x ?y ?treatment '.
             "WHERE { uni:$illness rdfs:subClassOf ?x.".
                 '?x owl:onProperty uni:hasBeenTreated.'.
                 "?treatment rdfs:subClassOf* uni:$modernOrProphet .".
                 ' ?x ?y ?treatment'.
                '}'
         );
     return $result;
}

function getIllnessMaterial($sparql, $illness, $modernOrProphet){
      $result = $sparql->query(
            ##gentamicin SubClassOf treat some plague_Infection
           ' SELECT distinct ?x ?y ?treatment '.
             "WHERE { uni:$illness rdfs:subClassOf ?x.".
                 '?x owl:onProperty uni:hasBeenTreated.'.
                 "?treatment rdfs:subClassOf* uni:$modernOrProphet .".
                 ' ?x ?y ?treatment'.
                '}'
         );
     return $result;
}

function getIllnessesTreatedBy($sparql, $treatment){
     global $illness_class_name;
     $result = $sparql->query(
            ##gentamicin SubClassOf treat some plague_Infection
             ##LowerTemperature SubClassOf isStronglyVerifiedBy value Sahih_al-Bukhari_5725
           ' SELECT distinct ?x ?y ?illness '.
             "WHERE { uni:$treatment rdfs:subClassOf ?x.".
                 '?x owl:onProperty uni:treat.'.
                 "?illness rdfs:subClassOf* uni:$illness_class_name.".
                 ' ?x ?y ?illness'.
                '}'
         );
     return $result;
}

function getRelatedHadeeth($sparql, $class){
    global $is_strongly_verified;
    $result = $sparql -> query(
            'select ?text '.
                'where {'.
                " uni:$class rdfs:subClassOf ?s . ".
                "?s owl:onProperty uni:$is_strongly_verified .".
                ' ?ind a uni:Authentic_Hadeeth.'.
                ' ?ind uni:Arabic_Hadeeth ?text.'.
                ' ?s ?y ?ind'.
                '}'
           );
    return $result;
}

function getIllnessesTreatedByMaterial($sparql, $material){
     global $illness_class_name;
     $result = $sparql->query(
           ' SELECT ?x ?y ?illness '.
             "WHERE { uni:$material rdfs:subClassOf ?x.".
                 '?x owl:onProperty uni:treat.'.
                 "?illness rdfs:subClassOf* uni:$illness_class_name.".
                 ' ?x ?y ?illness'.
                '}'
         );
     return $result;
}

