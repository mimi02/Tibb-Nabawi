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
require_once("../global.settings.php");

require_once("../libs/search.lib.php");
require_once("../libs/graph.lib.php");
require_once("../libs/core.lib.php");
require_once("../libs/ontology.lib.php");
require_once("../libs/sparql.queries.lib.php");

//require_once("query.handling.common.php");
$query = $_GET['q'];
$label = $_GET['label'];
$exact = $_GET['exact'];


//if(isset($_GET['lang']) && !empty($_GET['lang'])){
//    $lang = $_GET['lang'];
//}
//else 
    
    if (isArabicString($label))
    {
            $lang = "AR";
            $direction = "rtl";
    } else {
        $lang="EN";
    }

require_once("../resources/lang/$lang/messages.php");
$lang_resource = new Resource();

$sparql = getSPARQLEngine();
function formatString($str, $key){
    global $lang;
    if($lang == 'EN')
        return stripOntologyNamespace($str->$key);
//    echoN($str->treatment_ar);
    $ark = $key."_ar";
    if(isset($str->$ark))
        return stripOntologyNamespace($str->$ark);
    else
         return stripOntologyNamespace($str->$key);
}   

function showRef($row){
    global $lang_resource;
    if(isset($row->h_link)){
        return "<a target=\"_blank\" href =".$row->h_link."> {$lang_resource->h_ref} </a>";
    }
}

function showHadeeth($row){
    global $lang;
    if($lang == 'AR' && isset($row->text_ar)){
                return formatString($row, 'text');
        }elseif ($lang == 'EN' && isset($row->text)) {
                return formatString($row,'text');
        }else{
            return '';
        }
}

function getIllnessMaterial_($sparql, $query, $exact, $prophet_mdern_material_){
    if($exact == "true"){
        return getIllnessTreatmentByClassName($sparql, $query, $prophet_mdern_material_);
    } else{
         return getIllnessTreatmentSearch($sparql, $query, $prophet_mdern_material_);
    }
    
}

function getIllnessTreatment_($sparql, $query, $exact, $prophet_mdern_treatment_){
    if($exact == "true"){
       return getIllnessMaterialByClassName($sparql, $query, $prophet_mdern_treatment_);
    } else{
        return getIllnessMaterialSearch($sparql, $query, $prophet_mdern_treatment_);
    }
}



//$result = getRelatedHadeeth($sparql, $query);
global $modern_treatment_class_name, $prophet_treatment_class_name, $modern_material_class_name, $prophet_material_class_name;
$treatment_prophet = getIllnessTreatment_($sparql, $query, $exact, $prophet_treatment_class_name);
$treatmment_modern = getIllnessTreatment_($sparql, $query, $exact, $modern_treatment_class_name);
$material_prophet = getIllnessMaterial_($sparql, $query, $exact, $prophet_material_class_name);
$material_modern = getIllnessMaterial_($sparql, $query, $exact, $modern_material_class_name);

//formating

$str ="<h4> <span style=\"color:green;\"> {$lang_resource->prophets_treatment}:</span> </h4> ";
foreach ($treatment_prophet as $row){
    echoN("<b> $row->class </b>");
    $str = "$str <b>+". formatString($row, 'treatment') ." </b>"."<br>";
//    if( isset($row->text))
        $str = "$str <br> ".showHadeeth($row). showRef($row)."<br>";
}

$str = "$str <br> <h4><span style=\"color:green;\"> {$lang_resource->prophets_material}:</span> </h4> ";

foreach ($material_prophet as $row){
    $str = "$str  <b>+".formatString($row, 'treatment')." </b> <br> ";
    $str = "$str  ".showHadeeth($row) . showRef($row)."<br>";
}

$str = "$str <h4><span style=\"color:blue;\"> {$lang_resource->modern_treatment}:</span> </h4> ";

foreach ($treatmment_modern as $row){
    $str = "$str <b>+".formatString( $row,'treatment' )." </b> <br>";
}

$str = "$str  <h4><span style=\"color:blue;\">{$lang_resource->modern_material}:</span></h4> ";

foreach ($material_modern as $row){
    $str = "$str +".formatString($row,'treatment') ." </b> <br>";
    

}
echo $str;



?>






